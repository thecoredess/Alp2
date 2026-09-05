<?php

namespace App\Services\Budget;

use App\Support\Money;

/**
 * Ringkasan bajet yang dikira sepenuhnya dari ledger menggunakan aritmetik
 * wang tepat (Money/BCMath). Objek nilai tidak boleh ubah — bukan sumber
 * kebenaran, hanya hasil pengiraan. TIADA float.
 */
final class BudgetSummary
{
    public readonly Money $allocation;
    public readonly Money $committed;
    public readonly Money $spent;

    public function __construct(?Money $allocation = null, ?Money $committed = null, ?Money $spent = null)
    {
        $this->allocation = $allocation ?? Money::zero();
        $this->committed = $committed ?? Money::zero();
        $this->spent = $spent ?? Money::zero();
    }

    /** Available = Allocation − Committed − Spent (tepat). */
    public function available(): Money
    {
        return $this->allocation->minus($this->committed)->minus($this->spent);
    }

    /**
     * Peratus penggunaan (committed + spent) berbanding peruntukan.
     * Operan wang kekal tepat; hasil nisbah dipulangkan sebagai float untuk PAPARAN sahaja.
     */
    public function utilisationPercent(): float
    {
        $used = $this->committed->plus($this->spent);

        return $used->percentageOf($this->allocation, 1);
    }

    /** Gabung dua ringkasan (untuk jumlah keseluruhan). */
    public function plus(BudgetSummary $other): self
    {
        return new self(
            $this->allocation->plus($other->allocation),
            $this->committed->plus($other->committed),
            $this->spent->plus($other->spent),
        );
    }

    /** Wakil array (nilai wang sebagai string kanonik). */
    public function toArray(): array
    {
        return [
            'allocation' => $this->allocation->value(),
            'committed' => $this->committed->value(),
            'spent' => $this->spent->value(),
            'available' => $this->available()->value(),
            'utilisation' => $this->utilisationPercent(),
        ];
    }
}
