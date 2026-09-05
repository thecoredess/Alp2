<?php

namespace App\Services\Reports;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Models\Application;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Pelaporan permohonan. Kiraan status & amaun dari jadual applications.
 * Pending Request (menunggu) TIDAK sama dengan Committed — jangan dicampur.
 */
class ApplicationReportService
{
    /** Kiraan mengikut status bagi tapisan diberi. */
    public function statusCounts(array $filters): array
    {
        $rows = $this->base($filters)
            ->groupBy('status')->selectRaw('status, COUNT(*) as c')->pluck('c', 'status');

        $get = fn (ApplicationStatus $s) => (int) ($rows[$s->value] ?? 0);

        return [
            'total' => (int) $rows->sum(),
            'draft' => $get(ApplicationStatus::DRAFT),
            'under_review' => $get(ApplicationStatus::SUBMITTED) + $get(ApplicationStatus::UNDER_SECRETARIAT_REVIEW)
                + $get(ApplicationStatus::UNDER_FINANCE_REVIEW) + $get(ApplicationStatus::UNDER_TECHNICAL_REVIEW),
            'revision' => $get(ApplicationStatus::REVISION_REQUIRED),
            'pending_approval' => $get(ApplicationStatus::PENDING_APPROVAL),
            'approved' => $get(ApplicationStatus::APPROVED),
            'rejected' => $get(ApplicationStatus::REJECTED),
        ];
    }

    /** Amaun agregat (tepat). */
    public function amounts(array $filters): array
    {
        $sumFor = function (array $statuses) use ($filters) {
            $s = $this->base($filters)->when($statuses, fn ($q) => $q->whereIn('status', $statuses))->sum('requested_amount');

            return Money::of($s === null ? '0' : (string) $s);
        };

        return [
            'total_requested' => $sumFor([]),
            'pending_request' => $sumFor(ApplicationStatus::pendingRequestValues()),
            'approved_amount' => $sumFor([ApplicationStatus::APPROVED->value]),
            'rejected_amount' => $sumFor([ApplicationStatus::REJECTED->value]),
        ];
    }

    /** Corong permohonan (kiraan setiap peringkat). */
    public function pipeline(array $filters): array
    {
        $counts = $this->statusCounts($filters);
        $approvedWithProject = $this->base($filters)
            ->where('status', ApplicationStatus::APPROVED->value)
            ->whereHas('project')->count();

        return [
            'draft' => $counts['draft'],
            'review' => $counts['under_review'],
            'pending_approval' => $counts['pending_approval'],
            'approved' => $counts['approved'],
            'project' => $approvedWithProject,
        ];
    }

    /** Pecahan mengikut jenis (CSR/Pembangunan). */
    public function byType(array $filters): array
    {
        $rows = $this->base($filters)->groupBy('application_type')
            ->selectRaw('application_type, COUNT(*) as c, SUM(requested_amount) as t')->get();

        return $rows->mapWithKeys(fn ($r) => [
            ($r->application_type instanceof ApplicationType ? $r->application_type->value : $r->application_type) => [
                'count' => (int) $r->c, 'amount' => Money::of((string) ($r->t ?? '0')),
            ],
        ])->all();
    }

    /** Senarai permohonan (untuk jadual laporan & eksport). */
    public function listing(array $filters): Collection
    {
        return $this->base($filters)
            ->with(['alp:id,ref_code,name', 'financialYear:id,year'])
            ->when($filters['amount_min'] ?? null, fn ($q, $v) => $q->where('requested_amount', '>=', $v))
            ->when($filters['amount_max'] ?? null, fn ($q, $v) => $q->where('requested_amount', '<=', $v))
            ->orderByDesc('created_at')
            ->get();
    }

    private function base(array $filters)
    {
        return Application::query()
            ->when($filters['financial_year_id'] ?? null, fn ($q, $v) => $q->where('financial_year_id', $v))
            ->when($filters['alp_id'] ?? null, fn ($q, $v) => $q->where('alp_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('application_type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }
}
