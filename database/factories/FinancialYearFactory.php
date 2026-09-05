<?php

namespace Database\Factories;

use App\Enums\FinancialYearStatus;
use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialYear>
 */
class FinancialYearFactory extends Factory
{
    protected $model = FinancialYear::class;

    public function definition(): array
    {
        return [
            'year' => fake()->unique()->numberBetween(2000, 3000),
            'label' => null,
            'status' => FinancialYearStatus::DRAFT,
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => FinancialYearStatus::ACTIVE,
            'is_active' => true,
            'opened_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => FinancialYearStatus::CLOSED,
            'is_active' => false,
            'closed_at' => now(),
        ]);
    }
}
