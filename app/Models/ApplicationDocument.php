<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'document_type',
        'original_filename',
        'stored_path',
        'mime_type',
        'file_size',
        'sha256',
        'uploaded_by',
    ];

    protected $hidden = ['stored_path']; // jangan dedah laluan storan

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'file_size' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
