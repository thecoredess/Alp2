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
            'application_number' => 'ALP/SUM/2026/'.fake()->unique()->numerify('####'),
            'financial_year_id' => FinancialYear::factory()->active(),
            'alp_id' => Alp::factory(),
            'application_type' => ApplicationType::SUMBANGAN,
            'purpose' => fake()->sentence(6),
            'recipient_name' => fake()->company(),
            'recipient_ros_number' => 'ROS-'.fake()->unique()->numerify('########'),
            'program_date' => now()->addMonths(3)->toDateString(),
            'program_category' => ProgramCategory::KOMUNITI,
            'recipient_bank_account' => fake()->numerify('##########'),
            'recipient_address' => 'No. 1, Jalan Raja Laut, 50350 Kuala Lumpur',
            'requested_amount' => '1000.00',
            'status' => ApplicationStatus::DRAFT,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => ApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);
    }
}
