<?php

namespace Tests\Feature;

use App\Enums\RefundStatus;
use App\Models\BudgetTransaction;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectFinancialService;
use App\Services\Project\ProjectRefundService;
use App\Services\Project\ProjectRefundVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ProjectRefundTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function maker(): ProjectRefundService
    {
        return app(ProjectRefundService::class);
    }

    private function checkerSvc(): ProjectRefundVerificationService
    {
        return app(ProjectRefundVerificationService::class);
    }

    private function draftRefund(ProjectExpense $expense, string $amount): ProjectExpenseRefund
    {
        return $this->maker()->createDraft($expense, $this->financeMaker(), [
            'refund_date' => now()->toDateString(),
            'reference_number' => 'RF/'.fake()->unique()->numerify('####'),
            'reason' => 'Lebihan bayaran', 'amount' => $amount,
        ]);
    }

    private function refundCount(Project $project): int
    {
        return BudgetTransaction::where('project_id', $project->id)->where('type', 'refund')->count();
    }

    private function verifiedExpense(Project $project, string $amount): ProjectExpense
    {
        return $this->verifyExpense($project, $amount);
    }

    public function test_refund_only_against_verified_expense(): void
    {
        $project = $this->startedProject('85000.00');
        $maker = $this->financeMaker();
        // Perbelanjaan DRAF (belum disahkan).
        $expense = app(\App\Services\Project\ProjectExpenseService::class)->createDraft($project, $maker, [
            'expense_date' => now()->toDateString(), 'reference_number' => 'INV/1', 'description' => 'x', 'amount' => '10000.00',
        ]);

        $this->expectException(ProjectException::class);
        $this->draftRefund($expense->fresh(), '1000.00');
    }

    public function test_verified_refund_posts_exactly_one_refund_transaction(): void
    {
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');

        $refund = $this->draftRefund($expense, '10000.00');
        $this->attachEvidence($refund);
        $this->maker()->submit($refund, $this->financeMaker());
        $this->checkerSvc()->verify($refund->fresh(), $this->financeChecker());

        $this->assertSame(RefundStatus::VERIFIED, $refund->fresh()->status);
        $this->assertSame(1, $this->refundCount($project));
        $this->assertDatabaseHas('budget_transactions', [
            'project_expense_refund_id' => $refund->id, 'type' => 'refund', 'amount' => '10000.00',
        ]);
    }

    public function test_draft_refund_has_no_ledger_effect(): void
    {
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');

        $refund = $this->draftRefund($expense, '10000.00');
        $this->assertSame(0, $this->refundCount($project));

        $this->attachEvidence($refund);
        $this->maker()->submit($refund, $this->financeMaker());
        $this->assertSame(0, $this->refundCount($project)); // masih tiada REFUND sehingga disahkan
    }

    public function test_maker_cannot_verify_own_refund(): void
    {
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');
        $maker = $this->financeMaker();
        $maker->givePermissionTo('refunds.verify');

        $refund = $this->maker()->createDraft($expense, $maker, [
            'refund_date' => now()->toDateString(), 'reference_number' => 'RF/1', 'reason' => 'x', 'amount' => '5000.00',
        ]);
        $this->attachEvidence($refund);
        $this->maker()->submit($refund, $maker);

        $this->expectException(ProjectException::class);
        $this->checkerSvc()->verify($refund->fresh(), $maker);
    }

    public function test_refund_requires_evidence_before_submit(): void
    {
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');
        $refund = $this->draftRefund($expense, '5000.00');

        $this->expectException(ProjectException::class);
        $this->maker()->submit($refund, $this->financeMaker());
    }

    public function test_cumulative_refund_cannot_exceed_expense_amount(): void
    {
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');

        // Refund pertama 30k (disahkan).
        $r1 = $this->draftRefund($expense, '30000.00');
        $this->attachEvidence($r1);
        $this->maker()->submit($r1, $this->financeMaker());
        $this->checkerSvc()->verify($r1->fresh(), $this->financeChecker());

        // Refund kedua 15k → 30k + 15k = 45k > 40k → ditolak semasa cipta.
        $this->expectException(ProjectException::class);
        $this->draftRefund($expense->fresh(), '15000.00');
    }

    public function test_reconciliation_after_refund_holds(): void
    {
        // Approved 85k; belanja 40k; refund 10k → net spent 30k, outstanding 45k.
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');

        $refund = $this->draftRefund($expense, '10000.00');
        $this->attachEvidence($refund);
        $this->maker()->submit($refund, $this->financeMaker());
        $this->checkerSvc()->verify($refund->fresh(), $this->financeChecker());

        $finance = app(ProjectFinancialService::class)->summary($project->fresh());
        $this->assertSame('40000.00', $finance['gross']->value());
        $this->assertSame('10000.00', $finance['refunded']->value());
        $this->assertSame('30000.00', $finance['spent']->value());        // net
        $this->assertSame('55000.00', $finance['outstanding']->value());  // 85k - 30k net
        // Approved = Outstanding + NetSpent + Released
        $recomputed = $finance['outstanding']->plus($finance['spent'])->plus($finance['released']);
        $this->assertSame($finance['approved']->value(), $recomputed->value());

        app(ProjectFinancialService::class)->assertConsistent($project->fresh());
    }

    public function test_original_expenditure_ledger_is_untouched_by_refund(): void
    {
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');

        $original = BudgetTransaction::where('project_expense_id', $expense->id)->where('type', 'expenditure')->firstOrFail();

        $refund = $this->draftRefund($expense, '10000.00');
        $this->attachEvidence($refund);
        $this->maker()->submit($refund, $this->financeMaker());
        $this->checkerSvc()->verify($refund->fresh(), $this->financeChecker());

        // Transaksi EXPENDITURE asal kekal sama (tiada suntingan/pemadaman).
        $this->assertDatabaseHas('budget_transactions', ['id' => $original->id, 'type' => 'expenditure', 'amount' => '40000.00']);
        $this->assertSame(1, BudgetTransaction::where('project_expense_id', $expense->id)->where('type', 'expenditure')->count());
    }

    public function test_unauthorized_role_cannot_verify_refund_via_http(): void
    {
        $project = $this->startedProject('85000.00');
        $expense = $this->verifiedExpense($project, '40000.00');
        $refund = $this->draftRefund($expense, '5000.00');
        $this->attachEvidence($refund);
        $this->maker()->submit($refund, $this->financeMaker());

        $alp = \App\Models\User::factory()->create()->assignRole(\App\Enums\RoleName::ALP->value);
        $this->actingAs($alp)->post(route('refunds.verify', $refund))->assertForbidden();
    }
}
