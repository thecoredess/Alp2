<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Header akaun peruntukan (ALP × Tahun Kewangan).
 * Tiada lajur jumlah — nilai kewangan dikira dari ledger melalui BudgetService.
 */
class Allocation extends Model
{
    /** @use HasFactory<\Database\Factories\AllocationFactory> */
    use HasFactory;

    protected $fillable = [
        'alp_id',
        'financial_year_id',
        'reference_no',
        'remarks',
        'created_by',
    ];

    public function alp(): BelongsTo
    {
        return $this->belongsTo(Alp::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
