<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Project;
use App\Models\User;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ProjectLifecycleTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function notStartedProject(string $amount = '85000.00'): Project
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '1000000.00');

        return $this->approvedProject($alp, $year, $amount);
    }

    public function test_operator_can_start_project(): void
    {
        $project = $this->notStartedProject();
        app(ProjectService::class)->start($project, $this->projectOperator());

        $this->assertSame(ProjectStatus::IN_PROGRESS, $project->fresh()->status);
        $this->assertNotNull($project->fresh()->actual_start_date);
    }

    public function test_alp_cannot_start_project_via_http(): void
    {
        $project = $this->notStartedProject();
        $alp = User::factory()->create(['alp_id' => $project->alp_id])->assignRole(RoleName::ALP->value);

        $this->actingAs($alp)->post(route('projects.start', $project))->assertForbidden();
    }

    public function test_progress_must_be_between_0_and_100(): void
    {
        $project = $this->startedProject();

        $this->expectException(ProjectException::class);
        app(ProjectService::class)->updateProgress($project, $this->projectOperator(), 101, null);
    }

    public function test_milestone_can_be_added_and_completed(): void
    {
        $project = $this->startedProject();
        $op = $this->projectOperator();

        $m = app(ProjectService::class)->addMilestone($project, $op, ['name' => 'Fasa 1']);
        $this->assertDatabaseHas('project_milestones', ['id' => $m->id, 'status' => 'pending']);

        app(ProjectService::class)->updateMilestone($m, $op, ['name' => 'Fasa 1', 'status' => 'completed']);
        $this->assertNotNull($m->fresh()->completed_at);
    }

    public function test_complete_requires_full_progress_and_milestones(): void
    {
        $project = $this->startedProject();
        $op = $this->projectOperator();

        // Progress masih 0 → tidak boleh selesai.
        $this->expectException(ProjectException::class);
        app(ProjectService::class)->complete($project, $op);
    }

    public function test_alp_can_view_own_project_but_not_others(): void
    {
        $project = $this->startedProject();
        $owner = User::factory()->create(['alp_id' => $project->alp_id])->assignRole(RoleName::ALP->value);
        $this->actingAs($owner)->get(route('projects.show', $project))->assertOk();

        $otherAlp = Alp::factory()->create();
        $other = User::factory()->create(['alp_id' => $otherAlp->id])->assignRole(RoleName::ALP->value);
        $this->actingAs($other)->get(route('projects.show', $project))->assertForbidden();
    }

    public function test_closed_project_cannot_add_milestone(): void
    {
        $project = $this->startedProject('85000.00');
        $this->verifyExpense($project, '85000.00');
        $op = $this->projectOperator();
        app(ProjectService::class)->updateProgress($project->fresh(), $op, 100, null);
        app(ProjectService::class)->complete($project->fresh(), $op);
        app(\App\Services\Project\ProjectReportService::class)->submit($project->fresh(), $op, ['summary' => 'x']);
        app(\App\Services\Project\ProjectClosureService::class)->close($project->fresh(), $this->financeChecker());

        $this->expectException(ProjectException::class);
        app(ProjectService::class)->addMilestone($project->fresh(), $op, ['name' => 'X']);
    }
}
