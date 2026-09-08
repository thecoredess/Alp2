<?php

namespace App\Models;

use App\Enums\ReviewDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardReview extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'reviewer_id',
        'stage',
        'decision',
        'comments',
        'reviewed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'decision' => ReviewDecision::class,
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
