<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Enums\BudgetTransactionType;
use App\Enums\ProjectExpenseStatus;
use App\Enums\RefundStatus;
use App\Models\Allocation;
use App\Models\BudgetTransaction;
use App\Models\FinancialYear;
use App\Models\Project;
use App\Services\Reports\DataQualityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Semakan integriti kewangan menyeluruh (kawalan dalaman). LAPOR SAHAJA — tidak
 * membaiki apa-apa. Menggabungkan semakan hala-depan (sumber → ledger) daripada
 * DataQualityService dengan semakan hala-balik (ledger → sumber) & identiti maker-checker.
 *
 * Kod keluar 0 jika 0 pengecualian; 1 jika ada pengecualian (sesuai untuk CI/cron).
 */
class IntegrityCheck extends Command
{
    protected $signature = 'integrity:check';

    protected $description = 'Semak integriti kewangan (rekonsiliasi, ledger vs sumber, maker-checker). Lapor sahaja.';

    public function handle(DataQualityService $dataQuality): int
    {
        $rows = [];

        // ── Hala-depan (sumber → ledger), setiap tahun kewangan, dari DataQualityService.
        foreach (FinancialYear::all() as $year) {
            foreach ($dataQuality->run($year->id) as $check) {
                $count = $check['items']->count();
                if ($count > 0) {
                    $rows[] = [$check['label']." (TK {$year->year})", $count];
                }
            }
        }

        // ── Hala-balik (ledger → sumber) — global.
        $commitmentNoApproved = BudgetTransaction::where('type', BudgetTransactionType::COMMITMENT->value)
            ->where(function ($q) {
                $q->whereNull('application_id')
                    ->orWhereDoesntHave('application', fn ($a) => $a->where('status', ApplicationStatus::APPROVED->value));
            })->count();
        $this->tally($rows, 'COMMITMENT tanpa permohonan DILULUSKAN', $commitmentNoApproved);

        $expenditureNoVerified = BudgetTransaction::where('type', BudgetTransactionType::EXPENDITURE->value)
            ->whereNotIn('project_expense_id', fn ($q) => $q->select('id')->from('project_expenses')->where('status', ProjectExpenseStatus::VERIFIED->value))
            ->count();
        $this->tally($rows, 'EXPENDITURE tanpa perbelanjaan DISAHKAN', $expenditureNoVerified);

        $refundNoVerified = BudgetTransaction::where('type', BudgetTransactionType::REFUND->value)
            ->whereNotIn('project_expense_refund_id', fn ($q) => $q->select('id')->from('project_expense_refunds')->where('status', RefundStatus::VERIFIED->value))
            ->count();
        $this->tally($rows, 'REFUND tanpa refund DISAHKAN', $refundNoVerified);

        // ── Duplikasi (kekangan unik sepatutnya menghalang; sahkan).
        $dupProjects = Project::select('application_id')->groupBy('application_id')->havingRaw('COUNT(*) > 1')->get()->count();
        $this->tally($rows, 'Projek duplikat bagi satu permohonan', $dupProjects);

        $dupAllocations = Allocation::select('alp_id', 'financial_year_id')->groupBy('alp_id', 'financial_year_id')->havingRaw('COUNT(*) > 1')->get()->count();
        $this->tally($rows, 'Peruntukan awal duplikat (ALP×tahun)', $dupAllocations);

        // ── Pelanggaran identiti Maker == Checker.
        $brBad = DB::table('budget_requests')->whereNotNull('approved_by')
            ->where(fn ($q) => $q->whereColumn('approved_by', 'created_by')->orWhereColumn('approved_by', 'submitted_by'))->count();
        $this->tally($rows, 'Cadangan bajet: maker == checker', $brBad);

        $expBad = DB::table('project_expenses')->whereNotNull('verified_by')
            ->where(fn ($q) => $q->whereColumn('verified_by', 'created_by')->orWhereColumn('verified_by', 'submitted_by'))->count();
        $this->tally($rows, 'Perbelanjaan: maker == checker', $expBad);

        $refBad = DB::table('project_expense_refunds')->whereNotNull('verified_by')
            ->where(fn ($q) => $q->whereColumn('verified_by', 'created_by')->orWhereColumn('verified_by', 'submitted_by'))->count();
        $this->tally($rows, 'Refund: maker == checker', $refBad);

        // ── Laporan.
        $total = array_sum(array_column($rows, 1));
        if ($rows) {
            $this->table(['Pengecualian', 'Bilangan'], $rows);
        }
        $this->newLine();
        $this->line("Financial Integrity Exceptions = {$total}");

        if ($total === 0) {
            $this->info('✓ Integriti kewangan disahkan — tiada pengecualian.');

            return self::SUCCESS;
        }

        $this->error('✗ Pengecualian integriti dikesan. Siasat (LAPOR SAHAJA — tiada pembaikan automatik).');

        return self::FAILURE;
    }

    private function tally(array &$rows, string $label, int $count): void
    {
        if ($count > 0) {
            $rows[] = [$label, $count];
        }
    }
}
