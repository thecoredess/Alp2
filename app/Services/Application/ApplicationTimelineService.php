<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\ApprovalDecision;
use App\Enums\ReportCardStatus;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Models\Application;
use App\Support\UrsContributionPolicy;
use Carbon\Carbon;

/**
 * Garis masa proses URS v1.2 (UR-M02-003…005) hingga laporan aktiviti (M07).
 */
class ApplicationTimelineService
{
    public const KPI_DAYS = 14;

    public const ALP_VOUCHER_PAYMENT_HINT = 'Semakan bayaran boleh disemak melalui https://dbayar.dbkl.gov.my';

    public function __construct(
        private readonly ApplicationReportCardService $reportCards,
    ) {}

    /**
     * @return list<array{key: string, label: string, at: ?Carbon, done: bool, skipped: bool, hint: ?string, days_from_submit: ?int, status_label: ?string}>
     */
    public function stages(Application $application, bool $forAlpView = false): array
    {
        $application->loadMissing(['statusHistories', 'reviews', 'approvals.approvalLevel', 'reportCardReviews']);

        $submittedAt = $application->submitted_at ? Carbon::parse($application->submitted_at) : null;

        $jpAt = $application->reviews
            ->where('review_type', ReviewType::SECRETARIAT)
            ->where('decision', ReviewDecision::RECOMMEND)
            ->sortByDesc('created_at')
            ->first()?->created_at;

        $approvedRecords = $application->approvals
            ->where('decision', ApprovalDecision::APPROVED)
            ->sortBy('created_at')
            ->values();

        $rejection = $this->rejectionContext($application);
        $isRejected = $application->status === ApplicationStatus::REJECTED;

        $perakuAt = $approvedRecords->first()?->decided_at;
        $pepuAt = $approvedRecords->count() >= 2 ? $approvedRecords->last()?->decided_at : null;

        $voucherAt = $application->payment_status?->value === 'voucher_prepared'
            || $application->payment_status?->value === 'sent_to_jkew'
            || $application->payment_status?->value === 'paid'
            ? ($application->payment_updated_at ?? $application->paid_at ?? $application->sent_to_jkew_at)
            : null;

        $defs = [
            ['key' => 'submitted', 'label' => 'Permohonan dihantar', 'at' => $submittedAt, 'skipped' => false, 'hint' => null],
            ['key' => 'jp_review', 'label' => 'Semakan Jabatan', 'at' => $jpAt ? Carbon::parse($jpAt) : null, 'skipped' => false, 'hint' => null],
            ['key' => 'peraku', 'label' => 'Pengesyoran TP/Pengarah JP', 'at' => $perakuAt ? Carbon::parse($perakuAt) : null, 'skipped' => false, 'hint' => null],
            ['key' => 'pepu', 'label' => 'Kelulusan PEPU', 'at' => $pepuAt ? Carbon::parse($pepuAt) : null, 'skipped' => false, 'hint' => null],
            ['key' => 'voucher', 'label' => 'Baucar disedia', 'at' => $voucherAt ? Carbon::parse($voucherAt) : null, 'skipped' => false, 'hint' => null],
        ];

        $stages = array_map(function (array $row) use ($submittedAt) {
            $at = $row['at'];
            $days = ($submittedAt && $at) ? (int) $submittedAt->diffInDays($at) : null;

            return [
                'key' => $row['key'],
                'label' => $row['label'],
                'at' => $at,
                'done' => $at !== null,
                'skipped' => $row['skipped'],
                'hint' => $row['hint'],
                'days_from_submit' => $days,
                'status_label' => null,
            ];
        }, $defs);

        if ($forAlpView) {
            $stages = array_values(array_filter(
                $stages,
                fn (array $stage) => ! in_array($stage['key'], ['peraku', 'pepu'], true),
            ));

            $fullyApproved = $pepuAt !== null || $application->status === ApplicationStatus::APPROVED;

            $stages = array_map(function (array $stage) use ($jpAt, $voucherAt, $submittedAt, $fullyApproved, $isRejected) {
                if ($stage['key'] !== 'jp_review' || ! $jpAt) {
                    return $stage;
                }

                if ($isRejected) {
                    $at = Carbon::parse($jpAt);

                    return [
                        ...$stage,
                        'at' => $at,
                        'done' => true,
                        'status_label' => null,
                        'days_from_submit' => $submittedAt ? (int) $submittedAt->diffInDays($at) : null,
                    ];
                }

                if ($voucherAt) {
                    $at = Carbon::parse($voucherAt);

                    return [
                        ...$stage,
                        'at' => $at,
                        'done' => true,
                        'status_label' => null,
                        'days_from_submit' => $submittedAt ? (int) $submittedAt->diffInDays($at) : null,
                    ];
                }

                return [
                    ...$stage,
                    'at' => Carbon::parse($jpAt),
                    'done' => false,
                    'status_label' => $fullyApproved
                        ? 'Diluluskan — menunggu baucar'
                        : 'Dalam proses kelulusan',
                    'days_from_submit' => null,
                ];
            }, $stages);

        }

        $stages = $this->applyVoucherPaymentHint($stages, $application, $voucherAt, $isRejected);

        if ($isRejected && $rejection !== null) {
            $stages = $this->injectRejectedStage($stages, $rejection, $submittedAt);
        }

        $stages[] = $this->reportStage($application, $submittedAt, $voucherAt ? Carbon::parse($voucherAt) : null);

        return $stages;
    }

