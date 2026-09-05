<?php

namespace App\Models;

use App\Enums\BudgetRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRequestHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'budget_request_id', 'from_status', 'to_status', 'changed_by', 'remarks', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => BudgetRequestStatus::class,
            'to_status' => BudgetRequestStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
