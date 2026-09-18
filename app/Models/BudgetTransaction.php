<?php

namespace App\Models;

use App\Enums\BudgetTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Satu baris ledger bajet — IMMUTABLE.
 * Kemas kini & padam disekat di peringkat model. Untuk membatalkan kesan
 * sesuatu transaksi, cipta transaksi pembalikan (reversal) yang baharu.
 */
class BudgetTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetTransactionFactory> */
    use HasFactory;

    public const UPDATED_AT = null; // tiada updated_at

    protected $fillable = [
        'allocation_id',
        'alp_id',
        'financial_year_id',
        'application_id',
        'budget_request_id',
        'project_id',
        'project_expense_id',
        'project_expense_refund_id',
        'type',
        'amount',
        'reference_no',
        'description',
        'meta',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => BudgetTransactionType::class,
            'amount' => 'decimal:2',
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Kuatkuasakan sifat immutable.
        static::updating(function () {
            throw new RuntimeException('Transaksi lejar bajet tidak boleh diubah. Gunakan transaksi pembalikan.');
        });

        static::deleting(function () {
            throw new RuntimeException('Transaksi lejar bajet tidak boleh dipadam.');
        });
    }

    // ── Perhubungan ─────────────────────────────────────────────

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    public function alp(): BelongsTo
    {
        return $this->belongsTo(Alp::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
