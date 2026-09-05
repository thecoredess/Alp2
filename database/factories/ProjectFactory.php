<?php

namespace Database\Factories;

use App\Enums\ApplicationType;
use App\Enums\ProjectStatus;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'project_number' => 'PRJ/CSR/2026/'.fake()->unique()->numerify('####'),
            'application_id' => Application::factory()->submitted(),
            'alp_id' => Alp::factory(),
            'financial_year_id' => FinancialYear::factory()->active(),
            'project_name' => fake()->sentence(4),
            'project_type' => ApplicationType::CSR,
            'approved_amount' => '85000.00',
            'status' => ProjectStatus::NOT_STARTED,
            'progress_percent' => 0,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => ProjectStatus::IN_PROGRESS, 'actual_start_date' => now()]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => ProjectStatus::COMPLETED, 'progress_percent' => 100, 'actual_start_date' => now()->subMonth(), 'actual_completion_date' => now()]);
    }
}
