<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationBudgetItem extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationBudgetItemFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'description',
        'quantity',
        'unit',
        'unit_cost',
        'total',
        'remarks',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Kira jumlah tepat = kuantiti × harga seunit (Money, tanpa float).
     */
    public static function computeTotal(int $quantity, string $unitCost): Money
    {
        return Money::of($unitCost)->times($quantity);
    }
}
