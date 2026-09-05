<?php

namespace App\Services\Reports;

use App\Enums\ApplicationType;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\Project\ProjectFinancialService;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Pelaporan projek. Nilai kewangan projek dikira dari ledger melalui
 * ProjectFinancialService (bukan dari rekod perbelanjaan sahaja).
 */
class ProjectReportService
{
    public function __construct(private readonly ProjectFinancialService $financial) {}

    /** Kiraan status projek. */
    public function statusCounts(array $filters): array
    {
        $rows = $this->base($filters)->groupBy('status')->selectRaw('status, COUNT(*) as c')->pluck('c', 'status');
        $get = fn (ProjectStatus $s) => (int) ($rows[$s->value] ?? 0);

        return [
            'total' => (int) $rows->sum(),
            'not_started' => $get(ProjectStatus::NOT_STARTED),
            'in_progress' => $get(ProjectStatus::IN_PROGRESS),
            'delayed' => $get(ProjectStatus::DELAYED),
            'completed' => $get(ProjectStatus::COMPLETED),
            'closed' => $get(ProjectStatus::CLOSED),
            'cancelled' => $get(ProjectStatus::CANCELLED),
        ];
    }

    /** Ringkasan kewangan agregat semua projek dalam skop (dari ledger). */
    public function financialTotals(array $filters): array
    {
        $approved = Money::zero();
        $gross = Money::zero();
        $refunded = Money::zero();
        $net = Money::zero();
        $outstanding = Money::zero();
        $released = Money::zero();

        foreach ($this->listing($filters) as $row) {
            $s = $row['finance'];
            $approved = $approved->plus($s['approved']);
            $gross = $gross->plus($s['gross']);
            $refunded = $refunded->plus($s['refunded']);
            $net = $net->plus($s['spent']);
            $outstanding = $outstanding->plus($s['outstanding']);
            $released = $released->plus($s['released']);
        }

        return compact('approved', 'gross', 'refunded', 'net', 'outstanding', 'released');
    }

    /**
     * Senarai projek + ringkasan kewangan setiap satu.
     *
     * @return Collection<int, array{project: Project, finance: array}>
     */
    public function listing(array $filters): Collection
    {
        return $this->base($filters)
            ->with(['alp:id,ref_code,name', 'financialYear:id,year', 'report:id,project_id,beneficiary_count'])
            ->orderByDesc('created_at')->get()
            ->map(fn (Project $p) => ['project' => $p, 'finance' => $this->financial->summary($p)]);
    }

    /**
     * Senarai projek yang PERLU PERHATIAN, dikira dari keadaan sistem sebenar.
     *
     * @return Collection<int, array{project: Project, reasons: array<int,string>}>
     */
    public function attentionList(array $filters): Collection
    {
        $today = now()->startOfDay();

        return $this->base($filters)
            ->with(['alp:id,ref_code', 'report:id,project_id,submitted_at'])
            ->withCount([
                'expenses as pending_expenses_count' => fn ($q) => $q->where('status', \App\Enums\ProjectExpenseStatus::PENDING_VERIFICATION->value),
            ])
            ->get()
            ->map(function (Project $p) use ($today) {
                $reasons = [];
                if ($p->status === ProjectStatus::DELAYED) {
                    $reasons[] = 'Projek lewat';
                }
                if ($p->end_date && $p->end_date->lt($today) && ! in_array($p->status, [ProjectStatus::COMPLETED, ProjectStatus::CLOSED, ProjectStatus::CANCELLED], true)) {
                    $reasons[] = 'Tarikh tamat berlalu, belum selesai';
                }
                if ($p->status === ProjectStatus::COMPLETED) {
                    $reasons[] = 'Selesai tetapi belum ditutup';
                }
                if ((int) $p->pending_expenses_count > 0) {
                    $reasons[] = 'Perbelanjaan menunggu pengesahan';
                }
                if ($this->pendingRefundCount($p) > 0) {
                    $reasons[] = 'Refund menunggu pengesahan';
                }
                if ($p->status->isActive() && $p->progress_percent < 25 && $p->end_date && $p->end_date->diffInDays($today, false) > -30) {
                    $reasons[] = 'Kemajuan rendah menghampiri tarikh tamat';
                }

                return ['project' => $p, 'reasons' => $reasons];
            })
            ->reject(fn ($r) => empty($r['reasons']))
            ->values();
    }

    private function pendingRefundCount(Project $p): int
    {
        return \App\Models\ProjectExpenseRefund::where('project_id', $p->id)
            ->where('status', \App\Enums\RefundStatus::PENDING_VERIFICATION->value)->count();
    }

    private function base(array $filters)
    {
        return Project::query()
            ->when($filters['financial_year_id'] ?? null, fn ($q, $v) => $q->where('financial_year_id', $v))
            ->when($filters['alp_id'] ?? null, fn ($q, $v) => $q->where('alp_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('project_type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v));
    }
}
