<?php

namespace Tests\Feature;

use App\Enums\BudgetTransactionType;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Models\FinancialYear;
use App\Services\Budget\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ujian regresi ketepatan wang — dijalankan terhadap MySQL 8.4 (lihat phpunit.xml)
 * untuk membuktikan DECIMAL(15,2), SUM, dan baki berjalan adalah tepat.
 */
class BudgetPrecisionTest extends TestCase
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

    private function record(Allocation $a, BudgetTransactionType $type, string $amount): void
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

    public function test_decimal_values_persist_exactly_in_mysql(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $this->budget->allocate($alp, $year, '0.10');
        $allocation = Allocation::first();
        $this->budget->adjust($allocation, '0.20', null, 'ujian');

        // Nilai disimpan tepat sebagai string DECIMAL.
        $this->assertDatabaseHas('budget_transactions', ['amount' => '0.10']);
        $this->assertDatabaseHas('budget_transactions', ['amount' => '0.20']);

        // 0.10 + 0.20 = 0.30 (tepat).
        $this->assertSame('0.30', $this->budget->summaryFor($alp->id, $year->id)->allocation->value());
    }

    public function test_full_transaction_sequence_is_exact_to_two_decimals(): void
    {
        [$alp, $year] = $this->alpAndYear();

        // allocation + adjustment
        $allocation = $this->budget->allocate($alp, $year, '500000.00');
        $this->budget->adjust($allocation, '123456.78', null, 'pelarasan');

        // - commitment + commitment reversal - expenditure + refund
        $this->record($allocation, BudgetTransactionType::COMMITMENT, '100000.50');
        $this->record($allocation, BudgetTransactionType::COMMITMENT_REVERSAL, '0.50');
        $this->record($allocation, BudgetTransactionType::EXPENDITURE, '50000.33');
        $this->record($allocation, BudgetTransactionType::REFUND, '0.33');

        $s = $this->budget->summaryFor($alp->id, $year->id);

        // allocation = 500000.00 + 123456.78 = 623456.78
        $this->assertSame('623456.78', $s->allocation->value());
        // committed = 100000.50 - 0.50 - 50000.33 + 0.33 (refund pulih) = 50000.00
        $this->assertSame('50000.00', $s->committed->value());
        // spent = 50000.33 - 0.33 = 50000.00
        $this->assertSame('50000.00', $s->spent->value());
        // available = 623456.78 - 50000.00 - 50000.00 = 523456.78
        $this->assertSame('523456.78', $s->available()->value());
    }

    public function test_many_one_cent_transactions_have_no_drift(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, '100.00');

        // 100 komitmen × 0.01 = 1.00 tepat (float akan menyimpang, cth 1.0000000000000007).
        for ($i = 0; $i < 100; $i++) {
            $this->record($allocation, BudgetTransactionType::COMMITMENT, '0.01');
        }

        $s = $this->budget->summaryFor($alp->id, $year->id);
        $this->assertSame('1.00', $s->committed->value());
        $this->assertSame('99.00', $s->available()->value());
    }

    public function test_running_ledger_balance_is_exact(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, '1000000.10');
        $this->budget->adjust($allocation, '0.20', null, 'ujian');

        $statement = $this->budget->statement($allocation);

        $this->assertSame('1000000.10', $statement[0]['running_available']->value());
        $this->assertSame('1000000.30', $statement[1]['running_available']->value());
    }

    public function test_mysql_sum_matches_service_calculation(): void
    {
        [$alp, $year] = $this->alpAndYear();
        $allocation = $this->budget->allocate($alp, $year, '333333.33');
        $this->budget->adjust($allocation, '333333.33', null, 'a');
        $this->budget->adjust($allocation, '333333.34', null, 'b');

        // SUM langsung dari MySQL.
        $sqlSum = (string) BudgetTransaction::where('allocation_id', $allocation->id)->sum('amount');
        $this->assertSame('1000000.00', number_format((float) $sqlSum, 2, '.', ''));

        // Perkhidmatan (Money) menghasilkan nilai yang sama, tepat.
        $this->assertSame('1000000.00', $this->budget->summaryFor($alp->id, $year->id)->allocation->value());
    }
}
