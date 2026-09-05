<?php

namespace App\Models;

use App\Enums\FinancialYearStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialYear extends Model
{
    /** @use HasFactory<\Database\Factories\FinancialYearFactory> */
    use HasFactory;

    protected $fillable = [
        'year',
        'label',
        'status',
        'is_active',
        'opened_at',
        'closed_at',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'status' => FinancialYearStatus::class,
            'is_active' => 'boolean',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Bantuan ─────────────────────────────────────────────────

    public function isClosed(): bool
    {
        return $this->status === FinancialYearStatus::CLOSED;
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    /** Dapatkan tahun kewangan aktif semasa (atau null). */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
