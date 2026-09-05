<?php

namespace App\Services\Reports;

use App\Enums\ApplicationStatus;
use App\Enums\BudgetTransactionType;
use App\Enums\ProjectExpenseStatus;
use App\Enums\ProjectStatus;
use App\Enums\RefundStatus;
use App\Models\Application;
use App\Models\BudgetTransaction;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use Illuminate\Support\Collection;

/**
 * Semakan kualiti data / kawalan dalaman. LAPOR SAHAJA — tidak membaiki apa-apa.
 * Setiap semakan mengembalikan senarai rekod yang melanggar invarian yang
 * dijangka. Sistem yang sihat: semua sifar.
 */
class DataQualityService
{
    public function __construct(private readonly FinancialReportService $financial) {}

    /** @return array<string, array{label: string, items: Collection}> */
    public function run(int $financialYearId): array
    {
        return [
            'reconciliation_mismatch' => [
                'label' => 'Projek dengan ketakpadanan rekonsiliasi',
                'items' => $this->reconciliationMismatch($financialYearId),
            ],
            'approved_without_project' => [
                'label' => 'Permohonan diluluskan tanpa projek',
                'items' => $this->approvedWithoutProject($financialYearId),
            ],
            'approved_without_commitment' => [
                'label' => 'Permohonan diluluskan tanpa komitmen ledger',
                'items' => $this->approvedWithoutCommitment($financialYearId),
            ],
            'verified_expense_no_ledger' => [
                'label' => 'Perbelanjaan disahkan tanpa transaksi ledger',
                'items' => $this->verifiedExpenseMissingLedger($financialYearId),
            ],
            'verified_refund_no_ledger' => [
                'label' => 'Refund disahkan tanpa transaksi ledger',
                'items' => $this->verifiedRefundMissingLedger($financialYearId),
            ],
            'closed_with_outstanding' => [
                'label' => 'Projek ditutup dengan baki komitmen',
                'items' => $this->closedWithOutstanding($financialYearId),
            ],
        ];
    }

    public function totalExceptions(int $financialYearId): int
    {
        return collect($this->run($financialYearId))->sum(fn ($c) => $c['items']->count());
    }

    private function reconciliationMismatch(int $fyId): Collection
    {
        return $this->financial->reconciliationExceptions($fyId)
            ->map(fn ($r) => [
                'ref' => $r['project']->project_number,
                'detail' => sprintf('Diluluskan RM%s ≠ dikira semula RM%s', $r['expected']->format(), $r['recomputed']->format()),
            ]);
    }

    private function approvedWithoutProject(int $fyId): Collection
    {
        return Application::query()
            ->where('financial_year_id', $fyId)
            ->where('status', ApplicationStatus::APPROVED->value)
            ->whereDoesntHave('project')
            ->get(['id', 'application_number'])
            ->map(fn ($a) => ['ref' => $a->application_number, 'detail' => 'Tiada rekod projek berkaitan']);
    }

    private function approvedWithoutCommitment(int $fyId): Collection
    {
        return Project::query()
            ->where('financial_year_id', $fyId)
            ->whereDoesntHave('budgetTransactions', fn ($q) => $q->where('type', BudgetTransactionType::COMMITMENT->value))
            ->get(['id', 'project_number'])
            ->map(fn ($p) => ['ref' => $p->project_number, 'detail' => 'Tiada transaksi COMMITMENT']);
    }

    private function verifiedExpenseMissingLedger(int $fyId): Collection
    {
        return ProjectExpense::query()
            ->where('status', ProjectExpenseStatus::VERIFIED->value)
            ->whereHas('project', fn ($q) => $q->where('financial_year_id', $fyId))
            ->whereDoesntHave('transaction')
            ->get(['id', 'reference_number', 'project_id'])
            ->map(fn ($e) => ['ref' => $e->reference_number, 'detail' => 'Tiada transaksi EXPENDITURE dalam ledger']);
    }

    private function verifiedRefundMissingLedger(int $fyId): Collection
    {
        return ProjectExpenseRefund::query()
            ->where('status', RefundStatus::VERIFIED->value)
            ->whereHas('project', fn ($q) => $q->where('financial_year_id', $fyId))
            ->whereDoesntHave('transaction')
            ->get(['id', 'reference_number', 'project_id'])
            ->map(fn ($r) => ['ref' => $r->reference_number ?? ('RF#'.$r->id), 'detail' => 'Tiada transaksi REFUND dalam ledger']);
    }

    private function closedWithOutstanding(int $fyId): Collection
    {
        return Project::query()
            ->where('financial_year_id', $fyId)
            ->where('status', ProjectStatus::CLOSED->value)
            ->get()
            ->map(function (Project $p) {
                // Baki komitmen dari ledger untuk projek ini.
                $committed = \App\Support\Money::zero();
                $rows = BudgetTransaction::where('project_id', $p->id)->groupBy('type')->selectRaw('type, SUM(amount) as total')->get();
                foreach ($rows as $row) {
                    $committed = $committed->plus($row->type->deltas(\App\Support\Money::of((string) $row->total))['committed']);
                }

                return ['project' => $p, 'committed' => $committed];
            })
            ->filter(fn ($r) => $r['committed']->isPositive())
            ->map(fn ($r) => ['ref' => $r['project']->project_number, 'detail' => 'Baki komitmen RM'.$r['committed']->format().' selepas penutupan'])
            ->values();
    }
}
