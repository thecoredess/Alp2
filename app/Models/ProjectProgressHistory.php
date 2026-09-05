<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProgressHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['project_id', 'progress_percent', 'status', 'remarks', 'updated_by', 'created_at'];

    protected function casts(): array
    {
        return [
            'progress_percent' => 'integer',
            'status' => ProjectStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
