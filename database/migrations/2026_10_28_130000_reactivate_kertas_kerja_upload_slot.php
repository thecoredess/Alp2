<?php

use App\Enums\DocumentType;
use App\Models\DocumentRequirement;
use Illuminate\Database\Migrations\Migration;

/** Benarkan slot muat naik Kertas Kerja (medan g tetap dalam borang). */
return new class extends Migration
{
    public function up(): void
    {
        DocumentRequirement::query()->updateOrCreate(
            ['document_type' => DocumentType::KERTAS_KERJA->value],
            ['is_required' => false, 'active' => true],
        );
    }

    public function down(): void
    {
        DocumentRequirement::query()
            ->where('document_type', DocumentType::KERTAS_KERJA->value)
            ->update(['active' => false, 'is_required' => false]);
    }
};
