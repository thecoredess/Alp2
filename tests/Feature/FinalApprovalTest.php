<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\BudgetTransactionType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Notifications\ApplicationWorkflowNotification;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApprovalService;
use App\Services\Budget\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/** Kelulusan akhir: Pengarah JP (satu aras). PEPU makluman sahaja. */
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
        $app = $this->submitted($alp, $year, '2500.00');

        $this->expectException(ApplicationException::class);
        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
    }

    public function test_pengarah_approval_creates_exactly_one_commitment(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

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

    public function test_pepu_cannot_approve_pending_application(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00'));

        $this->expectException(ApplicationException::class);
        $this->approvals()->approve($app, $this->userWithRole(RoleName::PENGURUSAN->value), null);
    }

    public function test_unauthorized_role_cannot_approve_twice(): void
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

        $this->assertSame('1999.99', BudgetTransaction::where('application_id', $app->id)->where('type', 'commitment')->value('amount'));
    }

    public function test_budget_recheck_does_not_double_subtract_own_pending(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '10000.00');

        $this->submitted($alp, $year, '2000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '7000.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

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

    public function test_pengarah_approval_notifies_pepu_and_kewangan(): void
    {
        Notification::fake();

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $pepu = $this->userWithRole(RoleName::PENGURUSAN->value);

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        Notification::assertSentTo(
            $pepu,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'approved_pepu_informed',
        );

        Notification::assertSentTo(
            $kewangan,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'awaiting_payment',
        );

        Notification::assertSentTo(
            $owner,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'approved',
        );
    }

    public function test_rejected_application_does_not_notify_kewangan_jp(): void
    {
        Notification::fake();

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->approvals()->reject($app, $this->userWithRole(RoleName::PELULUS->value), 'Tidak lengkap');

        Notification::assertNotSentTo($kewangan, ApplicationWorkflowNotification::class);
    }
}
