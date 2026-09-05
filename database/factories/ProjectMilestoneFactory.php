<?php

namespace Database\Factories;

use App\Enums\MilestoneStatus;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMilestone>
 */
class ProjectMilestoneFactory extends Factory
{
    protected $model = ProjectMilestone::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->sentence(3),
            'status' => MilestoneStatus::PENDING,
            'sort_order' => 1,
        ];
    }
}
