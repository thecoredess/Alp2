<?php

namespace App\Models;

use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationReview extends Model
{
    public $timestamps = false; // created_at sahaja (append-only)

    protected $fillable = [
        'application_id',
        'review_type',
        'reviewer_id',
        'decision',
        'comments',
        'checklist',
        'revision_number',
        'reviewed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'review_type' => ReviewType::class,
            'decision' => ReviewDecision::class,
            'checklist' => 'array',
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
