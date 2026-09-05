<?php

namespace Database\Seeders;

use App\Enums\ApplicationType;
use App\Enums\DocumentType;
use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

/**
 * Kewajipan dokumen URS v1.2 TBL-9 (wajib bagi semua jenis sumbangan).
 * Idempotent.
 */
class DocumentRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $ursRequired = [
            DocumentType::PENDAFTARAN_PERTUBUHAN,
            DocumentType::BORANG_EFT,
            DocumentType::PENYATA_BANK,
            DocumentType::KERTAS_KERJA,
            DocumentType::SIJIL_ROS,
        ];

        foreach ([ApplicationType::CSR->value, ApplicationType::DEVELOPMENT->value] as $type) {
            foreach ($ursRequired as $docType) {
                DocumentRequirement::updateOrCreate(
                    ['application_type' => $type, 'document_type' => $docType->value],
                    ['is_required' => true, 'active' => true],
                );
            }

            // Dokumen lama — kekal pilihan (tidak wajib) untuk data historikal.
            foreach ([DocumentType::PECAHAN_BAJET, DocumentType::SEBUT_HARGA, DocumentType::SURAT_SOKONGAN, DocumentType::PELAN_LOKASI, DocumentType::DOKUMEN_TEKNIKAL] as $optional) {
                DocumentRequirement::updateOrCreate(
                    ['application_type' => $type, 'document_type' => $optional->value],
                    ['is_required' => false, 'active' => true],
                );
            }
        }
    }
}
