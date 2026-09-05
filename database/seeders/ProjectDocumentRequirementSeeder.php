<?php

namespace Database\Seeders;

use App\Enums\ApplicationType;
use App\Enums\ProjectDocumentType;
use App\Models\ProjectDocumentRequirement;
use Illuminate\Database\Seeder;

/**
 * Kewajipan dokumen PENUTUPAN projek mengikut jenis projek.
 * BOLEH DIKONFIG — bukan polisi tetap DBKL. Idempotent (production-safe).
 */
class ProjectDocumentRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ApplicationType::CSR->value => [
                [ProjectDocumentType::FINAL_REPORT, true],
                [ProjectDocumentType::COMPLETION_PHOTO, true],
                [ProjectDocumentType::PROGRAM_ATTENDANCE, false],
            ],
            ApplicationType::DEVELOPMENT->value => [
                [ProjectDocumentType::FINAL_REPORT, true],
                [ProjectDocumentType::COMPLETION_CERTIFICATE, true],
                [ProjectDocumentType::COMPLETION_PHOTO, true],
                [ProjectDocumentType::TECHNICAL_COMPLETION, false],
            ],
        ];

        foreach ($defaults as $type => $rules) {
            foreach ($rules as [$docType, $required]) {
                ProjectDocumentRequirement::updateOrCreate(
                    ['project_type' => $type, 'category' => 'closure_evidence', 'document_type' => $docType->value],
                    ['is_required' => $required, 'active' => true],
                );
            }
        }
    }
}
