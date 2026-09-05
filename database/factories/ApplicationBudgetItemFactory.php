<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationBudgetItem;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationBudgetItem>
 */
class ApplicationBudgetItemFactory extends Factory
{
    protected $model = ApplicationBudgetItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitCost = (string) fake()->numberBetween(100, 10000).'.00';
        $total = Money::of($unitCost)->times($quantity)->value();

        return [
            'application_id' => Application::factory(),
            'description' => fake()->words(3, true),
            'quantity' => $quantity,
            'unit' => 'unit',
            'unit_cost' => $unitCost,
            'total' => $total,
            'sort_order' => 1,
        ];
    }
}
