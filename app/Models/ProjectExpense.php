<?php

namespace App\Models;

use App\Enums\ProjectExpenseStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectExpense extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id', 'expense_date', 'reference_number', 'payee', 'description', 'amount', 'status',
        'revision_number', 'created_by', 'submitted_by', 'submitted_at', 'verified_by', 'verified_at',
        'rejected_by', 'rejected_at', 'rejection_reason', 'returned_by', 'returned_at', 'return_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectExpenseStatus::class,
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'revision_number' => 'integer',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
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
        return $this->hasMany(ProjectExpenseHistory::class)->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(ProjectExpenseRefund::class)->orderByDesc('id');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BudgetTransaction::class)
            ->where('type', \App\Enums\BudgetTransactionType::EXPENDITURE->value);
    }

    public function amountMoney(): Money
    {
        return Money::of((string) $this->amount);
    }

    /** Jumlah refund yang telah DISAHKAN bagi perbelanjaan ini (tepat). */
    public function verifiedRefundTotal(): Money
    {
        $total = Money::zero();
        foreach ($this->refunds()->where('status', \App\Enums\RefundStatus::VERIFIED->value)->get() as $refund) {
            $total = $total->plus($refund->amountMoney());
        }

        return $total;
    }

    /** Baki boleh dipulangkan = jumlah perbelanjaan − jumlah refund disahkan. */
    public function refundableRemaining(): Money
    {
        return $this->amountMoney()->minus($this->verifiedRefundTotal());
    }

    public function isEditableByMaker(): bool
    {
        return $this->status->isEditableByMaker();
    }
}
