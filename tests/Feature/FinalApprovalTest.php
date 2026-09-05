<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\BudgetTransactionType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApprovalService;
use App\Services\Budget\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/**
 * Matriks URS v1.2 (ApprovalLevelSeeder):
 * - ≤ RM3,000 → Aras 1 Peraku (pelulus) sahaja
 * - > RM3,000 → Aras 1 Peraku kemudian Aras 2 PEPU (pengurusan)
 */
class FinalApprovalTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    private function approvals(): ApprovalService
    {
        return app(ApprovalService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_cannot_approve_before_required_reviews(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00'); // masih SUBMITTED

        $this->expectException(ApplicationException::class);
        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
    }

    public function test_single_level_final_approval_creates_exactly_one_commitment(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00')); // aras 1 sahaja

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $app->refresh();
        $this->assertSame(ApplicationStatus::APPROVED, $app->status);
        $this->assertSame(1, BudgetTransaction::where('application_id', $app->id)->where('type', BudgetTransactionType::COMMITMENT->value)->count());
    }

    public function test_pending_decreases_committed_increases_available_decreases(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $budget = app(BudgetService::class);
        $appBudget = app(\App\Services\Application\ApplicationBudgetService::class);

        $this->assertSame('0.00', $budget->summaryFor($alp->id, $year->id)->committed->value());
        $this->assertSame('2500.00', $appBudget->pendingRequest($alp->id, $year->id)->value());

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $summary = $budget->summaryFor($alp->id, $year->id);
        $this->assertSame('2500.00', $summary->committed->value());
        $this->assertSame('497500.00', $summary->available()->value());
        $this->assertSame('0.00', $appBudget->pendingRequest($alp->id, $year->id)->value());
    }

    public function test_multi_level_sequence_enforced(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00')); // aras 1 & 2

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
        $app->refresh();
        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->status);
        $this->assertSame(0, BudgetTransaction::where('application_id', $app->id)->count());

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PENGURUSAN->value), null);
        $app->refresh();
        $this->assertSame(ApplicationStatus::APPROVED, $app->status);
        $this->assertSame(1, BudgetTransaction::where('application_id', $app->id)->where('type', 'commitment')->count());
    }

    public function test_unauthorized_role_cannot_approve_higher_level(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $this->expectException(ApplicationException::class);
        $this->approvals()->approve($app->fresh(), $this->userWithRole(RoleName::PELULUS->value), null);
    }

    public function test_commitment_amount_is_exact(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '1999.99'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $this->assertDatabaseHas('budget_transactions', [
            'application_id' => $app->id,
            'type' => 'commitment',
            'amount' => '1999.99',
        ]);
    }

    public function test_budget_recheck_does_not_double_subtract_own_pending(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '10000.00');

        $this->submitted($alp, $year, '2000.00'); // pending lain
        $app = $this->toPendingApproval($this->submitted($alp, $year, '7000.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
        $this->approvals()->approve($app->fresh(), $this->userWithRole(RoleName::PENGURUSAN->value), null);

        $this->assertSame(ApplicationStatus::APPROVED, $app->fresh()->status);
        $this->assertDatabaseHas('budget_transactions', ['application_id' => $app->id, 'type' => 'commitment', 'amount' => '7000.00']);
    }

    public function test_approval_creates_audit_history_and_record(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $this->assertDatabaseHas('application_approvals', ['application_id' => $app->id, 'decision' => 'approved']);
        $this->assertDatabaseHas('application_status_histories', ['application_id' => $app->id, 'to_status' => 'approved']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'APPLICATION_APPROVED', 'entity_id' => $app->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'BUDGET_COMMITMENT_CREATED']);
    }
}
