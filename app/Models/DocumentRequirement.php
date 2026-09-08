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

    /**
     * @return \Illuminate\Support\Collection<int, DocumentType>
     */
    public static function requiredFor(): \Illuminate\Support\Collection
    {
        return static::query()
            ->where('is_required', true)
            ->where('active', true)
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
            ->orderBy('id')
            ->get();
    }
}
