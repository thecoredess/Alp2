<?php

namespace App\Services\Project;

use App\Enums\ProjectExpenseStatus;
use App\Enums\RefundStatus;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use App\Models\ProjectExpenseRefundHistory;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * MAKER — refund/pemulihan dana perbelanjaan projek. TIADA kesan ledger.
 * Refund hanya boleh dibuat terhadap perbelanjaan yang telah DISAHKAN dan
 * jumlah terkumpul tidak boleh melebihi jumlah perbelanjaan itu.
 */
class ProjectRefundService
{
    public function __construct(private readonly AuditService $audit) {}

    public function createDraft(ProjectExpense $expense, User $maker, array $data): ProjectExpenseRefund
    {
        return DB::transaction(function () use ($expense, $maker, $data) {
            $expense = ProjectExpense::whereKey($expense->id)->lockForUpdate()->firstOrFail();
            $expense->loadMissing('project');

            $this->assertRefundable($expense);

            $amount = Money::of((string) $data['amount']);
            $this->assertAmountWithinRemaining($expense, $amount, null);

            $refund = ProjectExpenseRefund::create([
                'project_expense_id' => $expense->id,
                'project_id' => $expense->project_id,
                'amount' => $amount->value(),
                'reason' => $data['reason'],
                'reference_number' => $data['reference_number'] ?? null,
                'refund_date' => $data['refund_date'],
                'status' => RefundStatus::DRAFT,
                'created_by' => $maker->id,
            ]);

            $this->history($refund, null, RefundStatus::DRAFT, $maker, 'Refund dicipta');
            $this->audit->log('REFUND_CREATED', $refund, null, [
                'amount' => $amount->value(), 'project_expense_id' => $expense->id,
            ]);

            return $refund;
        });
    }

    public function update(ProjectExpenseRefund $refund, User $maker, array $data): ProjectExpenseRefund
    {
        return DB::transaction(function () use ($refund, $maker, $data) {
            $refund = ProjectExpenseRefund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            if (! $refund->isEditableByMaker()) {
                throw new ProjectException('Refund ini tidak boleh disunting.');
            }

            $expense = ProjectExpense::whereKey($refund->project_expense_id)->lockForUpdate()->firstOrFail();
            $amount = Money::of((string) $data['amount']);
            $this->assertAmountWithinRemaining($expense, $amount, $refund->id);

            $refund->update([
                'amount' => $amount->value(),
                'reason' => $data['reason'],
                'reference_number' => $data['reference_number'] ?? null,
                'refund_date' => $data['refund_date'],
            ]);

            $this->audit->log('REFUND_UPDATED', $refund, null, ['amount' => $amount->value()]);

            return $refund;
        });
    }

    public function submit(ProjectExpenseRefund $refund, User $maker): ProjectExpenseRefund
    {
        return DB::transaction(function () use ($refund, $maker) {
            $refund = ProjectExpenseRefund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            $expense = ProjectExpense::whereKey($refund->project_expense_id)->lockForUpdate()->firstOrFail();
            $expense->loadMissing('project');

            if (! in_array($refund->status, [RefundStatus::DRAFT, RefundStatus::REVISION_REQUIRED], true)) {
                throw new ProjectException('Hanya refund Draf/Perlu Pembetulan boleh dihantar.');
            }
            $this->assertRefundable($expense);

            // Sekurang-kurangnya satu dokumen bukti refund.
            if ($refund->documents()->count() < 1) {
                throw new ProjectException('Sekurang-kurangnya satu dokumen bukti refund diperlukan sebelum penghantaran.');
            }

            $amount = $refund->amountMoney();
            $this->assertAmountWithinRemaining($expense, $amount, $refund->id);

            $from = $refund->status;
            $wasRevision = $from === RefundStatus::REVISION_REQUIRED;

            $refund->update([
                'status' => RefundStatus::PENDING_VERIFICATION,
                'submitted_by' => $maker->id,
                'submitted_at' => now(),
                'revision_number' => $wasRevision ? $refund->revision_number + 1 : $refund->revision_number,
            ]);

            $this->history($refund, $from, RefundStatus::PENDING_VERIFICATION, $maker, 'Dihantar untuk pengesahan');
            $this->audit->log('REFUND_SUBMITTED', $refund, null, ['amount' => $amount->value()]);

            return $refund;
        });
    }

    /** Perbelanjaan mesti DISAHKAN & projek belum ditutup. */
    private function assertRefundable(ProjectExpense $expense): void
    {
        if ($expense->status !== ProjectExpenseStatus::VERIFIED) {
            throw new ProjectException('Refund hanya boleh dibuat terhadap perbelanjaan yang telah DISAHKAN.');
        }
        if ($expense->project->isClosed()) {
            throw new ProjectException('Projek telah ditutup — refund tidak boleh diproses. (Had: pembukaan semula projek tidak disokong.)');
        }
    }

    /**
     * Jumlah refund (terkumpul disahkan + belum selesai, kecuali refund semasa)
     * tidak boleh melebihi jumlah perbelanjaan.
     */
    private function assertAmountWithinRemaining(ProjectExpense $expense, Money $amount, ?int $excludeRefundId): void
    {
        if (! $amount->isPositive()) {
            throw new ProjectException('Jumlah refund mesti lebih daripada sifar.');
        }

        // Refund DISAHKAN + belum selesai (draft/pending/revision) mengunci baki,
        // supaya jumlah keseluruhan tidak boleh melebihi perbelanjaan.
        $committedStatuses = [
            RefundStatus::VERIFIED->value,
            RefundStatus::DRAFT->value,
            RefundStatus::PENDING_VERIFICATION->value,
            RefundStatus::REVISION_REQUIRED->value,
        ];

        $used = Money::zero();
        $query = $expense->refunds()->whereIn('status', $committedStatuses);
        if ($excludeRefundId !== null) {
            $query->where('id', '!=', $excludeRefundId);
        }
        foreach ($query->get() as $other) {
            $used = $used->plus($other->amountMoney());
        }

        $remaining = $expense->amountMoney()->minus($used);
        if ($amount->greaterThan($remaining)) {
            throw new ProjectException(sprintf(
                'Jumlah refund (RM%s) melebihi baki boleh dipulangkan (RM%s).',
                $amount->format(), $remaining->format(),
            ));
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
