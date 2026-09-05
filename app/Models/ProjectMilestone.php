<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectMilestoneFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id', 'name', 'description', 'target_date', 'completed_at',
        'status', 'sort_order', 'remarks', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => MilestoneStatus::class,
            'target_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
