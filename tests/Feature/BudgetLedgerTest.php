<?php

namespace Tests\Feature;

use App\Enums\BudgetTransactionType;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Models\FinancialYear;
use App\Services\Budget\BudgetException;
use App\Services\Budget\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BudgetLedgerTest extends TestCase
{
    use RefreshDatabase;

    private BudgetService $budget;

    protected function setUp(): void
    {
        parent::setUp();
        $this->budget = app(BudgetService::class);
    }

    private function alpAndYear(): array
    {
        return [Alp::factory()->create(), FinancialYear::factory()->active()->create()];
    }

    /** Rekod transaksi mentah dalam ledger (untuk simulasi fasa akan datang). */
    private function record(Allocation $a, BudgetTransactionType $type, float $amount): void
    {
        BudgetTransaction::create([
            'allocation_id' => $a->id,
            'alp_id' => $a->alp_id,
            'financial_year_id' => $a->financial_year_id,
            'type' => $type,
            'amount' => $amount,
            'created_at' => now(),
        ]);
    }

    public function test_allocate_creates_ledger_transaction_and_sets_balance(): void
    {
        [$alp, $year] = $this->alpAndYear();

        $allocation = $this->budget->allocate($alp, $year, 500000, 'REF/1');

        $this->assertDatabaseHas('budget_transactions', [
            'allocation_id' => $allocation->id,
            'type' => BudgetTransactionType::INITIAL_ALLOCATION->value,
            'amount' => 500000.00,
        ]);

        $summary = $this->budget->summaryFor($alp->id, $year->id);
        $this->assertSame('500000.00', $summary->allocation->value());
        $this->assertSame('500000.00', $summary->available()->value());
    }

    public function test_cannot_allocate_twice_for_same_alp_and_year(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $this->budget->allocate($alp, $year, 500000);

        $this->expectException(BudgetException::class);
        $this->budget->allocate($alp, $year, 100000);
    }

    public function test_cannot_allocate_zero_or_negative(): void
    {
        [$alp, $year] = $this->alpAndYear();

        $this->expectException(BudgetException::class);
        $this->budget->allocate($alp, $year, 0);
    }

    public function test_cannot_allocate_in_closed_year(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->closed()->create();

        $this->expectException(BudgetException::class);
        $this->budget->allocate($alp, $year, 500000);
    }

    public function test_adjustment_updates_allocation_via_new_transaction(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, 500000);

        $this->budget->adjust($allocation, 50000, 'PIND/1', 'Tambahan');
        $this->assertSame('550000.00', $this->budget->summaryFor($alp->id, $year->id)->allocation->value());

        $this->budget->adjust($allocation, -100000, 'PIND/2', 'Pengurangan');
        $this->assertSame('450000.00', $this->budget->summaryFor($alp->id, $year->id)->allocation->value());

        // 1 initial + 2 adjustments = 3 transaksi (append-only).
        $this->assertSame(3, BudgetTransaction::where('allocation_id', $allocation->id)->count());
    }

    public function test_adjustment_cannot_reduce_below_committed_plus_spent(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, 500000);

        // Komitmen 300k, kemudian belanja 100k daripadanya.
        // Committed baki = 200k, spent = 100k → jumlah terikat = 300k.
        $this->record($allocation, BudgetTransactionType::COMMITMENT, 300000);
        $this->record($allocation, BudgetTransactionType::EXPENDITURE, 100000);

        // Turunkan peruntukan 250k → 250k, kurang daripada 300k terikat → ditolak.
        $this->expectException(BudgetException::class);
        $this->budget->adjust($allocation, -250000, 'PIND/X', 'Terlebih kurang');
    }

    public function test_ledger_transactions_are_immutable(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, 500000);
        $txn = BudgetTransaction::where('allocation_id', $allocation->id)->first();

        $this->expectException(RuntimeException::class);
        $txn->update(['amount' => 999999]);
    }

    public function test_ledger_transactions_cannot_be_deleted(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, 500000);
        $txn = BudgetTransaction::where('allocation_id', $allocation->id)->first();

        $this->expectException(RuntimeException::class);
        $txn->delete();
    }

    public function test_balance_has_no_double_counting_between_committed_and_spent(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, 500000);

        // Commit 100k.
        $this->record($allocation, BudgetTransactionType::COMMITMENT, 100000);
        $s1 = $this->budget->summaryFor($alp->id, $year->id);
        $this->assertSame('100000.00', $s1->committed->value());
        $this->assertSame('0.00', $s1->spent->value());
        $this->assertSame('400000.00', $s1->available()->value());

        // Belanja 60k daripada komitmen itu → committed turun, spent naik, available kekal.
        $this->record($allocation, BudgetTransactionType::EXPENDITURE, 60000);
        $s2 = $this->budget->summaryFor($alp->id, $year->id);
        $this->assertSame('40000.00', $s2->committed->value());   // 100k - 60k
        $this->assertSame('60000.00', $s2->spent->value());
        $this->assertSame('400000.00', $s2->available()->value()); // tiada double-count

        // Balik baki komitmen 40k → available naik.
        $this->record($allocation, BudgetTransactionType::COMMITMENT_REVERSAL, 40000);
        $s3 = $this->budget->summaryFor($alp->id, $year->id);
        $this->assertSame('0.00', $s3->committed->value());
        $this->assertSame('60000.00', $s3->spent->value());
        $this->assertSame('440000.00', $s3->available()->value());

        // Refund 10k (Fasa 5A): membalikkan EXPENDITURE → spent turun, komitmen
        // (outstanding) dipulihkan. Available kekal kerana dana pulih kembali kepada
        // baki komitmen projek (dilepaskan ke available hanya semasa penutupan).
        $this->record($allocation, BudgetTransactionType::REFUND, 10000);
        $s4 = $this->budget->summaryFor($alp->id, $year->id);
        $this->assertSame('10000.00', $s4->committed->value());     // 0 + 10k dipulihkan
        $this->assertSame('50000.00', $s4->spent->value());          // 60k - 10k
        $this->assertSame('440000.00', $s4->available()->value());   // kekal (tiada double-count)
    }

    public function test_year_totals_sum_across_all_alps(): void
    {
        $year = FinancialYear::factory()->active()->create();
        $this->budget->allocate(Alp::factory()->create(), $year, 500000);
        $this->budget->allocate(Alp::factory()->create(), $year, 300000);

        $totals = $this->budget->totalsForYear($year->id);
        $this->assertSame('800000.00', $totals->allocation->value());
    }

    public function test_allocate_and_adjust_write_audit_records(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, 500000);
        $this->budget->adjust($allocation, 25000, null, 'Tambah');

        $this->assertDatabaseHas('audit_logs', ['action' => 'ALLOCATE', 'entity_id' => $allocation->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ADJUST', 'entity_id' => $allocation->id]);
    }
}
