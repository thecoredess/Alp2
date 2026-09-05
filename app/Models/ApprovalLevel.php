<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLevel extends Model
{
    /** @use HasFactory<\Database\Factories\ApprovalLevelFactory> */
    use HasFactory;

    protected $fillable = [
        'financial_year_id',
        'name',
        'min_amount',
        'max_amount',
        'required_role',
        'sequence',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'sequence' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function minMoney(): Money
    {
        return Money::of((string) $this->min_amount);
    }

    public function maxMoney(): ?Money
    {
        return $this->max_amount === null ? null : Money::of((string) $this->max_amount);
    }

    /** Adakah jumlah berada dalam julat aras ini? */
    public function contains(Money $amount): bool
    {
        if ($amount->lessThan($this->minMoney())) {
            return false;
        }

        $max = $this->maxMoney();

        return $max === null || ! $amount->greaterThan($max);
    }
}
