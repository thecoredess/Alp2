<?php

namespace Database\Factories;

use App\Enums\BudgetRequestStatus;
use App\Enums\BudgetRequestType;
use App\Models\Alp;
use App\Models\BudgetRequest;
use App\Models\FinancialYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetRequest>
 */
class BudgetRequestFactory extends Factory
{
    protected $model = BudgetRequest::class;

    public function definition(): array
    {
        return [
            'request_type' => BudgetRequestType::INITIAL_ALLOCATION,
            'alp_id' => Alp::factory(),
            'financial_year_id' => FinancialYear::factory()->active(),
            'amount' => '500000.00',
            'status' => BudgetRequestStatus::DRAFT,
            'reason' => 'Ujian',
            'created_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => BudgetRequestStatus::PENDING_APPROVAL,
            'submitted_at' => now(),
        ]);
    }

    public function increase(string $amount): static
    {
        return $this->state(fn () => ['request_type' => BudgetRequestType::ALLOCATION_INCREASE, 'amount' => $amount]);
    }

    public function decrease(string $amount): static
    {
        return $this->state(fn () => ['request_type' => BudgetRequestType::ALLOCATION_DECREASE, 'amount' => $amount]);
    }
}
