<?php

namespace Database\Factories;

use App\Models\Alp;
use App\Models\Allocation;
use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allocation>
 */
class AllocationFactory extends Factory
{
    protected $model = Allocation::class;

    public function definition(): array
    {
        return [
            'alp_id' => Alp::factory(),
            'financial_year_id' => FinancialYear::factory()->active(),
            'reference_no' => 'DBKL/BGT/'.fake()->year().'/'.fake()->unique()->numerify('####'),
            'remarks' => null,
        ];
    }
}
