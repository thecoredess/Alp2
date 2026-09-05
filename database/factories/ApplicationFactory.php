<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ProgramCategory;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'application_number' => 'ALP/CSR/2026/'.fake()->unique()->numerify('####'),
            'financial_year_id' => FinancialYear::factory()->active(),
            'alp_id' => Alp::factory(),
            'application_type' => ApplicationType::CSR,
            'project_title' => fake()->sentence(4),
            'project_summary' => fake()->paragraph(),
            'recipient_name' => fake()->company(),
            'recipient_ros_number' => 'ROS-'.fake()->unique()->numerify('########'),
            'recipient_bank_account' => fake()->numerify('##########'),
            'recipient_address' => 'Jalan Tun Razak, Kuala Lumpur',
            'program_category' => ProgramCategory::KOMUNITI,
            'location' => 'Kuala Lumpur',
            'proposed_start_date' => now()->addMonths(3),
            'compliance_declared_at' => now(),
            'objectives' => fake()->sentence(),
            'scope' => fake()->sentence(),
            'requested_amount' => '0.00',
            'status' => ApplicationStatus::DRAFT,
        ];
    }

    public function development(): static
    {
        return $this->state(fn () => ['application_type' => ApplicationType::DEVELOPMENT]);
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => ApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);
    }
}
