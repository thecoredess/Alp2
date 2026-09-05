<?php

namespace App\Services\Budget;

use App\Enums\BudgetRequestStatus;
use App\Enums\FinancialYearStatus;
use App\Enums\RoleName;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestHistory;
use App\Models\User;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Audit\AuditService;
use App\Support\Money;
use App\Support\UrsContributionPolicy;
use Illuminate\Support\Facades\DB;

/**
 * Tindakan CHECKER bagi cadangan bajet. HANYA di sini transaksi ledger diposkan
 * (selepas kelulusan). Menguatkuasakan MAKER ≠ CHECKER, keselamatan pengurangan,
 * ketepatan wang, idempotensi, dan atomicity penuh.
 */
class BudgetRequestApprovalService
{
    public function __construct(
        private readonly BudgetService $budget,
        private readonly ApplicationBudgetService $appBudget,
        private readonly AuditService $audit,
    ) {}

    public function approve(BudgetRequest $request, User $checker): BudgetRequest
    {
        return DB::transaction(function () use ($request, $checker) {
            $request = BudgetRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            $request->load('financialYear');

            // (4) Status betul.
            if ($request->status !== BudgetRequestStatus::PENDING_APPROVAL) {
                throw new BudgetException('Cadangan tidak berada pada peringkat kelulusan.');
            }

            // (5) MAKER ≠ CHECKER (dikuatkuasakan walau untuk Super Admin).
            $this->assertMakerChecker($request, $checker);

            // (6) Tahun kewangan sah.
            $year = $request->financialYear;
            if ($year->isClosed() || ! in_array($year->status, [FinancialYearStatus::ACTIVE, FinancialYearStatus::OPEN], true)) {
                throw new BudgetException('Tahun kewangan tidak sah atau telah ditutup — kelulusan gagal.');
            }

            // (7) Jumlah sah.
            $amount = $request->amountMoney();
            if (! $amount->isPositive()) {
                throw new BudgetException('Jumlah tidak sah.');
            }

            if ($request->request_type->isInitial()) {
                foreach (UrsContributionPolicy::validateAnnualAllocation($amount) as $error) {
                    throw new BudgetException($error);
                }
            }

            // (9) Belum diposkan (idempotensi).
            if ($request->transaction()->exists()) {
                throw new BudgetException('Cadangan ini telah diposkan ke ledger.');
            }

            $alp = Alp::findOrFail($request->alp_id);

            // (10) Poskan ke ledger mengikut jenis.
            if ($request->request_type->isInitial()) {
                // Pastikan tiada peruntukan sedia ada (semakan semula).
                if (Allocation::where('alp_id', $alp->id)->where('financial_year_id', $year->id)->exists()) {
                    throw new BudgetException('Peruntukan awal telah wujud untuk ALP/tahun ini.');
                }
                $this->budget->allocate($alp, $year, $amount, $request->reference_number, $request->reason, $request);
            } else {
                $allocation = Allocation::where('alp_id', $alp->id)
                    ->where('financial_year_id', $year->id)->lockForUpdate()->first();
                if (! $allocation) {
                    throw new BudgetException('Tiada peruntukan untuk dilaraskan.');
                }

                if ($request->request_type->isDecrease()) {
                    $this->assertDecreaseSafe($alp->id, $year->id, $amount);
                }

                $delta = $request->request_type->ledgerDelta($amount); // bertanda
                $this->budget->adjust($allocation, $delta, $request->reference_number, $request->reason, $request);
            }

            // (11-12) Tandakan diluluskan.
            $from = $request->status;
            $request->update([
                'status' => BudgetRequestStatus::APPROVED,
                'approved_by' => $checker->id,
                'approved_at' => now(),
            ]);

            // (13) Sejarah + audit.
            $this->history($request, $from, BudgetRequestStatus::APPROVED, $checker, 'Diluluskan & diposkan ke ledger');
            $this->audit->log($this->prefix($request).'_APPROVED', $request, null, [
                'amount' => $amount->value(), 'checker_id' => $checker->id,
            ]);

            if ($checker->hasRole(RoleName::SUPER_ADMIN->value)) {
                $this->audit->log('SUPER_ADMIN_FINANCIAL_OVERRIDE', $request, null, [
                    'amount' => $amount->value(),
                    'note' => 'Kelulusan kewangan oleh Super Admin.',
                ]);
            }

            return $request;
        });
    }

