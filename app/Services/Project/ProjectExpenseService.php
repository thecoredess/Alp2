<?php

namespace App\Services\Project;

use App\Enums\ProjectExpenseStatus;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseHistory;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * MAKER — perbelanjaan projek. TIADA kesan ledger (cadangan sahaja).
 */
class ProjectExpenseService
{
    public function __construct(private readonly AuditService $audit) {}

    public function createDraft(Project $project, User $maker, array $data): ProjectExpense
    {
        if (! $project->status->isActive()) {
            throw new ProjectException('Perbelanjaan hanya boleh dicipta untuk projek yang aktif.');
        }
        $amount = Money::of((string) $data['amount']);
        if (! $amount->isPositive()) {
            throw new ProjectException('Jumlah perbelanjaan mesti lebih daripada sifar.');
        }

        $expense = $project->expenses()->create([
            'expense_date' => $data['expense_date'],
            'reference_number' => $data['reference_number'],
            'payee' => $data['payee'] ?? null,
            'description' => $data['description'],
            'amount' => $amount->value(),
            'status' => ProjectExpenseStatus::DRAFT,
            'created_by' => $maker->id,
        ]);

        $this->history($expense, null, ProjectExpenseStatus::DRAFT, $maker, 'Perbelanjaan dicipta');
        $this->audit->log('EXPENSE_CREATED', $expense, null, ['amount' => $amount->value(), 'project_id' => $project->id]);

        return $expense;
    }

    public function update(ProjectExpense $expense, User $maker, array $data): ProjectExpense
    {
        if (! $expense->isEditableByMaker()) {
            throw new ProjectException('Perbelanjaan ini tidak boleh disunting.');
        }
        $amount = Money::of((string) $data['amount']);
        if (! $amount->isPositive()) {
            throw new ProjectException('Jumlah mesti lebih daripada sifar.');
        }

        $expense->update([
            'expense_date' => $data['expense_date'],
            'reference_number' => $data['reference_number'],
            'payee' => $data['payee'] ?? null,
            'description' => $data['description'],
            'amount' => $amount->value(),
        ]);

        $this->audit->log('EXPENSE_UPDATED', $expense, null, ['amount' => $amount->value()]);

        return $expense;
    }

    public function submit(ProjectExpense $expense, User $maker): ProjectExpense
    {
        return DB::transaction(function () use ($expense, $maker) {
            $expense = ProjectExpense::whereKey($expense->id)->lockForUpdate()->firstOrFail();
            $expense->loadMissing('project');

            if (! in_array($expense->status, [ProjectExpenseStatus::DRAFT, ProjectExpenseStatus::REVISION_REQUIRED], true)) {
                throw new ProjectException('Hanya perbelanjaan Draf/Perlu Pembetulan boleh dihantar.');
            }
            if (! $expense->project->status->isActive()) {
                throw new ProjectException('Projek tidak aktif.');
            }
            if (! $expense->amountMoney()->isPositive()) {
                throw new ProjectException('Jumlah tidak sah.');
            }
            // Sekurang-kurangnya satu dokumen bukti perbelanjaan diperlukan.
            if ($expense->documents()->count() < 1) {
                throw new ProjectException('Sekurang-kurangnya satu dokumen bukti perbelanjaan diperlukan sebelum penghantaran.');
            }

            $from = $expense->status;
            $wasRevision = $from === ProjectExpenseStatus::REVISION_REQUIRED;

            $expense->update([
                'status' => ProjectExpenseStatus::PENDING_VERIFICATION,
                'submitted_by' => $maker->id,
                'submitted_at' => now(),
                'revision_number' => $wasRevision ? $expense->revision_number + 1 : $expense->revision_number,
            ]);

            $this->history($expense, $from, ProjectExpenseStatus::PENDING_VERIFICATION, $maker, 'Dihantar untuk pengesahan');
            $this->audit->log('EXPENSE_SUBMITTED', $expense, null, ['amount' => $expense->amount]);

            return $expense;
        });
    }

    private function history(ProjectExpense $expense, ?ProjectExpenseStatus $from, ProjectExpenseStatus $to, User $user, string $remarks): void
    {
        ProjectExpenseHistory::create([
            'project_expense_id' => $expense->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by' => $user->id,
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }
}
