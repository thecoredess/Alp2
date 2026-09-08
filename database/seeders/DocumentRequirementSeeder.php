<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

/** Lampiran wajib Senarai Semak Penyaluran Sumbangan ALP. */
class DocumentRequirementSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DocumentType::contributionAttachments() as $docType) {
            DocumentRequirement::updateOrCreate(
                ['document_type' => $docType->value],
                ['is_required' => true, 'active' => true],
            );
        }

        DocumentRequirement::query()
            ->whereNotIn('document_type', array_map(fn (DocumentType $t) => $t->value, DocumentType::contributionAttachments()))
            ->update(['is_required' => false, 'active' => false]);
    }
}
