<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['project_id', 'from_status', 'to_status', 'changed_by', 'remarks', 'created_at'];

    protected function casts(): array
    {
        return [
            'from_status' => ProjectStatus::class,
            'to_status' => ProjectStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
