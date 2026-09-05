<?php

namespace App\Services\Budget;

use App\Enums\BudgetRequestStatus;
use App\Enums\BudgetRequestType;
use App\Enums\FinancialYearStatus;
use App\Models\Allocation;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestHistory;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * Tindakan MAKER bagi cadangan bajet (peruntukan awal & pelarasan).
 * TIADA kesan ledger di sini — hanya cadangan yang menunggu kelulusan checker.
 */
class BudgetRequestService
{
    public function __construct(private readonly AuditService $audit) {}

    public function createDraft(User $maker, array $data): BudgetRequest
    {
        $type = $data['request_type'] instanceof BudgetRequestType ? $data['request_type'] : BudgetRequestType::from($data['request_type']);
        $amount = Money::of((string) $data['amount']);
        $year = FinancialYear::findOrFail($data['financial_year_id']);

        $this->assertYearOpen($year);
        if (! $amount->isPositive()) {
            throw new BudgetException('Jumlah mesti lebih daripada sifar.');
        }

        if ($type->isInitial()) {
            $this->assertNoExistingInitial((int) $data['alp_id'], (int) $data['financial_year_id']);
        } else {
            $this->assertAllocationExists((int) $data['alp_id'], (int) $data['financial_year_id']);
        }

        $request = BudgetRequest::create([
            'request_type' => $type,
            'alp_id' => $data['alp_id'],
            'financial_year_id' => $data['financial_year_id'],
            'amount' => $amount->value(),
            'status' => BudgetRequestStatus::DRAFT,
            'reason' => $data['reason'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'created_by' => $maker->id,
        ]);

        $this->history($request, null, BudgetRequestStatus::DRAFT, $maker, 'Cadangan dicipta');
        $this->audit->log($this->prefix($request).'_CREATED', $request, null, [
            'type' => $type->value, 'amount' => $amount->value(), 'alp_id' => $data['alp_id'],
        ]);

        return $request;
    }

    public function update(BudgetRequest $request, User $maker, array $data): BudgetRequest
    {
        if (! $request->isEditableByMaker()) {
            throw new BudgetException('Cadangan ini tidak boleh disunting.');
        }

        $amount = Money::of((string) $data['amount']);
        if (! $amount->isPositive()) {
            throw new BudgetException('Jumlah mesti lebih daripada sifar.');
        }

        $request->update([
            'amount' => $amount->value(),
            'reason' => $data['reason'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
        ]);

        $this->audit->log($this->prefix($request).'_UPDATED', $request, null, ['amount' => $amount->value()]);

        return $request;
    }

    /** Hantar cadangan untuk kelulusan (DRAFT / REVISION_REQUIRED → PENDING_APPROVAL). */
    public function submit(BudgetRequest $request, User $maker): BudgetRequest
    {
        return DB::transaction(function () use ($request, $maker) {
            $request = BudgetRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (! in_array($request->status, [BudgetRequestStatus::DRAFT, BudgetRequestStatus::REVISION_REQUIRED], true)) {
                throw new BudgetException('Hanya cadangan Draf atau Perlu Pembetulan boleh dihantar.');
            }

            $this->assertYearOpen($request->financialYear);
            if (! $request->amountMoney()->isPositive()) {
                throw new BudgetException('Jumlah mesti lebih daripada sifar.');
            }
            if ($request->request_type->isInitial()) {
                $this->assertNoExistingInitial($request->alp_id, $request->financial_year_id, $request->id);
            } else {
                $this->assertAllocationExists($request->alp_id, $request->financial_year_id);
            }

            $from = $request->status;
            $wasRevision = $from === BudgetRequestStatus::REVISION_REQUIRED;

            $request->update([
                'status' => BudgetRequestStatus::PENDING_APPROVAL,
                'submitted_by' => $maker->id,
                'submitted_at' => now(),
                'revision_number' => $wasRevision ? $request->revision_number + 1 : $request->revision_number,
            ]);

            $this->history($request, $from, BudgetRequestStatus::PENDING_APPROVAL, $maker, 'Dihantar untuk kelulusan');
            $this->audit->log($this->prefix($request).'_SUBMITTED', $request, null, [
                'amount' => $request->amount, 'revision_number' => $request->revision_number,
            ]);

            return $request;
        });
    }

    // ── Bantuan ─────────────────────────────────────────────────

    private function assertYearOpen(FinancialYear $year): void
    {
        if ($year->isClosed() || ! in_array($year->status, [FinancialYearStatus::ACTIVE, FinancialYearStatus::OPEN], true)) {
            throw new BudgetException('Tahun kewangan tidak sah atau telah ditutup.');
        }
    }

    private function assertNoExistingInitial(int $alpId, int $fyId, ?int $ignoreRequestId = null): void
    {
        if (Allocation::where('alp_id', $alpId)->where('financial_year_id', $fyId)->exists()) {
            throw new BudgetException('Peruntukan awal telah wujud untuk ALP ini bagi tahun kewangan ini. Gunakan pelarasan.');
        }

        $conflict = BudgetRequest::query()
            ->where('alp_id', $alpId)->where('financial_year_id', $fyId)
            ->where('request_type', BudgetRequestType::INITIAL_ALLOCATION->value)
            ->whereIn('status', BudgetRequestStatus::activeValues())
            ->when($ignoreRequestId, fn ($q) => $q->where('id', '!=', $ignoreRequestId))
            ->exists();

        if ($conflict) {
            throw new BudgetException('Sudah ada cadangan peruntukan awal aktif untuk ALP/tahun ini.');
        }
    }

    private function assertAllocationExists(int $alpId, int $fyId): void
    {
        if (! Allocation::where('alp_id', $alpId)->where('financial_year_id', $fyId)->exists()) {
            throw new BudgetException('Tiada peruntukan sedia ada untuk dilaraskan. Cipta peruntukan awal dahulu.');
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
