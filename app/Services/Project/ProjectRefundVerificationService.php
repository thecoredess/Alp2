<?php

namespace App\Services\Project;

use App\Enums\ProjectExpenseStatus;
use App\Enums\RefundStatus;
use App\Models\Allocation;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use App\Models\ProjectExpenseRefundHistory;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use Illuminate\Support\Facades\DB;

/**
 * CHECKER — pengesahan refund. HANYA di sini transaksi REFUND diposkan ke ledger.
 * Menguatkuasakan maker ≠ checker, had terkumpul, atomicity & idempotensi.
 * Ledger kekal immutable: refund ialah transaksi BAHARU (bukan pemadaman/penyuntingan).
 */
class ProjectRefundVerificationService
{
    public function __construct(
        private readonly BudgetService $budget,
        private readonly AuditService $audit,
    ) {}

    public function verify(ProjectExpenseRefund $refund, User $checker): ProjectExpenseRefund
    {
        return DB::transaction(function () use ($refund, $checker) {
            $refund = ProjectExpenseRefund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            $expense = ProjectExpense::whereKey($refund->project_expense_id)->lockForUpdate()->firstOrFail();
            $project = Project::whereKey($refund->project_id)->lockForUpdate()->firstOrFail();

            if ($refund->status !== RefundStatus::PENDING_VERIFICATION) {
                throw new ProjectException('Refund tidak berada pada peringkat pengesahan.');
            }
            $this->assertMakerChecker($refund, $checker);

            if ($expense->status !== ProjectExpenseStatus::VERIFIED) {
                throw new ProjectException('Perbelanjaan asal mesti DISAHKAN.');
            }
            if ($project->isClosed()) {
                throw new ProjectException('Projek telah ditutup.');
            }

            $amount = $refund->amountMoney();
            if (! $amount->isPositive()) {
                throw new ProjectException('Jumlah tidak sah.');
            }

            // Idempotensi — belum diposkan.
            if ($refund->transaction()->exists()) {
                throw new ProjectException('Refund ini telah diposkan ke lejar.');
            }

            // Had terkumpul: refund disahkan (termasuk yang ini) ≤ jumlah perbelanjaan.
            $verifiedOthers = $this->verifiedTotalExcluding($expense, $refund->id);
            $projected = $verifiedOthers->plus($amount);
            if ($projected->greaterThan($expense->amountMoney())) {
                throw new ProjectException(sprintf(
                    'Refund terkumpul (RM%s) melebihi jumlah perbelanjaan (RM%s).',
                    $projected->format(), $expense->amountMoney()->format(),
                ));
            }

            // Kunci allocation berkaitan projek.
            $allocation = Allocation::where('alp_id', $project->alp_id)
                ->where('financial_year_id', $project->financial_year_id)
                ->lockForUpdate()->first();
            if (! $allocation) {
                throw new ProjectException('Tiada peruntukan berkaitan.');
            }

            $from = $refund->status;
            $refund->update([
                'status' => RefundStatus::VERIFIED,
                'verified_by' => $checker->id,
                'verified_at' => now(),
            ]);

            // Poskan REFUND (immutable ledger; unique project_expense_refund_id).
            $this->budget->recordRefund($allocation, $amount, $project, $refund);

            $this->history($refund, $from, RefundStatus::VERIFIED, $checker, 'Disahkan & diposkan ke lejar');
            $this->audit->log('REFUND_VERIFIED', $refund, null, ['amount' => $amount->value(), 'checker_id' => $checker->id]);

            return $refund;
        });
    }

    public function reject(ProjectExpenseRefund $refund, User $checker, string $reason): ProjectExpenseRefund
    {
        return DB::transaction(function () use ($refund, $checker, $reason) {
            $refund = ProjectExpenseRefund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            if ($refund->status !== RefundStatus::PENDING_VERIFICATION) {
                throw new ProjectException('Refund tidak berada pada peringkat pengesahan.');
            }
            $this->assertMakerChecker($refund, $checker);

            $from = $refund->status;
            $refund->update([
                'status' => RefundStatus::REJECTED,
                'rejected_by' => $checker->id, 'rejected_at' => now(), 'rejection_reason' => $reason,
            ]);
            $this->history($refund, $from, RefundStatus::REJECTED, $checker, 'Ditolak');
            $this->audit->log('REFUND_REJECTED', $refund, null, ['reason' => $reason]);

            return $refund;
        });
    }

    public function returnForRevision(ProjectExpenseRefund $refund, User $checker, string $reason): ProjectExpenseRefund
    {
        return DB::transaction(function () use ($refund, $checker, $reason) {
            $refund = ProjectExpenseRefund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            if ($refund->status !== RefundStatus::PENDING_VERIFICATION) {
                throw new ProjectException('Refund tidak berada pada peringkat pengesahan.');
            }
            $this->assertMakerChecker($refund, $checker);

            $from = $refund->status;
            $refund->update([
                'status' => RefundStatus::REVISION_REQUIRED,
                'returned_by' => $checker->id, 'returned_at' => now(), 'return_reason' => $reason,
            ]);
            $this->history($refund, $from, RefundStatus::REVISION_REQUIRED, $checker, 'Dikembalikan untuk pembetulan');
            $this->audit->log('REFUND_RETURNED', $refund, null, ['reason' => $reason]);

            return $refund;
        });
    }

    private function verifiedTotalExcluding(ProjectExpense $expense, int $excludeRefundId): \App\Support\Money
    {
        $total = \App\Support\Money::zero();
        foreach ($expense->refunds()->where('status', RefundStatus::VERIFIED->value)->where('id', '!=', $excludeRefundId)->get() as $other) {
            $total = $total->plus($other->amountMoney());
        }

        return $total;
    }

    private function assertMakerChecker(ProjectExpenseRefund $refund, User $checker): void
    {
        if ($checker->id === $refund->created_by || $checker->id === $refund->submitted_by) {
            throw new ProjectException('MAKER ≠ CHECKER: anda tidak boleh mengesahkan refund yang anda cipta/hantar.');
        }
    }

    private function history(ProjectExpenseRefund $refund, ?RefundStatus $from, RefundStatus $to, User $user, string $remarks): void
    {
        ProjectExpenseRefundHistory::create([
            'project_expense_refund_id' => $refund->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by' => $user->id,
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }
}
