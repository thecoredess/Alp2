<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpenseRefundHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['project_expense_refund_id', 'from_status', 'to_status', 'changed_by', 'remarks', 'created_at'];

    protected function casts(): array
    {
        return [
            'from_status' => RefundStatus::class,
            'to_status' => RefundStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
