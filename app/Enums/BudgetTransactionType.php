<?php

namespace App\Enums;

use App\Support\Money;

/**
 * Jenis transaksi dalam ledger bajet (immutable).
 *
 * Setiap jenis mempunyai kesan tetap terhadap tiga baldi:
 *   - allocation (peruntukan)
 *   - committed  (komitmen)
 *   - spent      (perbelanjaan sebenar)
 *
 * Formula baki:
 *   Available = Allocation − Committed − Spent
 *
 * EXPENDITURE mengurangkan committed DAN menambah spent supaya tiada
 * pengiraan berganda (double-counting) antara committed dan spent.
 */
enum BudgetTransactionType: string
{
    case INITIAL_ALLOCATION = 'initial_allocation';
    case ALLOCATION_ADJUSTMENT = 'allocation_adjustment';
    case COMMITMENT = 'commitment';
    case COMMITMENT_REVERSAL = 'commitment_reversal';
    case COMMITMENT_RELEASE = 'commitment_release';
    case EXPENDITURE = 'expenditure';
    case REFUND = 'refund';
    case PROJECT_CLOSURE_ADJUSTMENT = 'project_closure_adjustment';

    public function label(): string
    {
        return match ($this) {
            self::INITIAL_ALLOCATION => 'Peruntukan Awal',
            self::ALLOCATION_ADJUSTMENT => 'Pelarasan Peruntukan',
            self::COMMITMENT => 'Komitmen',
            self::COMMITMENT_REVERSAL => 'Pembalikan Komitmen',
            self::COMMITMENT_RELEASE => 'Pelepasan Komitmen',
            self::EXPENDITURE => 'Perbelanjaan',
            self::REFUND => 'Bayaran Balik',
            self::PROJECT_CLOSURE_ADJUSTMENT => 'Pelarasan Penutupan Projek',
        };
    }

    /**
     * Kesan transaksi terhadap tiga baldi, diberi nilai wang bertanda $amount.
     * Menggunakan aritmetik wang tepat (Money), tanpa float.
     *
     * @return array{allocation: Money, committed: Money, spent: Money}
     */
    public function deltas(Money $amount): array
    {
        $zero = Money::zero();
        $base = ['allocation' => $zero, 'committed' => $zero, 'spent' => $zero];

        return match ($this) {
            self::INITIAL_ALLOCATION => [...$base, 'allocation' => $amount],
            self::ALLOCATION_ADJUSTMENT => [...$base, 'allocation' => $amount],
            self::PROJECT_CLOSURE_ADJUSTMENT => [...$base, 'allocation' => $amount],
            self::COMMITMENT => [...$base, 'committed' => $amount],
            self::COMMITMENT_REVERSAL => [...$base, 'committed' => $amount->negate()],
            self::COMMITMENT_RELEASE => [...$base, 'committed' => $amount->negate()],
            self::EXPENDITURE => [...$base, 'committed' => $amount->negate(), 'spent' => $amount],
            // REFUND membalikkan EXPENDITURE: perbelanjaan dipulangkan → spent turun,
            // dana kembali kepada komitmen projek (committed naik).
            self::REFUND => [...$base, 'committed' => $amount, 'spent' => $amount->negate()],
        };
    }

    /** Jenis yang membenarkan nilai negatif (pelarasan dua hala). */
    public function allowsNegative(): bool
    {
        return in_array($this, [self::ALLOCATION_ADJUSTMENT, self::PROJECT_CLOSURE_ADJUSTMENT], true);
    }

    /** Warna badge (kelas Tailwind). */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::INITIAL_ALLOCATION => 'bg-navy-100 text-navy-800',
            self::ALLOCATION_ADJUSTMENT => 'bg-blue-100 text-blue-800',
            self::COMMITMENT => 'bg-amber-100 text-amber-800',
            self::COMMITMENT_REVERSAL => 'bg-gray-100 text-gray-700',
            self::COMMITMENT_RELEASE => 'bg-teal-100 text-teal-800',
            self::EXPENDITURE => 'bg-purple-100 text-purple-800',
            self::REFUND => 'bg-green-100 text-green-800',
            self::PROJECT_CLOSURE_ADJUSTMENT => 'bg-gray-100 text-gray-700',
        };
    }
}
