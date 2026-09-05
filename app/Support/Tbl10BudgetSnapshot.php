<?php

namespace App\Support;

use App\Models\Application;
use App\Services\Budget\BudgetSummary;

/**
 * Pemetaan TBL-10 F–J (ruang kegunaan JP) dari ledger + permohonan semasa.
 */
final class Tbl10BudgetSnapshot
{
    public function __construct(
        public readonly Money $allocation,       // F
        public readonly Money $approvedSpend,    // G — komitmen (+ belanja legacy jika ada)
        public readonly Money $balance,          // H
        public readonly Money $currentRequest,   // I
        public readonly Money $balanceAfter,     // J
        public readonly Money $pending,          // rujukan operasi
    ) {}

    public static function from(
        Application $application,
        BudgetSummary $summary,
        Money $pending,
    ): self {
        $current = $application->requestedAmountMoney();
        $available = $summary->available();
        // G: perbelanjaan/komitmen terkumpul diluluskan
        $approvedSpend = $summary->committed->plus($summary->spent);
        // J: baki termasuk permohonan semasa (dan pending lain)
        $after = $available->minus($pending);
        // Jika pending tidak termasuk permohonan ini (sudah APPROVED), tolak juga current
        // untuk paparan “selepas permohonan ini” — untuk APPROVED, pending biasanya tiada current.
        if ($after->isNegative()) {
            $after = Money::zero();
        }

        return new self(
            allocation: $summary->allocation,
            approvedSpend: $approvedSpend,
            balance: $available,
            currentRequest: $current,
            balanceAfter: $after,
            pending: $pending,
        );
    }
}
