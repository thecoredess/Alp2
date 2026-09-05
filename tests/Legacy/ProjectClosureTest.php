<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\BudgetTransaction;
use App\Models\Project;
use App\Services\Project\ProjectClosureService;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectFinancialService;
use App\Services\Project\ProjectReportService;
use App\Services\Project\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ProjectClosureTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function completeProject(Project $project): void
    {
        $op = $this->projectOperator();
        app(ProjectService::class)->updateProgress($project->fresh(), $op, 100, 'Siap');
        app(ProjectService::class)->complete($project->fresh(), $op);
        app(ProjectReportService::class)->submit($project->fresh(), $op, ['summary' => 'Ringkasan akhir']);
    }

    private function releaseCount(Project $project): int
    {
        return BudgetTransaction::where('project_id', $project->id)->where('type', 'commitment_release')->count();
    }

    public function test_closure_releases_unused_commitment_exactly(): void
    {
        // Approved 85,000; spent 81,700 → release 3,300; committed 0.
        $project = $this->startedProject('85000.00');
        $this->verifyExpense($project, '81700.00');
        $this->completeProject($project);

        app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());

        $project->refresh();
        $this->assertSame(ProjectStatus::CLOSED, $project->status);
        $finance = app(ProjectFinancialService::class)->summary($project);
        $this->assertSame('0.00', $finance['outstanding']->value());
        $this->assertSame('81700.00', $finance['spent']->value());
        $this->assertSame('3300.00', $finance['released']->value());
        $this->assertSame(1, $this->releaseCount($project));
    }

    public function test_zero_unused_commitment_creates_no_release_transaction(): void
    {
        // Approved 85,000; spent 85,000 → unused 0 → tiada transaksi pelepasan.
        $project = $this->startedProject('85000.00');
        $this->verifyExpense($project, '85000.00');
        $this->completeProject($project);

        app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());

        $this->assertSame(ProjectStatus::CLOSED, $project->fresh()->status);
        $this->assertSame(0, $this->releaseCount($project));
    }

    public function test_closure_before_completion_is_rejected(): void
    {
        $project = $this->startedProject('85000.00'); // masih IN_PROGRESS

        $this->expectException(ProjectException::class);
        app(ProjectClosureService::class)->close($project, $this->financeChecker());
    }

    public function test_closure_without_final_report_is_rejected(): void
    {
        $project = $this->startedProject('85000.00');
        $op = $this->projectOperator();
        app(ProjectService::class)->updateProgress($project->fresh(), $op, 100, 'Siap');
        app(ProjectService::class)->complete($project->fresh(), $op);
        // Tiada laporan dihantar.

        $this->expectException(ProjectException::class);
        app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());
    }

    public function test_closure_with_pending_expense_is_rejected(): void
    {
        $project = $this->startedProject('85000.00');
        // Perbelanjaan dihantar tetapi belum disahkan.
        $maker = $this->financeMaker();
        $exp = app(\App\Services\Project\ProjectExpenseService::class)->createDraft($project, $maker, [
            'expense_date' => now()->toDateString(), 'reference_number' => 'INV/9', 'description' => 'x', 'amount' => '1000.00',
        ]);
        $this->attachEvidence($exp);
        app(\App\Services\Project\ProjectExpenseService::class)->submit($exp, $maker);

        $this->completeProject($project);

        $this->expectException(ProjectException::class);
        app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());
    }

    public function test_double_closure_does_not_duplicate_release(): void
    {
        $project = $this->startedProject('85000.00');
        $this->verifyExpense($project, '81700.00');
        $this->completeProject($project);

        app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());
        try {
            app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());
        } catch (ProjectException $e) {
        }

        $this->assertSame(1, $this->releaseCount($project));
    }
}
