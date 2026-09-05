<?php

namespace Database\Factories;

use App\Enums\BudgetTransactionType;
use App\Models\Allocation;
use App\Models\BudgetTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetTransaction>
 */
class BudgetTransactionFactory extends Factory
{
    protected $model = BudgetTransaction::class;

    public function definition(): array
    {
        $allocation = Allocation::factory();

        return [
            'allocation_id' => $allocation,
            'alp_id' => fn (array $attrs) => Allocation::find($attrs['allocation_id'])?->alp_id ?? Allocation::factory(),
            'financial_year_id' => fn (array $attrs) => Allocation::find($attrs['allocation_id'])?->financial_year_id ?? 1,
            'type' => BudgetTransactionType::INITIAL_ALLOCATION,
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'reference_no' => null,
            'description' => null,
            'created_at' => now(),
        ];
    }

    public function type(BudgetTransactionType $type, float $amount): static
    {
        return $this->state(fn () => ['type' => $type, 'amount' => $amount]);
    }
}
