<?php

namespace Database\Seeders;

use App\Enums\ApplicationType;
use App\Models\ApplicationWorkflowSetting;
use Illuminate\Database\Seeder;

/**
 * Tetapan aliran kerja lalai (boleh dikonfig). CSR tidak perlu semakan teknikal;
 * DEVELOPMENT perlu. Ini nilai lalai pembangunan — boleh diubah kemudian.
 * Idempotent (production-safe).
 */
class WorkflowSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ApplicationType::CSR->value => false,
            ApplicationType::DEVELOPMENT->value => true,
        ];

        foreach ($defaults as $type => $requiresTechnical) {
            ApplicationWorkflowSetting::updateOrCreate(
                ['application_type' => $type],
                ['requires_technical_review' => $requiresTechnical, 'active' => true],
            );
        }
    }
}
