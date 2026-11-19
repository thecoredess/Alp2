<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    protected $fillable = [
        'document_type',
        'is_required',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'is_required' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /** @return list<string> */
    public static function uploadTypeValues(): array
    {
        return array_map(
            fn (DocumentType $t) => $t->value,
            DocumentType::contributionUploadTypes(),
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, DocumentType>
     */
    public static function requiredFor(): \Illuminate\Support\Collection
    {
        return static::query()
            ->where('is_required', true)
            ->where('active', true)
            ->whereIn('document_type', self::uploadTypeValues())
            ->get()
            ->map(fn (self $r) => $r->document_type);
    }

    /**
     * @return \Illuminate\Support\Collection<int, DocumentRequirement>
     */
    public static function activeFor(): \Illuminate\Support\Collection
    {
        return static::query()
            ->where('active', true)
            ->whereIn('document_type', self::uploadTypeValues())
            ->orderBy('id')
            ->get();
    }
}
