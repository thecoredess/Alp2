<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    // ── Ketepatan aritmetik (kes klasik float) ──────────────────

    public function test_point_one_plus_point_two_equals_point_three(): void
    {
        $this->assertSame('0.30', Money::of('0.10')->plus(Money::of('0.20'))->value());
    }

    public function test_large_value_plus_small_is_exact(): void
    {
        $this->assertSame('1000000.30', Money::of('1000000.10')->plus(Money::of('0.20'))->value());
    }

    public function test_subtraction_is_exact(): void
    {
        $this->assertSame('376543.22', Money::of('500000.00')->minus(Money::of('123456.78'))->value());
    }

    public function test_no_drift_over_many_small_additions(): void
    {
        $total = Money::zero();
        for ($i = 0; $i < 1000; $i++) {
            $total = $total->plus(Money::of('0.01'));
        }
        // 1000 × 0.01 = 10.00 tepat (float akan menyimpang).
        $this->assertSame('10.00', $total->value());
    }

    public function test_alternating_add_subtract_returns_to_exact_zero(): void
    {
        $m = Money::of('0.00');
        for ($i = 0; $i < 100; $i++) {
            $m = $m->plus(Money::of('0.10'))->minus(Money::of('0.10'));
        }
        $this->assertSame('0.00', $m->value());
        $this->assertTrue($m->isZero());
    }

    // ── Normalisasi input ───────────────────────────────────────

    public function test_normalises_various_inputs_to_two_decimals(): void
    {
        $this->assertSame('100.00', Money::of('100')->value());
        $this->assertSame('100.00', Money::of('100.0')->value());
        $this->assertSame('100.00', Money::of('100.00')->value());
        $this->assertSame('100.00', Money::of(100)->value());
        $this->assertSame('100.10', Money::of('100.1')->value());
        $this->assertSame('0.00', Money::of('-0')->value());
    }

    // ── Penolakan input tidak sah ───────────────────────────────

    public static function invalidValues(): array
    {
        return [
            ['NaN'],
            ['Infinity'],
            ['1e5'],          // notasi saintifik
            ['1.234'],        // lebih 2 desimal
            ['abc'],
            [''],
            ['  '],
            ['12,345.00'],    // pemisah ribuan bukan input sah
            ['RM100'],
        ];
    }

    #[DataProvider('invalidValues')]
    public function test_rejects_invalid_money(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::of($value);
    }

    public function test_rejects_value_exceeding_decimal_15_2_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::of('99999999999999.99'); // 14 digit integer > had 13
    }

    // ── Perbandingan & tanda ────────────────────────────────────

    public function test_comparisons(): void
    {
        $this->assertTrue(Money::of('100.00')->greaterThan(Money::of('99.99')));
        $this->assertTrue(Money::of('99.99')->lessThan(Money::of('100.00')));
        $this->assertTrue(Money::of('100.00')->equals(Money::of('100.0')));
        $this->assertTrue(Money::of('-0.01')->isNegative());
        $this->assertTrue(Money::of('0.01')->isPositive());
        $this->assertTrue(Money::zero()->isZero());
    }

    public function test_negate_and_abs(): void
    {
        $this->assertSame('-50.00', Money::of('50.00')->negate()->value());
        $this->assertSame('50.00', Money::of('-50.00')->abs()->value());
    }

    // ── Peratus (nisbah paparan) ────────────────────────────────

    public function test_percentage_of_uses_exact_operands(): void
    {
        // (120000 + 80000) / 500000 = 40%
        $used = Money::of('120000')->plus(Money::of('80000'));
        $this->assertSame(40.0, $used->percentageOf(Money::of('500000')));
    }

    public function test_percentage_of_zero_base_is_zero(): void
    {
        $this->assertSame(0.0, Money::of('100')->percentageOf(Money::zero()));
    }

    // ── Format paparan ──────────────────────────────────────────

    public function test_format_adds_thousands_separator(): void
    {
        $this->assertSame('550,000.00', Money::of('550000.00')->format());
        $this->assertSame('1,000,000.30', Money::of('1000000.30')->format());
        $this->assertSame('-25,000.00', Money::of('-25000')->format());
    }
}
