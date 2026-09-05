<?php

namespace App\Services\Budget;

use App\Enums\BudgetTransactionType;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Models\FinancialYear;
use App\Services\Audit\AuditService;
use App\Support\Money;
use App\Support\UrsContributionPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Logik teras bajet. Ledger (budget_transactions) ialah satu-satunya sumber
 * kebenaran kewangan; semua baki dikira dengan menjumlahkan transaksi
 * menggunakan aritmetik wang tepat (Money/BCMath) — TIADA float.
 */
class BudgetService
{
    public function __construct(private readonly AuditService $audit) {}

    // ── Pengiraan baki ──────────────────────────────────────────

    /** Ringkasan bajet untuk satu ALP dalam satu tahun kewangan. */
    public function summaryFor(int $alpId, int $financialYearId): BudgetSummary
    {
        $rows = BudgetTransaction::query()
            ->where('alp_id', $alpId)
            ->where('financial_year_id', $financialYearId)
            ->groupBy('type')
            ->selectRaw('type, SUM(amount) as total')
            ->get();

        return $this->summaryFromRows($rows);
    }

    /**
     * Ringkasan per-ALP untuk satu tahun kewangan (paparan pengurusan).
     *
     * @return Collection<int, BudgetSummary> dikunci oleh alp_id
     */
    public function summariesForYear(int $financialYearId): Collection
    {
        return BudgetTransaction::query()
            ->where('financial_year_id', $financialYearId)
            ->groupBy('alp_id', 'type')
            ->selectRaw('alp_id, type, SUM(amount) as total')
            ->get()
            ->groupBy('alp_id')
            ->map(fn ($rows) => $this->summaryFromRows($rows));
    }

    /** Jumlah keseluruhan (semua ALP) untuk satu tahun kewangan. */
    public function totalsForYear(int $financialYearId): BudgetSummary
    {
        return $this->summariesForYear($financialYearId)
            ->reduce(fn (BudgetSummary $carry, BudgetSummary $s) => $carry->plus($s), new BudgetSummary());
    }

    /**
     * Bina ringkasan daripada baris transaksi berkumpulan (mempunyai `type` & `total`).
     * SUM(amount) dari MySQL adalah DECIMAL tepat (dipulangkan sebagai string).
     *
     * @param  Collection<int, BudgetTransaction>  $rows
     */
    private function summaryFromRows(Collection $rows): BudgetSummary
    {
        $allocation = Money::zero();
        $committed = Money::zero();
        $spent = Money::zero();

        foreach ($rows as $row) {
            // $row->total ialah string DECIMAL dari SUM() — tepat, tanpa float.
            $deltas = $row->type->deltas(Money::of((string) $row->total));
            $allocation = $allocation->plus($deltas['allocation']);
            $committed = $committed->plus($deltas['committed']);
            $spent = $spent->plus($deltas['spent']);
        }

        return new BudgetSummary($allocation, $committed, $spent);
    }

    // ── Operasi peruntukan ──────────────────────────────────────

    /**
     * Cipta peruntukan awal untuk ALP bagi satu tahun kewangan.
     * Atomik & diaudit. Hanya satu peruntukan awal dibenarkan per ALP×tahun.
     */
    public function allocate(
        Alp $alp,
        FinancialYear $year,
        Money|int|string $amount,
        ?string $referenceNo = null,
        ?string $remarks = null,
        ?\App\Models\BudgetRequest $request = null,
    ): Allocation {
        $this->assertYearOpen($year);
        $amount = $amount instanceof Money ? $amount : Money::of($amount);

        if (! $amount->isPositive()) {
            throw new BudgetException('Jumlah peruntukan mesti lebih daripada sifar.');
        }

        foreach (UrsContributionPolicy::validateAnnualAllocationForAlp($amount, $alp, (int) $year->year) as $error) {
            throw new BudgetException($error);
        }

        if (Allocation::where('alp_id', $alp->id)->where('financial_year_id', $year->id)->exists()) {
            throw new BudgetException('ALP ini telah mempunyai peruntukan bagi tahun kewangan ini. Gunakan pelarasan.');
        }

        return DB::transaction(function () use ($alp, $year, $amount, $referenceNo, $remarks, $request) {
            $allocation = Allocation::create([
                'alp_id' => $alp->id,
                'financial_year_id' => $year->id,
                'reference_no' => $referenceNo,
                'remarks' => $remarks,
                'created_by' => auth()->id(),
            ]);

            $txn = $this->recordTransaction(
                $allocation,
                BudgetTransactionType::INITIAL_ALLOCATION,
                $amount,
                $referenceNo,
                'Peruntukan awal',
                null,
                $request?->id,
            );

            $this->audit->log($request ? 'INITIAL_ALLOCATION_CREATED' : 'ALLOCATE', $allocation, null, [
                'amount' => $amount->value(),
                'reference_no' => $referenceNo,
                'transaction_id' => $txn->id,
                'budget_request_id' => $request?->id,
            ]);

            return $allocation;
        });
    }

