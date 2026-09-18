<?php

namespace App\Services\Project;

use App\Enums\ProjectExpenseStatus;
use App\Enums\ProjectStatus;
use App\Models\Allocation;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseHistory;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use Illuminate\Support\Facades\DB;

/**
 * CHECKER — pengesahan perbelanjaan. HANYA di sini EXPENDITURE diposkan ke ledger.
 * Menguatkuasakan maker ≠ checker, had komitmen, atomicity & idempotensi.
 */
class ProjectExpenseVerificationService
{
    public function __construct(
        private readonly BudgetService $budget,
        private readonly ProjectFinancialService $financial,
        private readonly AuditService $audit,
    ) {}

    public function verify(ProjectExpense $expense, User $checker): ProjectExpense
    {
        return DB::transaction(function () use ($expense, $checker) {
            $expense = ProjectExpense::whereKey($expense->id)->lockForUpdate()->firstOrFail();

            // (3) Kunci projek.
            $project = Project::whereKey($expense->project_id)->lockForUpdate()->firstOrFail();

            // (6) Status betul.
            if ($expense->status !== ProjectExpenseStatus::PENDING_VERIFICATION) {
                throw new ProjectException('Perbelanjaan tidak berada pada peringkat pengesahan.');
            }
            // (5) MAKER ≠ CHECKER.
            $this->assertMakerChecker($expense, $checker);

            // (7) Projek tidak ditutup & aktif.
            if ($project->status === ProjectStatus::CLOSED) {
                throw new ProjectException('Projek telah ditutup.');
            }
            if (! $project->status->isActive() && $project->status !== ProjectStatus::COMPLETED) {
                throw new ProjectException('Projek tidak dalam keadaan menerima perbelanjaan.');
            }

            $amount = $expense->amountMoney();
            if (! $amount->isPositive()) {
                throw new ProjectException('Jumlah tidak sah.');
            }

            // (9) Belum diposkan (idempotensi).
            if ($expense->transaction()->exists()) {
                throw new ProjectException('Perbelanjaan ini telah diposkan ke lejar.');
            }

            // (4) Kunci allocation.
            $allocation = Allocation::where('alp_id', $project->alp_id)
                ->where('financial_year_id', $project->financial_year_id)
                ->lockForUpdate()->first();
            if (! $allocation) {
                throw new ProjectException('Tiada peruntukan berkaitan.');
            }

            // (10) Tidak melebihi baki komitmen projek (silang-projek dilindungi — skop project_id).
            $outstanding = $this->financial->outstandingCommitment($project);
            if ($amount->greaterThan($outstanding)) {
                throw new ProjectException(sprintf(
                    'Perbelanjaan (RM%s) melebihi baki komitmen projek (RM%s).',
                    $amount->format(), $outstanding->format(),
                ));
            }

            // (11) Tandakan disahkan.
            $from = $expense->status;
            $expense->update([
                'status' => ProjectExpenseStatus::VERIFIED,
                'verified_by' => $checker->id,
                'verified_at' => now(),
            ]);

            // (12) Poskan EXPENDITURE (immutable ledger; unique project_expense_id).
            $this->budget->recordExpenditure($allocation, $amount, $project, $expense);

            $this->history($expense, $from, ProjectExpenseStatus::VERIFIED, $checker, 'Disahkan & diposkan ke lejar');
            $this->audit->log('EXPENSE_VERIFIED', $expense, null, ['amount' => $amount->value(), 'checker_id' => $checker->id]);

            return $expense;
        });
    }

    public function reject(ProjectExpense $expense, User $checker, string $reason): ProjectExpense
    {
        return DB::transaction(function () use ($expense, $checker, $reason) {
            $expense = ProjectExpense::whereKey($expense->id)->lockForUpdate()->firstOrFail();
            if ($expense->status !== ProjectExpenseStatus::PENDING_VERIFICATION) {
                throw new ProjectException('Perbelanjaan tidak berada pada peringkat pengesahan.');
            }
            $this->assertMakerChecker($expense, $checker);

            $from = $expense->status;
            $expense->update([
                'status' => ProjectExpenseStatus::REJECTED,
                'rejected_by' => $checker->id, 'rejected_at' => now(), 'rejection_reason' => $reason,
            ]);
            $this->history($expense, $from, ProjectExpenseStatus::REJECTED, $checker, 'Ditolak');
            $this->audit->log('EXPENSE_REJECTED', $expense, null, ['reason' => $reason]);

            return $expense;
        });
    }

    public function returnForRevision(ProjectExpense $expense, User $checker, string $reason): ProjectExpense
    {
        return DB::transaction(function () use ($expense, $checker, $reason) {
            $expense = ProjectExpense::whereKey($expense->id)->lockForUpdate()->firstOrFail();
            if ($expense->status !== ProjectExpenseStatus::PENDING_VERIFICATION) {
                throw new ProjectException('Perbelanjaan tidak berada pada peringkat pengesahan.');
            }
            $this->assertMakerChecker($expense, $checker);

            $from = $expense->status;
            $expense->update([
                'status' => ProjectExpenseStatus::REVISION_REQUIRED,
                'returned_by' => $checker->id, 'returned_at' => now(), 'return_reason' => $reason,
            ]);
            $this->history($expense, $from, ProjectExpenseStatus::REVISION_REQUIRED, $checker, 'Dikembalikan untuk pembetulan');
            $this->audit->log('EXPENSE_RETURNED', $expense, null, ['reason' => $reason]);

            return $expense;
        });
    }

    private function assertMakerChecker(ProjectExpense $expense, User $checker): void
    {
        if ($checker->id === $expense->created_by || $checker->id === $expense->submitted_by) {
            throw new ProjectException('MAKER ≠ CHECKER: anda tidak boleh mengesahkan perbelanjaan yang anda cipta/hantar.');
        }
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