    public function reject(BudgetRequest $request, User $checker, string $reason): BudgetRequest
    {
        return DB::transaction(function () use ($request, $checker, $reason) {
            $request = BudgetRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== BudgetRequestStatus::PENDING_APPROVAL) {
                throw new BudgetException('Cadangan tidak berada pada peringkat kelulusan.');
            }
            $this->assertMakerChecker($request, $checker);

            $from = $request->status;
            $request->update([
                'status' => BudgetRequestStatus::REJECTED,
                'rejected_by' => $checker->id, 'rejected_at' => now(), 'rejection_reason' => $reason,
            ]);
            $this->history($request, $from, BudgetRequestStatus::REJECTED, $checker, 'Ditolak');
            $this->audit->log($this->prefix($request).'_REJECTED', $request, null, ['reason' => $reason]);

            return $request;
        });
    }

    public function returnForRevision(BudgetRequest $request, User $checker, string $reason): BudgetRequest
    {
        return DB::transaction(function () use ($request, $checker, $reason) {
            $request = BudgetRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== BudgetRequestStatus::PENDING_APPROVAL) {
                throw new BudgetException('Cadangan tidak berada pada peringkat kelulusan.');
            }
            $this->assertMakerChecker($request, $checker);

            $from = $request->status;
            $request->update([
                'status' => BudgetRequestStatus::REVISION_REQUIRED,
                'returned_by' => $checker->id, 'returned_at' => now(), 'return_reason' => $reason,
            ]);
            $this->history($request, $from, BudgetRequestStatus::REVISION_REQUIRED, $checker, 'Dikembalikan untuk pembetulan');
            $this->audit->log($this->prefix($request).'_RETURNED', $request, null, ['reason' => $reason]);

            return $request;
        });
    }

    // ── Semakan ─────────────────────────────────────────────────

    private function assertMakerChecker(BudgetRequest $request, User $checker): void
    {
        if ($checker->id === $request->created_by || $checker->id === $request->submitted_by) {
            throw new BudgetException('MAKER ≠ CHECKER: anda tidak boleh meluluskan cadangan yang anda cipta atau hantar.');
        }
    }

    /**
     * Pengurangan tidak boleh menyebabkan Projected Available negatif
     * (Ledger Available selepas pengurangan − Pending Request aktif).
     */
    private function assertDecreaseSafe(int $alpId, int $fyId, Money $amount): void
    {
        $available = $this->budget->summaryFor($alpId, $fyId)->available();
        $availableAfter = $available->minus($amount);
        $pending = $this->appBudget->pendingRequest($alpId, $fyId);
        $projectedAfter = $availableAfter->minus($pending);

        if ($projectedAfter->isNegative()) {
            throw new BudgetException(sprintf(
                'Pengurangan ditolak: Projected Available akan menjadi negatif (RM%s). '.
                'Terdapat permohonan aktif (Pending RM%s) yang bergantung pada peruntukan ini.',
                $projectedAfter->format(),
                $pending->format(),
            ));
        }
    }

    private function prefix(BudgetRequest $request): string
    {
        return $request->request_type->isInitial() ? 'ALLOCATION_REQUEST' : 'ADJUSTMENT_REQUEST';
    }

    private function history(BudgetRequest $request, ?BudgetRequestStatus $from, BudgetRequestStatus $to, User $user, string $remarks): void
    {
        BudgetRequestHistory::create([
            'budget_request_id' => $request->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by' => $user->id,
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }
}
