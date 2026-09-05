<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationApproval extends Model
{
    public $timestamps = false; // created_at sahaja (append-only)

    protected $fillable = [
        'application_id',
        'approval_level_id',
        'approver_id',
        'decision',
        'comments',
        'sequence',
        'revision_number',
        'decided_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'decision' => ApprovalDecision::class,
            'decided_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function approvalLevel(): BelongsTo
    {
        return $this->belongsTo(ApprovalLevel::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
