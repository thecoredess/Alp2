<?php

namespace Tests\Feature;

use App\Enums\ProjectExpenseStatus;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Models\ProjectExpense;
use App\Services\Reports\DataQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class DataQualityExceptionTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function service(): DataQualityService
    {
        return app(DataQualityService::class);
    }

    public function test_healthy_project_has_no_exceptions(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '100000.00');
        $project = $this->approvedProject($alp, $year, '40000.00');
        app(\App\Services\Project\ProjectService::class)->start($project, $this->projectOperator());
        $this->verifyExpense($project->fresh(), '15000.00');

        $this->assertSame(0, $this->service()->totalExceptions($year->id));
    }

    public function test_detects_verified_expense_missing_ledger_transaction(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '100000.00');
        $project = $this->approvedProject($alp, $year, '40000.00');
        app(\App\Services\Project\ProjectService::class)->start($project, $this->projectOperator());

        // Fikstur ROSAK khusus-ujian: perbelanjaan DISAHKAN tanpa transaksi ledger.
        ProjectExpense::create([
            'project_id' => $project->id, 'expense_date' => now()->toDateString(),
            'reference_number' => 'BROKEN/1', 'description' => 'Tiada ledger', 'amount' => '5000.00',
            'status' => ProjectExpenseStatus::VERIFIED, 'created_by' => $this->financeMaker()->id,
            'verified_by' => $this->financeChecker()->id, 'verified_at' => now(),
        ]);

        $checks = $this->service()->run($year->id);
        $this->assertGreaterThanOrEqual(1, $checks['verified_expense_no_ledger']['items']->count());
        $this->assertGreaterThanOrEqual(1, $this->service()->totalExceptions($year->id));
    }

    public function test_detects_approved_application_without_project(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        // Permohonan APPROVED tanpa projek (fikstur ujian).
        \App\Models\Application::factory()->create([
            'alp_id' => $alp->id, 'financial_year_id' => $year->id,
            'status' => \App\Enums\ApplicationStatus::APPROVED,
        ]);

        $checks = $this->service()->run($year->id);
        $this->assertGreaterThanOrEqual(1, $checks['approved_without_project']['items']->count());
    }
}
