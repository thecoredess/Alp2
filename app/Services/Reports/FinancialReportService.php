<?php

namespace App\Services\Reports;

use App\Enums\ApplicationStatus;
use App\Enums\BudgetTransactionType;
use App\Enums\ProjectStatus;
use App\Models\Alp;
use App\Models\Application;
use App\Models\BudgetTransaction;
use App\Models\Project;
use App\Services\Project\ProjectFinancialService;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Perkhidmatan pelaporan kewangan. SEMUA nilai rasmi dikira dari ledger
 * (budget_transactions) melalui kesan baldi rasmi — bukan dari rekod UI.
 * Menggunakan pengagregatan pangkalan data (GROUP BY + SUM) untuk elak N+1,
 * dengan aritmetik wang tepat pada hasil DECIMAL.
 */
class FinancialReportService
{
    public function __construct(private readonly ProjectFinancialService $projectFinancial) {}

    /**
     * Pecahan kewangan per-ALP bagi satu tahun kewangan (dikunci oleh alp_id).
     *
     * @return Collection<int, FinancialBreakdown>
     */
    public function alpBreakdowns(int $financialYearId): Collection
    {
        // 1 pertanyaan: jumlah ledger mengikut (alp_id, type).
        $ledger = BudgetTransaction::query()
            ->where('financial_year_id', $financialYearId)
            ->groupBy('alp_id', 'type')
            ->selectRaw('alp_id, type, SUM(amount) as total')
            ->get()
            ->groupBy('alp_id');

        // 1 pertanyaan: jumlah permohonan menunggu mengikut alp_id.
        $pending = $this->pendingByAlp($financialYearId);

        return $ledger->map(function ($rows, $alpId) use ($pending) {
            $sums = [];
            foreach ($rows as $row) {
                $sums[$row->type instanceof BudgetTransactionType ? $row->type->value : $row->type] = Money::of((string) $row->total);
            }

            return FinancialBreakdown::fromTypeSums($sums, $pending[$alpId] ?? Money::zero());
        });
    }

    /** Pecahan bagi satu ALP. */
    public function alpBreakdown(int $alpId, int $financialYearId): FinancialBreakdown
    {
        $rows = BudgetTransaction::query()
            ->where('financial_year_id', $financialYearId)
            ->where('alp_id', $alpId)
            ->groupBy('type')
            ->selectRaw('type, SUM(amount) as total')
            ->get();

        $sums = [];
        foreach ($rows as $row) {
            $sums[$row->type instanceof BudgetTransactionType ? $row->type->value : $row->type] = Money::of((string) $row->total);
        }

        return FinancialBreakdown::fromTypeSums($sums, $this->pendingForAlp($alpId, $financialYearId));
    }

    /** Jumlah keseluruhan (semua ALP) bagi satu tahun kewangan. */
    public function totals(int $financialYearId): FinancialBreakdown
    {
        return $this->alpBreakdowns($financialYearId)
            ->reduce(fn (FinancialBreakdown $c, FinancialBreakdown $b) => $c->plus($b), new FinancialBreakdown());
    }

    /** Pecahan + model ALP untuk jadual penggunaan (management). */
    public function alpUtilisation(int $financialYearId): Collection
    {
        $breakdowns = $this->alpBreakdowns($financialYearId);
        $alps = Alp::whereIn('id', $breakdowns->keys())->orderBy('ref_code')->get();

        return $alps->map(fn (Alp $alp) => [
            'alp' => $alp,
            'breakdown' => $breakdowns[$alp->id] ?? new FinancialBreakdown(),
        ])->values();
    }

    /**
     * Baris ledger dengan baki berjalan (allocation/committed/spent) mengikut susunan masa.
     * Filter: alp_id, financial_year_id, type, date range.
     *
     * @return array{rows: array<int, array>, count: int}
     */
    public function ledgerRows(array $filters): array
    {
        $query = BudgetTransaction::query()
            ->with(['alp:id,ref_code,name'])
            ->when($filters['financial_year_id'] ?? null, fn ($q, $v) => $q->where('financial_year_id', $v))
            ->when($filters['alp_id'] ?? null, fn ($q, $v) => $q->where('alp_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy('created_at')->orderBy('id');

        $runAlloc = Money::zero();
        $runCommitted = Money::zero();
        $runSpent = Money::zero();
        $rows = [];

        foreach ($query->get() as $txn) {
            $amt = Money::of((string) $txn->amount);
            $d = $txn->type->deltas($amt);
            $runAlloc = $runAlloc->plus($d['allocation']);
            $runCommitted = $runCommitted->plus($d['committed']);
            $runSpent = $runSpent->plus($d['spent']);

            $rows[] = [
                'date' => $txn->created_at,
                'reference' => $txn->reference_no,
                'alp' => $txn->alp?->ref_code,
                'type' => $txn->type,
                'amount' => $amt,
                'run_allocation' => $runAlloc,
                'run_committed' => $runCommitted,
                'run_spent' => $runSpent,
                'description' => $txn->description,
            ];
        }

        return ['rows' => $rows, 'count' => count($rows)];
    }

    /** @return array<int, Money> pending mengikut alp_id */
    private function pendingByAlp(int $financialYearId): array
    {
        return Application::query()
            ->where('financial_year_id', $financialYearId)
            ->whereIn('status', ApplicationStatus::pendingRequestValues())
            ->groupBy('alp_id')
            ->selectRaw('alp_id, SUM(requested_amount) as total')
            ->pluck('total', 'alp_id')
            ->map(fn ($t) => Money::of((string) $t))
            ->all();
    }

    private function pendingForAlp(int $alpId, int $financialYearId): Money
    {
        $sum = Application::query()
            ->where('financial_year_id', $financialYearId)
            ->where('alp_id', $alpId)
            ->whereIn('status', ApplicationStatus::pendingRequestValues())
            ->sum('requested_amount');

        return Money::of($sum === null ? '0' : (string) $sum);
    }

    /**
     * Pengecualian rekonsiliasi peringkat projek:
     * Approved ≠ Outstanding + Net Spent + Released, atau outstanding negatif.
     *
     * @return Collection<int, array>
     */
    public function reconciliationExceptions(int $financialYearId): Collection
    {
        return Project::query()
            ->where('financial_year_id', $financialYearId)
            ->with('alp:id,ref_code')
            ->get()
            ->map(function (Project $project) {
                $s = $this->projectFinancial->summary($project);
                $recomputed = $s['outstanding']->plus($s['spent'])->plus($s['released']);
                $balanced = $recomputed->equals($s['approved']) && ! $s['outstanding']->isNegative();

                return [
                    'project' => $project,
                    'summary' => $s,
                    'expected' => $s['approved'],
                    'recomputed' => $recomputed,
                    'balanced' => $balanced,
                ];
            })
            ->reject(fn ($r) => $r['balanced'])
            ->values();
    }
}
