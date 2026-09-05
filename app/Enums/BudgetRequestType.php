<?php

namespace App\Enums;

use App\Support\Money;

/**
 * Jenis cadangan bajet (maker-checker) sebelum diposkan ke ledger.
 */
enum BudgetRequestType: string
{
    case INITIAL_ALLOCATION = 'initial_allocation';
    case ALLOCATION_INCREASE = 'allocation_increase';
    case ALLOCATION_DECREASE = 'allocation_decrease';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL_ALLOCATION => 'Peruntukan Awal',
            self::ALLOCATION_INCREASE => 'Pelarasan — Tambah',
            self::ALLOCATION_DECREASE => 'Pelarasan — Kurang',
        };
    }

    public function isInitial(): bool
    {
        return $this === self::INITIAL_ALLOCATION;
    }

    public function isAdjustment(): bool
    {
        return $this === self::ALLOCATION_INCREASE || $this === self::ALLOCATION_DECREASE;
    }

    public function isDecrease(): bool
    {
        return $this === self::ALLOCATION_DECREASE;
    }

    /** Kumpulan permission: 'allocations' untuk peruntukan awal, 'adjustments' untuk pelarasan. */
    public function permissionGroup(): string
    {
        return $this->isInitial() ? 'allocations' : 'adjustments';
    }

    public function permission(string $action): string
    {
        return $this->permissionGroup().'.'.$action;
    }

    /**
     * Delta bertanda terhadap peruntukan untuk poskan ke ledger,
     * daripada magnitud positif $amount.
     */
    public function ledgerDelta(Money $amount): Money
    {
        return $this === self::ALLOCATION_DECREASE ? $amount->negate() : $amount;
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::INITIAL_ALLOCATION => 'bg-navy-100 text-navy-800',
            self::ALLOCATION_INCREASE => 'bg-green-100 text-green-800',
            self::ALLOCATION_DECREASE => 'bg-orange-100 text-orange-800',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}
