<?php

namespace App\Models;

use App\Enums\AlpStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alp extends Model
{
    /** @use HasFactory<\Database\Factories\AlpFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'ref_code',
        'portfolio_zone',
        'appointment_start',
        'appointment_end',
        'status',
        'phone',
        'email',
        'address',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_start' => 'date',
            'appointment_end' => 'date',
            'status' => AlpStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Pengguna (akaun) yang dikaitkan dengan ALP ini. */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Akaun peruntukan (satu per tahun kewangan). */
    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    /** Transaksi ledger bajet. */
    public function budgetTransactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function isActive(): bool
    {
        return $this->status === AlpStatus::ACTIVE;
    }
}
