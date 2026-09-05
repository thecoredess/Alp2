<?php

namespace App\Models;

use App\Enums\ProjectExpenseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpenseHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['project_expense_id', 'from_status', 'to_status', 'changed_by', 'remarks', 'created_at'];

    protected function casts(): array
    {
        return [
            'from_status' => ProjectExpenseStatus::class,
            'to_status' => ProjectExpenseStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
