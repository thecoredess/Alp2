<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Services\Budget\BudgetService;
use App\Support\Money;

/**
 * Pengiraan berkaitan bajet permohonan yang berasingan daripada ledger.
 *
 * PENTING: "Pending Request" BUKAN "Committed". Ia hanya jumlah permohonan
 * aktif yang belum diluluskan/ditolak/dibatalkan, dan TIDAK disimpan dalam
 * budget_transactions (ledger tidak dicemari).
 *
 *   Ledger Available   = Allocation − Committed − Spent  (dari ledger)
 *   Pending Request    = Σ requested_amount permohonan berstatus pending
 *   Projected Available = Ledger Available − Pending Request
 */
class ApplicationBudgetService
{
    public function __construct(private readonly BudgetService $budget) {}

    /** Jumlah Pending Request untuk ALP dalam satu tahun kewangan. */
    public function pendingRequest(int $alpId, int $financialYearId, ?int $excludeApplicationId = null): Money
    {
        $sum = Application::query()
            ->where('alp_id', $alpId)
            ->where('financial_year_id', $financialYearId)
            ->whereIn('status', ApplicationStatus::pendingRequestValues())
            ->when($excludeApplicationId, fn ($q) => $q->where('id', '!=', $excludeApplicationId))
            ->sum('requested_amount');

        return Money::of($sum === null ? '0' : (string) $sum);
    }

    /** Ledger Available = Allocation − Committed − Spent. */
    public function ledgerAvailable(int $alpId, int $financialYearId): Money
    {
        return $this->budget->summaryFor($alpId, $financialYearId)->available();
    }

    /** Projected Available = Ledger Available − Pending Request. */
    public function projectedAvailable(int $alpId, int $financialYearId): Money
    {
        return $this->ledgerAvailable($alpId, $financialYearId)
            ->minus($this->pendingRequest($alpId, $financialYearId));
    }

    /**
     * Permohonan diluluskan bagi ALP dalam tahun kewangan.
     *
     * @return \Illuminate\Support\Collection<int, Application>
     */
    public function approvedApplications(int $alpId, int $financialYearId): \Illuminate\Support\Collection
    {
        return Application::query()
            ->where('alp_id', $alpId)
            ->where('financial_year_id', $financialYearId)
            ->where('status', ApplicationStatus::APPROVED->value)
            ->orderByDesc('updated_at')
            ->get(['id', 'application_number', 'recipient_name', 'requested_amount', 'updated_at', 'status']);
    }

    /**
     * Permohonan pending (belum muktamad) bagi ALP — boleh kecualikan satu permohonan.
     *
     * @return \Illuminate\Support\Collection<int, Application>
     */
    public function pendingApplications(int $alpId, int $financialYearId, ?int $excludeApplicationId = null): \Illuminate\Support\Collection
    {
        return Application::query()
            ->where('alp_id', $alpId)
            ->where('financial_year_id', $financialYearId)
            ->whereIn('status', ApplicationStatus::pendingRequestValues())
            ->when($excludeApplicationId, fn ($q) => $q->where('id', '!=', $excludeApplicationId))
            ->orderByDesc('submitted_at')
            ->get(['id', 'application_number', 'recipient_name', 'requested_amount', 'submitted_at', 'status']);
    }

    /**
     * Jumlah yang tersedia untuk permohonan pending BAHARU
     * = Ledger Available − Pending Request LAIN (tidak termasuk permohonan ini).
     */
    public function availableForNewRequest(int $alpId, int $financialYearId, int $excludeApplicationId): Money
    {
        return $this->ledgerAvailable($alpId, $financialYearId)
            ->minus($this->pendingRequest($alpId, $financialYearId, $excludeApplicationId));
    }
}
