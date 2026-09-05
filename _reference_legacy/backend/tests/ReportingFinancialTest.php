<?php

namespace Tests\Feature;

use App\Models\Alp;
use App\Models\FinancialYear;
use App\Services\Reports\FinancialReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ReportingFinancialTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function service(): FinancialReportService
    {
        return app(FinancialReportService::class);
    }

    public function test_alp_breakdown_is_exact_from_ledger(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '100000.00');
        // Projek diluluskan 40k (komitmen), belanja 15k disahkan.
        $project = $this->approvedProject($alp, $year, '40000.00');
        app(\App\Services\Project\ProjectService::class)->start($project, $this->projectOperator());
        $this->verifyExpense($project->fresh(), '15000.00');

        $b = $this->service()->alpBreakdown($alp->id, $year->id);

        $this->assertSame('100000.00', $b->allocation->value());
        $this->assertSame('25000.00', $b->committed->value());   // 40k komitmen − 15k belanja
        $this->assertSame('15000.00', $b->grossSpent->value());
        $this->assertSame('0.00', $b->refunded->value());
        $this->assertSame('15000.00', $b->netSpent()->value());
        $this->assertSame('60000.00', $b->available()->value()); // 100k − 25k − 15k
    }

    public function test_refund_reduces_net_spent_in_breakdown(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '100000.00');
        $project = $this->approvedProject($alp, $year, '40000.00');
        app(\App\Services\Project\ProjectService::class)->start($project, $this->projectOperator());
        $expense = $this->verifyExpense($project->fresh(), '15000.00');
        $this->verifyRefund($expense, '3000.00');

        $b = $this->service()->alpBreakdown($alp->id, $year->id);
        $this->assertSame('15000.00', $b->grossSpent->value());
        $this->assertSame('3000.00', $b->refunded->value());
        $this->assertSame('12000.00', $b->netSpent()->value());   // 15k − 3k
        // Refund memulihkan komitmen (28k), bukan available. Available kekal.
        $this->assertSame('28000.00', $b->committed->value());    // 40k − 15k + 3k
        $this->assertSame('60000.00', $b->available()->value());  // 100k − 28k − 12k
    }

    public function test_pending_application_not_counted_as_committed(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '100000.00');
        // Permohonan dihantar (menunggu) — 20k. Bukan komitmen.
        $this->toPendingApproval($this->submitted($alp, $year, '20000.00'));

        $b = $this->service()->alpBreakdown($alp->id, $year->id);
        $this->assertSame('20000.00', $b->pending->value());
        $this->assertSame('0.00', $b->committed->value());
        $this->assertSame('100000.00', $b->available()->value());        // pending tidak mengurangkan available
        $this->assertSame('80000.00', $b->projectedAvailable()->value()); // available − pending
    }

    public function test_financial_year_isolation(): void
    {
        $alp = Alp::factory()->create();
        $year1 = FinancialYear::factory()->create(['year' => 2025, 'is_active' => false]);
        $year2 = FinancialYear::factory()->active()->create(['year' => 2026]);
        $this->allocate($alp, $year1, '50000.00');
        $this->allocate($alp, $year2, '80000.00');

        $this->assertSame('50000.00', $this->service()->alpBreakdown($alp->id, $year1->id)->allocation->value());
        $this->assertSame('80000.00', $this->service()->alpBreakdown($alp->id, $year2->id)->allocation->value());
        // Totals tidak mencampurkan tahun.
        $this->assertSame('80000.00', $this->service()->totals($year2->id)->allocation->value());
    }

    public function test_healthy_data_has_zero_reconciliation_exceptions(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '100000.00');
        $project = $this->approvedProject($alp, $year, '40000.00');
        app(\App\Services\Project\ProjectService::class)->start($project, $this->projectOperator());
        $this->verifyExpense($project->fresh(), '15000.00');

        $this->assertCount(0, $this->service()->reconciliationExceptions($year->id));
    }

    /** Cipta + hantar + sahkan refund (dengan bukti). */
    private function verifyRefund(\App\Models\ProjectExpense $expense, string $amount): void
    {
        $maker = $this->financeMaker();
        $refund = app(\App\Services\Project\ProjectRefundService::class)->createDraft($expense, $maker, [
            'refund_date' => now()->toDateString(), 'reference_number' => 'RF/'.fake()->unique()->numerify('####'),
            'reason' => 'Pemulangan', 'amount' => $amount,
        ]);
        $this->attachEvidence($refund);
        app(\App\Services\Project\ProjectRefundService::class)->submit($refund, $maker);
        app(\App\Services\Project\ProjectRefundVerificationService::class)->verify($refund->fresh(), $this->financeChecker());
    }
}
