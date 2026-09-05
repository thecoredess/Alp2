<?php

namespace Tests\Feature;

use App\Enums\ProjectExpenseStatus;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Models\FinancialYear;
use App\Models\Project;
use App\Models\User;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectExpenseService;
use App\Services\Project\ProjectExpenseVerificationService;
use App\Services\Project\ProjectFinancialService;
use App\Services\Project\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ProjectExpenseTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function startedProject(string $amount = '85000.00'): Project
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '1000000.00');
        $project = $this->approvedProject($alp, $year, $amount);
        app(ProjectService::class)->start($project, $this->projectOperator());

        return $project->fresh();
    }

    private function maker(): ProjectExpenseService
    {
        return app(ProjectExpenseService::class);
    }

    private function checkerSvc(): ProjectExpenseVerificationService
    {
        return app(ProjectExpenseVerificationService::class);
    }

    private function draft(Project $project, User $maker, string $amount)
    {
        $expense = $this->maker()->createDraft($project, $maker, [
            'expense_date' => now()->toDateString(),
            'reference_number' => 'INV/'.fake()->unique()->numerify('####'),
            'description' => 'Bayaran', 'amount' => $amount,
        ]);
        $this->attachEvidence($expense);

        return $expense;
    }

    private function expCount(Project $project): int
    {
        return BudgetTransaction::where('project_id', $project->id)->where('type', 'expenditure')->count();
    }

    public function test_draft_and_submit_have_no_ledger_effect(): void
    {
        $project = $this->startedProject();
        $maker = $this->financeMaker();

        $exp = $this->draft($project, $maker, '20000.00');
        $this->assertSame(0, $this->expCount($project));

        $this->maker()->submit($exp, $maker);
        $this->assertSame(ProjectExpenseStatus::PENDING_VERIFICATION, $exp->fresh()->status);
        $this->assertSame(0, $this->expCount($project)); // masih tiada EXPENDITURE
    }

    public function test_verified_expense_creates_exactly_one_expenditure(): void
    {
        $project = $this->startedProject();
        $maker = $this->financeMaker();
        $exp = $this->draft($project, $maker, '20000.00');
        $this->maker()->submit($exp, $maker);

        $this->checkerSvc()->verify($exp->fresh(), $this->financeChecker());

        $this->assertSame(ProjectExpenseStatus::VERIFIED, $exp->fresh()->status);
        $this->assertSame(1, $this->expCount($project));
    }

    public function test_maker_cannot_verify_own_expense(): void
    {
        $project = $this->startedProject();
        $maker = $this->financeMaker();
        $maker->givePermissionTo('expenses.verify');
        $exp = $this->draft($project, $maker, '20000.00');
        $this->maker()->submit($exp, $maker);

        $this->expectException(ProjectException::class);
        $this->checkerSvc()->verify($exp->fresh(), $maker); // maker == checker
    }

    public function test_unauthorized_role_cannot_verify_via_http(): void
    {
        $project = $this->startedProject();
        $maker = $this->financeMaker();
        $exp = $this->draft($project, $maker, '20000.00');
        $this->maker()->submit($exp, $maker);

        $alp = User::factory()->create()->assignRole(\App\Enums\RoleName::ALP->value);
        $this->actingAs($alp)->post(route('expenses.verify', $exp))->assertForbidden();
    }

    public function test_expense_exceeding_outstanding_commitment_is_rejected(): void
    {
        $project = $this->startedProject('85000.00');
        $maker = $this->financeMaker();
        $exp = $this->draft($project, $maker, '90000.00'); // > 85000 outstanding
        $this->maker()->submit($exp, $maker);

        $this->expectException(ProjectException::class);
        $this->checkerSvc()->verify($exp->fresh(), $this->financeChecker());
    }

    public function test_commitment_consumption_is_exact(): void
    {
        // Approved 85,000; belanja 20,000 + 30,000 → committed 35,000, spent 50,000.
        $project = $this->startedProject('85000.00');
        $maker = $this->financeMaker();
        $checker = $this->financeChecker();

        foreach (['20000.00', '30000.00'] as $amount) {
            $exp = $this->draft($project, $maker, $amount);
            $this->maker()->submit($exp, $maker);
            $this->checkerSvc()->verify($exp->fresh(), $checker);
        }

        $finance = app(ProjectFinancialService::class)->summary($project);
        $this->assertSame('35000.00', $finance['outstanding']->value());
        $this->assertSame('50000.00', $finance['spent']->value());
    }

    public function test_expense_amount_precision(): void
    {
        $project = $this->startedProject('85000.00');
        $maker = $this->financeMaker();
        $exp = $this->draft($project, $maker, '0.01');
        $this->maker()->submit($exp, $maker);
        $this->checkerSvc()->verify($exp->fresh(), $this->financeChecker());

        $this->assertDatabaseHas('budget_transactions', ['project_expense_id' => $exp->id, 'type' => 'expenditure', 'amount' => '0.01']);
    }
}
