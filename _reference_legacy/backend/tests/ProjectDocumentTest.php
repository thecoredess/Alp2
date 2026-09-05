<?php

namespace Tests\Feature;

use App\Enums\ProjectDocumentType;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Services\Project\ProjectClosureService;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectExpenseService;
use App\Services\Project\ProjectReportService;
use App\Services\Project\ProjectService;
use Database\Seeders\ProjectDocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ProjectDocumentTest extends TestCase
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

    private function attachClosureDoc(Project $project, ProjectDocumentType $type): ProjectDocument
    {
        return ProjectDocument::create([
            'project_id' => $project->id,
            'category' => 'closure_evidence',
            'document_type' => $type,
            'original_filename' => 'doc.pdf',
            'stored_path' => 'test/'.fake()->unique()->numerify('########').'.pdf',
            'mime_type' => 'application/pdf', 'file_size' => 1024, 'sha256' => str_repeat('a', 64),
            'uploaded_by' => $this->projectOperator()->id,
        ]);
    }

    public function test_expense_submit_blocked_without_evidence(): void
    {
        $project = $this->startedProject('85000.00');
        $maker = $this->financeMaker();
        $expense = app(ProjectExpenseService::class)->createDraft($project, $maker, [
            'expense_date' => now()->toDateString(), 'reference_number' => 'INV/1', 'description' => 'x', 'amount' => '10000.00',
        ]);

        $this->expectException(ProjectException::class);
        app(ProjectExpenseService::class)->submit($expense, $maker);
    }

    public function test_closure_blocked_without_required_documents(): void
    {
        $this->seed(ProjectDocumentRequirementSeeder::class);
        $project = $this->startedProject('85000.00');
        $this->verifyExpense($project, '80000.00');
        $this->completeProject($project);

        // Tiada dokumen penutupan → dihalang (CSR wajib: Laporan Akhir + Gambar Penyiapan).
        $this->expectException(ProjectException::class);
        app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());
    }

    public function test_closure_succeeds_when_required_documents_present(): void
    {
        $this->seed(ProjectDocumentRequirementSeeder::class);
        $project = $this->startedProject('85000.00');
        $this->verifyExpense($project, '80000.00');
        $this->completeProject($project);

        $this->attachClosureDoc($project, ProjectDocumentType::FINAL_REPORT);
        $this->attachClosureDoc($project, ProjectDocumentType::COMPLETION_PHOTO);

        app(ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());
        $this->assertSame(\App\Enums\ProjectStatus::CLOSED, $project->fresh()->status);
    }

    public function test_missing_closure_documents_lists_required_types(): void
    {
        $this->seed(ProjectDocumentRequirementSeeder::class);
        $project = $this->startedProject('85000.00');
        $this->attachClosureDoc($project, ProjectDocumentType::FINAL_REPORT);

        $missing = app(ProjectClosureService::class)->missingClosureDocuments($project->fresh());
        $this->assertTrue($missing->contains(ProjectDocumentType::COMPLETION_PHOTO));
        $this->assertFalse($missing->contains(ProjectDocumentType::FINAL_REPORT));
    }
}
