<?php

use App\Enums\DocumentType;
use App\Models\DocumentRequirement;
use Illuminate\Database\Migrations\Migration;

/**
 * Kertas Kerja diisi dalam borang (medan g), bukan lampiran muat naik.
 */
return new class extends Migration
{
    public function up(): void
    {
        $contribution = array_map(
            fn (DocumentType $t) => $t->value,
            DocumentType::contributionAttachments(),
        );

        DocumentRequirement::query()
            ->whereNotIn('document_type', $contribution)
            ->update(['is_required' => false, 'active' => false]);

        foreach (DocumentType::contributionAttachments() as $type) {
            DocumentRequirement::query()->updateOrCreate(
                ['document_type' => $type->value],
                ['is_required' => true, 'active' => true],
            );
        }
    }

    public function down(): void
    {
        // Tiada rollback — keperluan lama tidak dipulihkan.
    }
};
