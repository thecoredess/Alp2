<?php

namespace App\Models;

use App\Enums\BudgetRequestStatus;
use App\Enums\BudgetRequestType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetRequest extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'request_type', 'alp_id', 'financial_year_id', 'amount', 'status', 'reason',
        'reference_number', 'revision_number', 'created_by', 'submitted_by', 'submitted_at',
        'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason',
        'returned_by', 'returned_at', 'return_reason',
    ];

    protected function casts(): array
    {
        return [
            'request_type' => BudgetRequestType::class,
            'status' => BudgetRequestStatus::class,
            'amount' => 'decimal:2',
            'revision_number' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function alp(): BelongsTo
    {
        return $this->belongsTo(Alp::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(BudgetRequestHistory::class)->orderBy('id');
    }

    /** Transaksi ledger yang diposkan (jika diluluskan). */
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
