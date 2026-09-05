<?php

namespace App\Models;

use App\Enums\ApplicationType;
use App\Enums\ProjectDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ProjectDocumentRequirement extends Model
{
    protected $fillable = ['project_type', 'category', 'document_type', 'is_required', 'active'];

    protected function casts(): array
    {
        return [
            'project_type' => ApplicationType::class,
            'document_type' => ProjectDocumentType::class,
            'is_required' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Jenis dokumen WAJIB (aktif) bagi jenis projek & kategori.
     *
     * @return Collection<int, ProjectDocumentType>
     */
    public static function requiredFor(ApplicationType $type, string $category): Collection
    {
        return static::query()
            ->where('project_type', $type->value)
            ->where('category', $category)
            ->where('is_required', true)
            ->where('active', true)
            ->get()
            ->map(fn (self $r) => $r->document_type);
    }
}
