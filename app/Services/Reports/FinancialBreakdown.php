<?php

namespace App\Services\Reports;

use App\Enums\BudgetTransactionType;
use App\Support\Money;

/**
 * Pecahan kewangan penuh yang DIKIRA DARI LEDGER (budget_transactions) menggunakan
 * kesan baldi rasmi (BudgetTransactionType::deltas) — sumber kebenaran tunggal,
 * TIADA float. Objek nilai tidak boleh ubah untuk pelaporan.
 *
 * Definisi (selaras dengan BudgetService/ProjectFinancialService):
 *   Allocation      = Peruntukan Awal + Pelarasan
 *   Committed       = baki komitmen belum guna (baldi committed)
 *   Gross Spent     = jumlah EXPENDITURE
 *   Refunded        = jumlah REFUND
 *   Net Spent       = Gross − Refund (baldi spent)
 *   Released        = jumlah COMMITMENT_RELEASE (dilepaskan semasa penutupan)
 *   Available       = Allocation − Committed − Net Spent
 *   Pending         = jumlah permohonan menunggu (BUKAN komitmen — jangan campur)
 *   Projected Avail = Available − Pending
 */
final class FinancialBreakdown
{
    public readonly Money $allocation;
    public readonly Money $committed;
    public readonly Money $grossSpent;
    public readonly Money $refunded;
    public readonly Money $released;
    public readonly Money $pending;

    public function __construct(
        ?Money $allocation = null,
        ?Money $committed = null,
        ?Money $grossSpent = null,
        ?Money $refunded = null,
        ?Money $released = null,
        ?Money $pending = null,
    ) {
        $this->allocation = $allocation ?? Money::zero();
        $this->committed = $committed ?? Money::zero();
        $this->grossSpent = $grossSpent ?? Money::zero();
        $this->refunded = $refunded ?? Money::zero();
        $this->released = $released ?? Money::zero();
        $this->pending = $pending ?? Money::zero();
    }

    /**
     * Bina daripada jumlah ledger berkumpulan mengikut jenis.
     *
     * @param  array<string, Money>  $typeSums  [type_value => SUM(amount)]
     */
    public static function fromTypeSums(array $typeSums, ?Money $pending = null): self
    {
        $pending ??= Money::zero();
        $allocation = Money::zero();
        $committedBucket = Money::zero();
        $gross = Money::zero();
        $refunded = Money::zero();
        $released = Money::zero();

        foreach ($typeSums as $typeValue => $amount) {
            $type = $typeValue instanceof BudgetTransactionType ? $typeValue : BudgetTransactionType::from((string) $typeValue);
            $d = $type->deltas($amount);
            $allocation = $allocation->plus($d['allocation']);
            $committedBucket = $committedBucket->plus($d['committed']);

            if ($type === BudgetTransactionType::EXPENDITURE) {
                $gross = $gross->plus($amount);
            } elseif ($type === BudgetTransactionType::REFUND) {
                $refunded = $refunded->plus($amount);
            } elseif ($type === BudgetTransactionType::COMMITMENT_RELEASE) {
                $released = $released->plus($amount);
            }
        }

        return new self($allocation, $committedBucket, $gross, $refunded, $released, $pending);
    }

    /** Net Spent = Gross − Refund (bersamaan baldi spent). */
    public function netSpent(): Money
    {
        return $this->grossSpent->minus($this->refunded);
    }

    /** Available = Allocation − Committed − Net Spent. */
    public function available(): Money
    {
        return $this->allocation->minus($this->committed)->minus($this->netSpent());
    }

    /** Projected Available = Available − Pending Application. */
    public function projectedAvailable(): Money
    {
        return $this->available()->minus($this->pending);
    }

    /** Net Utilisation % = (Net Spent / Allocation) × 100. */
    public function netUtilisationPercent(): float
    {
        return $this->netSpent()->percentageOf($this->allocation, 1);
    }

    /** Commitment Utilisation % = ((Committed + Net Spent) / Allocation) × 100. */
    public function commitmentUtilisationPercent(): float
    {
        return $this->committed->plus($this->netSpent())->percentageOf($this->allocation, 1);
    }

    public function withPending(Money $pending): self
    {
        return new self($this->allocation, $this->committed, $this->grossSpent, $this->refunded, $this->released, $pending);
    }

    public function plus(self $other): self
    {
        return new self(
            $this->allocation->plus($other->allocation),
            $this->committed->plus($other->committed),
            $this->grossSpent->plus($other->grossSpent),
            $this->refunded->plus($other->refunded),
            $this->released->plus($other->released),
            $this->pending->plus($other->pending),
        );
    }

    /** @return array<string, string|float> nilai wang sebagai string kanonik */
    public function toArray(): array
    {
        return [
            'allocation' => $this->allocation->value(),
            'committed' => $this->committed->value(),
            'gross_spent' => $this->grossSpent->value(),
            'refunded' => $this->refunded->value(),
            'net_spent' => $this->netSpent()->value(),
            'released' => $this->released->value(),
            'available' => $this->available()->value(),
            'pending' => $this->pending->value(),
            'projected_available' => $this->projectedAvailable()->value(),
            'net_utilisation' => $this->netUtilisationPercent(),
        ];
    }
}