    /**
     * Laras peruntukan (naik atau turun). $amount bertanda: negatif = pengurangan.
     * Tidak boleh menurunkan peruntukan di bawah (committed + spent).
     */
    public function adjust(
        Allocation $allocation,
        Money|int|string $amount,
        ?string $referenceNo = null,
        ?string $remarks = null,
        ?\App\Models\BudgetRequest $request = null,
    ): BudgetTransaction {
        $allocation->loadMissing('financialYear');
        $this->assertYearOpen($allocation->financialYear);
        $amount = $amount instanceof Money ? $amount : Money::of($amount);

        if ($amount->isZero()) {
            throw new BudgetException('Jumlah pelarasan tidak boleh sifar.');
        }

        $current = $this->summaryFor($allocation->alp_id, $allocation->financial_year_id);
        $newAllocation = $current->allocation->plus($amount);
        $usedFunds = $current->committed->plus($current->spent);

        if ($newAllocation->lessThan($usedFunds)) {
            throw new BudgetException(sprintf(
                'Pelarasan ditolak: peruntukan baharu (RM%s) akan menjadi kurang daripada jumlah telah diguna/dikomit (RM%s).',
                $newAllocation->format(),
                $usedFunds->format(),
            ));
        }

        $allocation->loadMissing('alp');
        foreach (UrsContributionPolicy::validateAnnualAllocationForAlp(
            $newAllocation,
            $allocation->alp,
            (int) $allocation->financialYear->year,
        ) as $error) {
            throw new BudgetException($error);
        }

        return DB::transaction(function () use ($allocation, $amount, $referenceNo, $remarks, $request) {
            $txn = $this->recordTransaction(
                $allocation,
                BudgetTransactionType::ALLOCATION_ADJUSTMENT,
                $amount,
                $referenceNo,
                $remarks ?? 'Pelarasan peruntukan',
                null,
                $request?->id,
            );

            $this->audit->log($request ? 'ALLOCATION_ADJUSTMENT_CREATED' : 'ADJUST', $allocation, null, [
                'amount' => $amount->value(),
                'reference_no' => $referenceNo,
                'remarks' => $remarks,
                'transaction_id' => $txn->id,
                'budget_request_id' => $request?->id,
            ]);

            return $txn;
        });
    }

