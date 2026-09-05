<?php

namespace App\Models;

use App\Enums\ApplicationType;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    protected $fillable = [
        'application_type',
        'document_type',
        'is_required',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'application_type' => ApplicationType::class,
            'document_type' => DocumentType::class,
            'is_required' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Senarai dokumen WAJIB (aktif) bagi satu jenis permohonan.
     *
     * @return \Illuminate\Support\Collection<int, DocumentType>
     */
    public static function requiredFor(ApplicationType $type): \Illuminate\Support\Collection
    {
        return static::query()
            ->where('application_type', $type->value)
            ->where('is_required', true)
            ->where('active', true)
            ->get()
            ->map(fn (self $r) => $r->document_type);
    }

    /**
     * Semua peraturan aktif bagi satu jenis (wajib & pilihan) untuk checklist.
     *
     * @return \Illuminate\Support\Collection<int, DocumentRequirement>
     */
    public static function activeFor(ApplicationType $type): \Illuminate\Support\Collection
    {
        return static::query()
            ->where('application_type', $type->value)
            ->where('active', true)
            ->get();
    }
}
