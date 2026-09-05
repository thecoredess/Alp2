<?php

namespace Tests\Unit;

use App\Models\Alp;
use App\Models\SystemSetting;
use App\Support\Money;
use App\Support\UrsContributionPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrsProgramRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_br007_june_appointment_prorates_annual_ceiling(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, true);
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_ANNUAL, '30000.00');

        $alp = Alp::factory()->create(['appointment_start' => '2026-06-01']);
        $max = UrsContributionPolicy::maxAnnualForAlp($alp, 2026);

        // 7/12 × 30000 = 17500
        $this->assertSame('17500.00', $max->value());
    }

    public function test_br007_rejects_allocation_above_entitlement(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, true);
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_ANNUAL, '30000.00');

        $alp = Alp::factory()->create(['appointment_start' => '2026-06-01']);
        $errors = UrsContributionPolicy::validateAnnualAllocationForAlp(Money::of('20000.00'), $alp, 2026);

        $this->assertNotEmpty($errors);
    }

    public function test_br010_detects_kuala_lumpur_address(): void
    {
        $this->assertTrue(UrsContributionPolicy::isKualaLumpurAddress('Jalan Ampang, Kuala Lumpur'));
        $this->assertTrue(UrsContributionPolicy::isKualaLumpurAddress(null, 'KL'));
        $this->assertFalse(UrsContributionPolicy::isKualaLumpurAddress('Johor Bahru'));
    }

    public function test_br014_short_notice_when_less_than_two_months(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-15'));
        $this->assertTrue(UrsContributionPolicy::isShortNotice(Carbon::parse('2026-04-01')));
        $this->assertFalse(UrsContributionPolicy::isShortNotice(Carbon::parse('2026-06-20')));
        Carbon::setTestNow();
    }

    public function test_overdue_default_is_fourteen_days(): void
    {
        $this->assertSame(14, UrsContributionPolicy::overdueDays());
    }
}