    /**
     * Penyata ledger dengan baki available berjalan (untuk paparan sejarah).
     *
     * @return array<int, array{transaction: BudgetTransaction, running_available: Money}>
     */
    public function statement(Allocation $allocation): array
    {
        $running = Money::zero();
        $out = [];

        $transactions = BudgetTransaction::where('allocation_id', $allocation->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($transactions as $txn) {
            $deltas = $txn->type->deltas(Money::of((string) $txn->amount));
            // Kesan bersih terhadap available: +allocation −committed −spent.
            $running = $running
                ->plus($deltas['allocation'])
                ->minus($deltas['committed'])
                ->minus($deltas['spent']);
            $out[] = ['transaction' => $txn, 'running_available' => $running];
        }

        return $out;
    }

    // ── Bantuan dalaman ─────────────────────────────────────────

    /**
     * Rekod transaksi COMMITMENT yang dikaitkan dengan permohonan.
     * Dipanggil oleh FinalApprovalService dalam transaksi atomiknya.
     * Idempotensi dijamin oleh unique(application_id, type) pada jadual ledger.
     */
    public function recordCommitment(
        Allocation $allocation,
        Money $amount,
        \App\Models\Application $application,
        ?string $referenceNo = null,
        ?\App\Models\Project $project = null,
    ): BudgetTransaction {
        $txn = $this->recordTransaction(
            $allocation,
            BudgetTransactionType::COMMITMENT,
            $amount,
            $referenceNo ?? $application->application_number,
            'Komitmen bajet — permohonan diluluskan',
            $application->id,
            null,
            $project?->id,
        );

        $this->audit->log('BUDGET_COMMITMENT_CREATED', $txn, null, [
            'application_id' => $application->id,
            'application_number' => $application->application_number,
            'amount' => $amount->value(),
        ]);

        return $txn;
    }

    /** Rekod perbelanjaan sebenar (EXPENDITURE) — dikaitkan projek & perbelanjaan. */
    public function recordExpenditure(
        Allocation $allocation,
        Money $amount,
        \App\Models\Project $project,
        \App\Models\ProjectExpense $expense,
    ): BudgetTransaction {
        $txn = $this->recordTransaction(
            $allocation,
            BudgetTransactionType::EXPENDITURE,
            $amount,
            $expense->reference_number,
            'Perbelanjaan projek '.$project->project_number,
            null,
            null,
            $project->id,
            $expense->id,
        );

        $this->audit->log('BUDGET_EXPENDITURE_CREATED', $txn, null, [
            'project_id' => $project->id,
            'project_expense_id' => $expense->id,
            'amount' => $amount->value(),
        ]);

        return $txn;
    }

    /** Rekod refund (REFUND) — membalikkan sebahagian perbelanjaan yang disahkan. */
    public function recordRefund(
        Allocation $allocation,
        Money $amount,
        \App\Models\Project $project,
        \App\Models\ProjectExpenseRefund $refund,
    ): BudgetTransaction {
        // NOTA: project_expense_id sengaja TIDAK diisi di sini. Kekangan unik
        // bt_project_expense_unique menjamin SATU EXPENDITURE setiap perbelanjaan;
        // pautan refund→perbelanjaan wujud melalui project_expense_refund_id.
        $txn = $this->recordTransaction(
            $allocation,
            BudgetTransactionType::REFUND,
            $amount,
            $refund->reference_number,
            'Refund perbelanjaan projek '.$project->project_number,
            null,
            null,
            $project->id,
            null,
            $refund->id,
        );

        $this->audit->log('BUDGET_REFUND_CREATED', $txn, null, [
            'project_id' => $project->id,
            'project_expense_refund_id' => $refund->id,
            'amount' => $amount->value(),
        ]);

        return $txn;
    }

    /** Lepaskan baki komitmen tidak diguna (COMMITMENT_RELEASE) semasa penutupan. */
    public function recordCommitmentRelease(
        Allocation $allocation,
        Money $amount,
        \App\Models\Project $project,
    ): BudgetTransaction {
        $txn = $this->recordTransaction(
            $allocation,
            BudgetTransactionType::COMMITMENT_RELEASE,
            $amount,
            $project->project_number,
            'Pelepasan baki komitmen — penutupan projek',
            null,
            null,
            $project->id,
        );

        $this->audit->log('COMMITMENT_RELEASE_CREATED', $txn, null, [
            'project_id' => $project->id,
            'amount' => $amount->value(),
        ]);

        return $txn;
    }

    /** Ringkasan kewangan bagi satu projek (committed baki & spent) dari ledger. */
    public function summaryForProject(int $projectId): BudgetSummary
    {
        $rows = BudgetTransaction::query()
            ->where('project_id', $projectId)
            ->groupBy('type')
            ->selectRaw('type, SUM(amount) as total')
            ->get();

        return $this->summaryFromRows($rows);
    }

    private function recordTransaction(
        Allocation $allocation,
        BudgetTransactionType $type,
        Money $amount,
        ?string $referenceNo,
        ?string $description,
        ?int $applicationId = null,
        ?int $budgetRequestId = null,
        ?int $projectId = null,
        ?int $projectExpenseId = null,
        ?int $projectExpenseRefundId = null,
    ): BudgetTransaction {
        return BudgetTransaction::create([
            'allocation_id' => $allocation->id,
            'alp_id' => $allocation->alp_id,
            'financial_year_id' => $allocation->financial_year_id,
            'application_id' => $applicationId,
            'budget_request_id' => $budgetRequestId,
            'project_id' => $projectId,
            'project_expense_id' => $projectExpenseId,
            'project_expense_refund_id' => $projectExpenseRefundId,
            'type' => $type,
            'amount' => $amount->value(), // string kanonik → DECIMAL(15,2)
            'reference_no' => $referenceNo,
            'description' => $description,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);
    }

    private function assertYearOpen(FinancialYear $year): void
    {
        if ($year->isClosed()) {
            throw new BudgetException('Tahun kewangan telah ditutup — tiada perubahan bajet dibenarkan.');
        }
    }
}
