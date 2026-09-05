<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Alp;
use App\Models\Application;
use App\Models\Project;
use App\Services\Project\ProjectCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ProjectCreationTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_approved_application_creates_exactly_one_project(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '1000000.00');

        $project = $this->approvedProject($alp, $year, '85000.00');

        $this->assertNotNull($project);
        $this->assertSame(1, Project::count());
        $this->assertSame(ProjectStatus::NOT_STARTED, $project->status);
    }

    public function test_project_snapshot_and_links_are_correct(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '1000000.00');

        $project = $this->approvedProject($alp, $year, '85000.00');

        $this->assertSame('85000.00', $project->approved_amount);
        $this->assertSame($alp->id, $project->alp_id);
        $this->assertSame($year->id, $project->financial_year_id);
        $this->assertNotNull($project->application_id);
        $this->assertStringStartsWith('PRJ/', $project->project_number);
    }

    public function test_non_approved_application_has_no_project(): void
    {
        $app = Application::factory()->submitted()->create();
        $this->assertNull($app->project);
    }

    public function test_duplicate_project_creation_is_idempotent(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '1000000.00');
        $project = $this->approvedProject($alp, $year, '85000.00');

        // Panggilan kedua mengembalikan projek yang sama (tiada duplikasi).
        $again = app(ProjectCreationService::class)->createFromApproved($project->application->fresh());

        $this->assertSame($project->id, $again->id);
        $this->assertSame(1, Project::where('application_id', $project->application_id)->count());
    }
}
