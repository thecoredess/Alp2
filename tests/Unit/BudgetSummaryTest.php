<?php

namespace Tests\Unit;

use App\Services\Budget\BudgetSummary;
use App\Support\Money;
use PHPUnit\Framework\TestCase;

class BudgetSummaryTest extends TestCase
{
    public function test_available_is_allocation_minus_committed_minus_spent(): void
    {
        $s = new BudgetSummary(Money::of('500000'), Money::of('120000'), Money::of('80000'));

        $this->assertSame('300000.00', $s->available()->value());
    }

    public function test_utilisation_percent(): void
    {
        $s = new BudgetSummary(Money::of('200000'), Money::of('40000'), Money::of('60000'));

        // (40000 + 60000) / 200000 = 50%
        $this->assertSame(50.0, $s->utilisationPercent());
    }

    public function test_utilisation_is_zero_when_no_allocation(): void
    {
        $this->assertSame(0.0, (new BudgetSummary())->utilisationPercent());
    }

    public function test_plus_combines_two_summaries(): void
    {
        $a = new BudgetSummary(Money::of('500000'), Money::of('100000'), Money::of('50000'));
        $b = new BudgetSummary(Money::of('300000'), Money::of('20000'), Money::of('10000'));

        $total = $a->plus($b);

        $this->assertSame('800000.00', $total->allocation->value());
        $this->assertSame('120000.00', $total->committed->value());
        $this->assertSame('60000.00', $total->spent->value());
        $this->assertSame('620000.00', $total->available()->value());
    }
}
