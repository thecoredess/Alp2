<?php

namespace Tests;

use App\Models\SystemSetting;
use App\Support\UrsContributionPolicy;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ujian aliran lama guna jumlah besar; ujian Urs* aktifkan polisi secara eksplisit.
        // Production seeder kekal ON (URS v1.2).
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, false);
    }
}
