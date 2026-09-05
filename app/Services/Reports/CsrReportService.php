<?php

namespace App\Services\Reports;

use App\Enums\ApplicationType;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\Project\ProjectFinancialService;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Pelaporan impak CSR. Hanya projek jenis CSR. Beneficiary count dari laporan
 * akhir; nilai null dikendalikan dengan selamat (tidak dikira, bukan sifar palsu).
 */
class CsrReportService
{
    public function __construct(private readonly ProjectFinancialService $financial) {}

    public function summary(array $filters): array
    {
        $projects = $this->base($filters)->with('report:id,project_id,beneficiary_count')->get();

        $approved = Money::zero();
        $net = Money::zero();
        foreach ($projects as $p) {
            $s = $this->financial->summary($p);
            $approved = $approved->plus($s['approved']);
            $net = $net->plus($s['spent']);
        }

        // Beneficiary: hanya jumlahkan nilai bukan-null; jejak berapa laporan ada data.
        $withBeneficiary = $projects->filter(fn ($p) => $p->report && $p->report->beneficiary_count !== null);

        return [
            'total' => $projects->count(),
            'approved' => $approved,
            'net' => $net,
            'completed' => $projects->where('status', ProjectStatus::COMPLETED)->count(),
            'closed' => $projects->where('status', ProjectStatus::CLOSED)->count(),
            'beneficiary_total' => (int) $withBeneficiary->sum(fn ($p) => (int) $p->report->beneficiary_count),
            'beneficiary_reported' => $withBeneficiary->count(),
            'beneficiary_missing' => $projects->count() - $withBeneficiary->count(),
        ];
    }

    /** Pecahan CSR mengikut ALP. */
    public function byAlp(array $filters): Collection
    {
        return $this->base($filters)->with(['alp:id,ref_code,name', 'report:id,project_id,beneficiary_count'])->get()
            ->groupBy('alp_id')
            ->map(function ($rows) {
                $net = Money::zero();
                foreach ($rows as $p) {
                    $net = $net->plus($this->financial->summary($p)['spent']);
                }

                return [
                    'alp' => $rows->first()->alp,
                    'count' => $rows->count(),
                    'net' => $net,
                    'beneficiaries' => (int) $rows->filter(fn ($p) => $p->report?->beneficiary_count !== null)->sum(fn ($p) => (int) $p->report->beneficiary_count),
                ];
            })->values();
    }

    /** Pecahan CSR mengikut kawasan (location). */
    public function byArea(array $filters): Collection
    {
        return $this->base($filters)->with('application:id,location')->get()
            ->groupBy(fn ($p) => $p->application?->location ?: 'Tidak dinyatakan')
            ->map(fn ($rows, $area) => ['area' => $area, 'count' => $rows->count()])
            ->values();
    }

    /** Senarai projek CSR + kewangan. */
    public function listing(array $filters): Collection
    {
        return $this->base($filters)
            ->with(['alp:id,ref_code,name', 'application:id,location,target_group', 'report:id,project_id,beneficiary_count'])
            ->orderByDesc('created_at')->get()
            ->map(fn (Project $p) => ['project' => $p, 'finance' => $this->financial->summary($p)]);
    }

    private function base(array $filters)
    {
        return Project::query()
            ->where('project_type', ApplicationType::CSR->value)
            ->when($filters['financial_year_id'] ?? null, fn ($q, $v) => $q->where('financial_year_id', $v))
            ->when($filters['alp_id'] ?? null, fn ($q, $v) => $q->where('alp_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v));
    }
}
