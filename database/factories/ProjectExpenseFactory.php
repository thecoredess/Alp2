<?php

namespace Database\Factories;

use App\Enums\ProjectExpenseStatus;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectExpense>
 */
class ProjectExpenseFactory extends Factory
{
    protected $model = ProjectExpense::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'expense_date' => now(),
            'reference_number' => 'INV/'.fake()->unique()->numerify('#####'),
            'payee' => fake()->company(),
            'description' => fake()->words(3, true),
            'amount' => '20000.00',
            'status' => ProjectExpenseStatus::DRAFT,
            'created_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => ProjectExpenseStatus::PENDING_VERIFICATION,
            'submitted_by' => $attrs['created_by'] ?? User::factory(),
            'submitted_at' => now(),
        ]);
    }
}
