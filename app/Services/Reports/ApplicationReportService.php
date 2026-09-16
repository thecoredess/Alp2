<?php

namespace App\Services\Reports;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ApprovalDecision;
use App\Enums\ReportCardStatus;
use App\Models\Application;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Pelaporan permohonan. Kiraan status & amaun dari jadual applications.
 * Pending Request (menunggu) TIDAK sama dengan Committed — jangan dicampur.
 */
class ApplicationReportService
{
    public const FILTER_RECEIVED = 'diterima';

    public const FILTER_IN_REVIEW = 'dalam_semakan';

    public const FILTER_RETURNED = 'dikembalikan';

    public const FILTER_AWAITING_VOUCHER = 'menunggu_baucar';

    public const FILTER_PENDING = 'menunggu';

    /** Selepas TP/Pengarah JP (Peraku) lulus — menunggu kelulusan PEPU. */
    public const FILTER_RECOMMENDED = 'disyorkan';

    public const FILTER_APPROVED = 'diluluskan';

    public const FILTER_REJECTED = 'ditolak';

    /** @return array<string, string> */
    public static function statusFilterOptions(): array
    {
        return [
            self::FILTER_RECEIVED => 'Laporan aktiviti diterima',
            self::FILTER_IN_REVIEW => 'Dalam semakan JP',
            self::FILTER_RETURNED => 'Dikembalikan untuk pembetulan',
            self::FILTER_AWAITING_VOUCHER => 'Menunggu baucar',
            self::FILTER_PENDING => 'Menunggu laporan',
            self::FILTER_RECOMMENDED => 'Disyorkan',
            self::FILTER_APPROVED => 'Diluluskan',
            self::FILTER_REJECTED => 'Ditolak',
        ];
    }

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
            ->when($filters['status'] ?? null, fn ($q, $v) => $this->applyStatusFilter($q, $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }

    private function applyStatusFilter(Builder $query, string $status): Builder
    {
        return match ($status) {
            self::FILTER_RECEIVED => $query
                ->where('status', ApplicationStatus::APPROVED->value)
                ->where('report_card_status', ReportCardStatus::APPROVED->value),
            self::FILTER_IN_REVIEW => $query
                ->where('status', ApplicationStatus::APPROVED->value)
                ->where('report_card_status', ReportCardStatus::AWAITING_ADMIN_JP->value),
            self::FILTER_RETURNED => $query
                ->where('status', ApplicationStatus::APPROVED->value)
                ->where('report_card_status', ReportCardStatus::RETURNED->value),
            self::FILTER_AWAITING_VOUCHER => $this->applyVoucherNotPrepared(
                $query->where('status', ApplicationStatus::APPROVED->value),
            ),
            self::FILTER_PENDING => $this->applyVoucherPrepared(
                $query->where('status', ApplicationStatus::APPROVED->value),
            )
                ->whereNull('report_card_submitted_at')
                ->where(function (Builder $q) {
                    $q->whereNull('report_card_status')
                        ->orWhereNotIn('report_card_status', [
                            ReportCardStatus::APPROVED->value,
                            ReportCardStatus::AWAITING_ADMIN_JP->value,
                            ReportCardStatus::AWAITING_PEGAWAI_JP->value,
                            ReportCardStatus::RETURNED->value,
                        ]);
                }),
            self::FILTER_RECOMMENDED => $query
                ->where('status', ApplicationStatus::PENDING_APPROVAL->value)
                ->whereHas('approvals', function (Builder $q) {
                    $q->where('decision', ApprovalDecision::APPROVED->value)
                        ->whereColumn('revision_number', 'applications.revision_number');
                }),
            self::FILTER_APPROVED => $query->where('status', ApplicationStatus::APPROVED->value),
            self::FILTER_REJECTED => $query->where('status', ApplicationStatus::REJECTED->value),
            default => $query,
        };
    }

    private function applyVoucherPrepared(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNotNull('payment_voucher_date')
                ->orWhereNotNull('payment_voucher_no')
                ->orWhereIn('payment_status', [
                    ApplicationPaymentStatus::VOUCHER_PREPARED->value,
                    ApplicationPaymentStatus::SENT_TO_JKEW->value,
                    ApplicationPaymentStatus::PAID->value,
                ]);
        });
    }

    private function applyVoucherNotPrepared(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('payment_voucher_date')
                ->whereNull('payment_voucher_no')
                ->where(function (Builder $inner) {
                    $inner->whereNull('payment_status')
                        ->orWhereNotIn('payment_status', [
                            ApplicationPaymentStatus::VOUCHER_PREPARED->value,
                            ApplicationPaymentStatus::SENT_TO_JKEW->value,
                            ApplicationPaymentStatus::PAID->value,
                        ]);
                });
        });
    }
}
