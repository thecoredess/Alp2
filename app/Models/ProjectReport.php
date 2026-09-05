<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReport extends Model
{
    protected $fillable = [
        'project_id', 'summary', 'outcome', 'beneficiary_count', 'impact_summary', 'completion_summary',
        'issues', 'lessons_learned', 'final_remarks', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'beneficiary_count' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
