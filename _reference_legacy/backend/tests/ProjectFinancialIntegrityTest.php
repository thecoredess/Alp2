<?php

namespace Tests\Feature;

use App\Enums\BudgetTransactionType;
use App\Enums\ProjectExpenseStatus;
use App\Models\Allocation;
use App\Models\BudgetTransaction;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\User;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectExpenseService;
use App\Services\Project\ProjectExpenseVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ProjectFinancialIntegrityTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function pendingExpense(Project $project, string $amount, ?User $maker = null): ProjectExpense
    {
        $maker ??= $this->financeMaker();
        $exp = app(ProjectExpenseService::class)->createDraft($project, $maker, [
            'expense_date' => now()->toDateString(), 'reference_number' => 'INV/'.fake()->unique()->numerify('#####'),
            'description' => 'x', 'amount' => $amount,
        ]);
        $this->attachEvidence($exp);
        app(ProjectExpenseService::class)->submit($exp, $maker);

        return $exp->fresh();
    }

    private function expCount(Project $project): int
    {
        return BudgetTransaction::where('project_id', $project->id)->where('type', 'expenditure')->count();
    }

    public function test_double_verification_produces_one_expenditure(): void
    {
        $project = $this->startedProject('85000.00');
        $exp = $this->pendingExpense($project, '20000.00');
        $checker = $this->financeChecker();

        app(ProjectExpenseVerificationService::class)->verify($exp, $checker);
        try {
            app(ProjectExpenseVerificationService::class)->verify($exp->fresh(), $checker);
        } catch (ProjectException $e) {
        }

        $this->assertSame(1, $this->expCount($project));
    }

    public function test_db_constraint_blocks_duplicate_expenditure(): void
    {
        $project = $this->startedProject('85000.00');
        $exp = $this->pendingExpense($project, '20000.00');
        $allocation = Allocation::where('alp_id', $project->alp_id)->first();

        $row = [
            'allocation_id' => $allocation->id, 'alp_id' => $project->alp_id, 'financial_year_id' => $project->financial_year_id,
            'project_id' => $project->id, 'project_expense_id' => $exp->id,
            'type' => BudgetTransactionType::EXPENDITURE, 'amount' => '20000.00', 'created_at' => now(),
        ];
        BudgetTransaction::create($row);

        $this->expectException(\Illuminate\Database\QueryException::class);
        BudgetTransaction::create($row);
    }

    public function test_failure_during_posting_rolls_back(): void
    {
        $project = $this->startedProject('85000.00');
        $exp = $this->pendingExpense($project, '20000.00');
        $allocation = Allocation::where('alp_id', $project->alp_id)->first();

        // Suntik EXPENDITURE berkaitan untuk memaksa kegagalan "telah diposkan".
        BudgetTransaction::create([
            'allocation_id' => $allocation->id, 'alp_id' => $project->alp_id, 'financial_year_id' => $project->financial_year_id,
            'project_id' => $project->id, 'project_expense_id' => $exp->id,
            'type' => BudgetTransactionType::EXPENDITURE, 'amount' => '20000.00', 'created_at' => now(),
        ]);

        try {
            app(ProjectExpenseVerificationService::class)->verify($exp->fresh(), $this->financeChecker());
        } catch (ProjectException $e) {
        }

        // Status kekal (tiada keadaan separa).
        $this->assertSame(ProjectExpenseStatus::PENDING_VERIFICATION, $exp->fresh()->status);
    }

    public function test_two_expenses_cannot_overspend_commitment(): void
    {
        // Approved 10,000; dua perbelanjaan 7,000 → paling banyak satu boleh disahkan.
        $project = $this->startedProject('10000.00');
        $maker = $this->financeMaker();
        $checker = $this->financeChecker();

        $a = $this->pendingExpense($project, '7000.00', $maker);
        $b = $this->pendingExpense($project, '7000.00', $maker);

        app(ProjectExpenseVerificationService::class)->verify($a, $checker);
        try {
            app(ProjectExpenseVerificationService::class)->verify($b, $checker);
        } catch (ProjectException $e) {
        }

        $this->assertSame(1, $this->expCount($project)); // 7000 + 7000 > 10000
    }

    public function test_cross_project_commitment_is_isolated(): void
    {
        // Perbelanjaan projek A tidak boleh menggunakan komitmen projek B.
        $projectA = $this->startedProject('10000.00');
        $projectB = $this->startedProject('10000.00');

        // Perbelanjaan 15,000 pada A (melebihi komitmen A 10,000) walaupun jumlah kedua-dua projek 20,000.
        $exp = $this->pendingExpense($projectA, '15000.00');

        $this->expectException(ProjectException::class);
        app(ProjectExpenseVerificationService::class)->verify($exp, $this->financeChecker());
    }
}
