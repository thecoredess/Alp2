<?php

namespace App\Models;

use App\Enums\RefundStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectExpenseRefund extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectExpenseRefundFactory> */
    use HasFactory;

    protected $fillable = [
        'project_expense_id', 'project_id', 'amount', 'reason', 'reference_number', 'refund_date',
        'status', 'revision_number', 'created_by', 'submitted_by', 'submitted_at',
        'verified_by', 'verified_at', 'rejected_by', 'rejected_at', 'rejection_reason',
        'returned_by', 'returned_at', 'return_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class,
            'amount' => 'decimal:2',
            'refund_date' => 'date',
            'revision_number' => 'integer',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(ProjectExpense::class, 'project_expense_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProjectExpenseRefundHistory::class)->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BudgetTransaction::class);
    }

    public function amountMoney(): Money
    {
        return Money::of((string) $this->amount);
    }

    public function isEditableByMaker(): bool
    {
        return $this->status->isEditableByMaker();
    }
}
