<?php

namespace App\Models;

use App\Enums\ProjectDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocument extends Model
{
    protected $fillable = [
        'project_id', 'project_expense_id', 'project_expense_refund_id', 'category', 'document_type',
        'original_filename', 'stored_path', 'mime_type', 'file_size', 'sha256', 'uploaded_by',
    ];

    protected $hidden = ['stored_path'];

    protected function casts(): array
    {
        return [
            'document_type' => ProjectDocumentType::class,
            'file_size' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(ProjectExpense::class, 'project_expense_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
