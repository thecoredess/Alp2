<?php

namespace App\Services\Application;

use App\Enums\ApplicationStatus;
use App\Enums\ApprovalDecision;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Models\Application;
use App\Support\UrsContributionPolicy;
use Carbon\Carbon;

/**
 * Garis masa proses URS v1.2 (UR-M02-003…005) hingga hantar ke JKEW.
 */
class ApplicationTimelineService
{
    public const KPI_DAYS = 14;

    public const ALP_VOUCHER_PAYMENT_HINT = 'Semakan bayaran boleh disemak melalui https://dbayar.dbkl.gov.my';

    /**
     * @return list<array{key: string, label: string, at: ?Carbon, done: bool, skipped: bool, hint: ?string, days_from_submit: ?int, status_label: ?string}>
     */
    public function stages(Application $application, bool $forAlpView = false): array
    {
        $application->loadMissing(['statusHistories', 'reviews', 'approvals.approvalLevel']);

        $submittedAt = $application->submitted_at ? Carbon::parse($application->submitted_at) : null;

        $jpAt = $application->reviews
            ->where('review_type', ReviewType::SECRETARIAT)
            ->where('decision', ReviewDecision::RECOMMEND)
            ->sortByDesc('created_at')
            ->first()?->created_at;

        $approvals = $application->approvals
            ->where('decision', ApprovalDecision::APPROVED)
            ->sortBy('created_at')
            ->values();

        $perakuAt = $approvals->first()?->decided_at;
        $pepuAt = $approvals->count() >= 2 ? $approvals->last()?->decided_at : null;

        $voucherAt = $application->payment_status?->value === 'voucher_prepared'
            || $application->payment_status?->value === 'sent_to_jkew'
            || $application->payment_status?->value === 'paid'
            ? ($application->payment_updated_at ?? $application->paid_at ?? $application->sent_to_jkew_at)
            : null;

        $defs = [
            ['key' => 'submitted', 'label' => 'Permohonan dihantar', 'at' => $submittedAt, 'skipped' => false, 'hint' => null],
            ['key' => 'jp_review', 'label' => 'Semakan Pegawai JP', 'at' => $jpAt ? Carbon::parse($jpAt) : null, 'skipped' => false, 'hint' => null],
            ['key' => 'peraku', 'label' => 'Pengesyoran PEPU', 'at' => $perakuAt ? Carbon::parse($perakuAt) : null, 'skipped' => false, 'hint' => null],
            ['key' => 'pepu', 'label' => 'Kelulusan PEPU / Pengurusan', 'at' => $pepuAt ? Carbon::parse($pepuAt) : null, 'skipped' => false, 'hint' => null],
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

            $stages = array_map(function (array $stage) use ($jpAt, $voucherAt, $submittedAt, $fullyApproved) {
                if ($stage['key'] !== 'jp_review' || ! $jpAt) {
                    return $stage;
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

            $stages = array_map(function (array $stage) use ($voucherAt) {
                if ($stage['key'] === 'voucher' && $voucherAt) {
                    return [
                        ...$stage,
                        'hint' => self::ALP_VOUCHER_PAYMENT_HINT,
                    ];
                }

                return $stage;
            }, $stages);
        }

        return $stages;
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
