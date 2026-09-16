<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Support\NotificationTemplates;
use App\Support\UrsContributionPolicy;
use Illuminate\Database\Seeder;

/** Nilai lalai polisi URS v1.2 — wajib ON untuk operasi rasmi. */
class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, true);
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_ANNUAL, '30000.00');
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_PER_APPLICATION, '3000.00');
        SystemSetting::set(UrsContributionPolicy::KEY_PERIOD_QUOTA, '10000.00');
        SystemSetting::set(UrsContributionPolicy::KEY_OVERDUE_DAYS, '14');

        NotificationTemplates::seedDefaults();
    }
}