    /**
     * @param  list<array{key: string, label: string, at: ?Carbon, done: bool, skipped: bool, hint: ?string, days_from_submit: ?int, status_label: ?string}>  $stages
     * @return list<array{key: string, label: string, at: ?Carbon, done: bool, skipped: bool, hint: ?string, days_from_submit: ?int, status_label: ?string}>
     */
    private function injectRejectedStage(array $stages, object $rejection, ?Carbon $submittedAt): array
    {
        $rejectedAt = $rejection->decided_at ? Carbon::parse($rejection->decided_at) : null;
        $comments = trim((string) ($rejection->comments ?? ''));

        $rejectedStage = [
            'key' => 'rejected',
            'variant' => 'rejected',
            'label' => 'Permohonan ditolak',
            'at' => $rejectedAt,
            'done' => true,
            'skipped' => false,
            'hint' => $comments !== '' ? $comments : null,
            'days_from_submit' => ($submittedAt && $rejectedAt) ? (int) $submittedAt->diffInDays($rejectedAt) : null,
            'status_label' => 'Ditolak',
        ];

        $result = [];
        $inserted = false;

        foreach ($stages as $stage) {
            if (! $inserted && $stage['key'] === 'voucher') {
                $result[] = $rejectedStage;
                $inserted = true;
            }

            if ($stage['key'] === 'voucher') {
                $result[] = [
                    ...$stage,
                    'done' => false,
                    'skipped' => true,
                    'at' => null,
                    'hint' => null,
                    'days_from_submit' => null,
                    'status_label' => null,
                ];

                continue;
            }

            $result[] = $stage;
        }

        if (! $inserted) {
            $result[] = $rejectedStage;
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $stages
     * @return list<array<string, mixed>>
     */
    private function applyVoucherPaymentHint(array $stages, Application $application, mixed $voucherAt, bool $isRejected): array
    {
        return array_map(function (array $stage) use ($application, $voucherAt, $isRejected) {
            if ($stage['key'] !== 'voucher' || ! $voucherAt || $isRejected) {
                return $stage;
            }

            return [
                ...$stage,
                'hint' => self::ALP_VOUCHER_PAYMENT_HINT,
                'payment_supplier_no' => filled($application->payment_supplier_no)
                    ? $application->payment_supplier_no
                    : null,
            ];
        }, $stages);
    }

    private function rejectionContext(Application $application): ?object
    {
        if ($application->status !== ApplicationStatus::REJECTED) {
            return null;
        }

        $approval = $application->approvals
            ->where('decision', ApprovalDecision::REJECTED)
            ->sortByDesc('decided_at')
            ->first();

        if ($approval) {
            return $approval;
        }

        $history = $application->statusHistories
            ->where('to_status', ApplicationStatus::REJECTED)
            ->sortByDesc('created_at')
            ->first();

        return (object) [
            'decided_at' => $history?->created_at ?? $application->updated_at,
            'comments' => $history?->remarks ?? '',
        ];
    }

    /**
     * @return array{key: string, label: string, at: ?Carbon, done: bool, skipped: bool, hint: ?string, days_from_submit: ?int, status_label: ?string}
     */
    private function reportStage(Application $application, ?Carbon $submittedAt, ?Carbon $voucherAt): array
    {
        $needsReport = $application->status === ApplicationStatus::APPROVED;
        $status = $application->report_card_status;

        $approvedAt = $application->reportCardReviews
            ->where('stage', 'pegawai_jp')
            ->sortByDesc('reviewed_at')
            ->first()?->reviewed_at;

        $latestReturn = $application->reportCardReviews
            ->where('decision', ReviewDecision::RETURN_FOR_REVISION)
            ->sortByDesc('reviewed_at')
            ->first();

        $at = null;
        $done = false;
        $statusLabel = null;
        $hint = null;

        if ($status === ReportCardStatus::APPROVED && $approvedAt) {
            $at = Carbon::parse($approvedAt);
            $done = true;
            $hint = 'Disahkan Pegawai JP';
        } elseif ($needsReport && $voucherAt) {
            if ($status === ReportCardStatus::RETURNED) {
                $statusLabel = 'Dikembalikan';
                $hint = $latestReturn?->comments
                    ? $latestReturn->comments.' — sila muat naik semula'
                    : 'Sila muat naik semula';
            } elseif ($status === ReportCardStatus::DRAFT) {
                $statusLabel = 'Draf — belum dihantar';
                $hint = 'Semak fail dan hantar ke Admin JP';
            } elseif (in_array($status, [ReportCardStatus::AWAITING_ADMIN_JP, ReportCardStatus::AWAITING_PEGAWAI_JP], true)) {
                $statusLabel = 'Dalam semakan JP';
                if ($application->report_card_submitted_at) {
                    $hint = 'Dihantar '.Carbon::parse($application->report_card_submitted_at)->format('d/m/Y H:i');
                }
            } else {
                $statusLabel = 'Menunggu muat naik';
                $due = $this->reportCards->dueDate($application);
                if ($due) {
                    $hint = 'Tarikh akhir: '.$due->format('d/m/Y');
                    if ($this->reportCards->isOverdue($application)) {
                        $hint .= ' · Tertunggak';
                    }
                }
            }
        }

        $days = ($submittedAt && $at) ? (int) $submittedAt->diffInDays($at) : null;

        return [
            'key' => 'report',
            'label' => 'Laporan aktiviti',
            'at' => $at,
            'done' => $done,
            'skipped' => ! $needsReport,
            'hint' => $hint,
            'days_from_submit' => $days,
            'status_label' => $statusLabel,
        ];
    }

    /**
     * Hari dari submit hingga JKEW (atau kini jika belum dihantar).
     *
     * @return array{days: ?int, kpi: int, within_kpi: ?bool, completed: bool, label: string}
     */
    public function kpi(Application $application): array
    {
        $kpi = max(1, UrsContributionPolicy::overdueDays() ?: self::KPI_DAYS);
        $submittedAt = $application->submitted_at ? Carbon::parse($application->submitted_at) : null;

        if (! $submittedAt) {
            return [
                'days' => null,
                'kpi' => $kpi,
                'within_kpi' => null,
                'completed' => false,
                'label' => 'Belum dihantar',
            ];
        }

        $end = $application->sent_to_jkew_at
            ? Carbon::parse($application->sent_to_jkew_at)
            : now();
        $days = (int) $submittedAt->diffInDays($end);
        $completed = (bool) $application->sent_to_jkew_at;

        return [
            'days' => $days,
            'kpi' => $kpi,
            'within_kpi' => $days <= $kpi,
            'completed' => $completed,
            'label' => $completed
                ? sprintf('%d hari hingga JKEW (KPI %d)', $days, $kpi)
                : sprintf('%d hari dalam proses (KPI %d)', $days, $kpi),
        ];
    }
}
