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

    /**
     * @return list<array{key: string, label: string, at: ?Carbon, done: bool, days_from_submit: ?int}>
     */
    public function stages(Application $application): array
    {
        $application->loadMissing(['statusHistories', 'reviews', 'approvals.approvalLevel']);

        $submittedAt = $application->submitted_at ? Carbon::parse($application->submitted_at) : null;

        $jpAt = $application->reviews
            ->where('review_type', ReviewType::SECRETARIAT)
            ->where('decision', ReviewDecision::RECOMMEND)
            ->sortByDesc('created_at')
            ->first()?->created_at;

        $perakuAt = $application->approvals
            ->where('decision', ApprovalDecision::APPROVED)
            ->sortBy('created_at')
            ->first()?->decided_at;

        $pepuAt = null;
        $approvals = $application->approvals
            ->where('decision', ApprovalDecision::APPROVED)
            ->sortBy('created_at')
            ->values();
        if ($approvals->count() >= 2) {
            $pepuAt = $approvals->last()?->decided_at;
        } elseif ($application->status === ApplicationStatus::APPROVED && $approvals->count() === 1) {
            // Aras tunggal (≤ RM3k) — Peraku = keputusan akhir.
            $pepuAt = $perakuAt;
        }

        $voucherAt = $application->payment_status?->value === 'voucher_prepared'
            || $application->payment_status?->value === 'sent_to_jkew'
            || $application->payment_status?->value === 'paid'
            ? ($application->payment_updated_at ?? $application->paid_at)
            : null;

        $jkewAt = $application->sent_to_jkew_at;

        $defs = [
            ['key' => 'submitted', 'label' => 'Permohonan dihantar', 'at' => $submittedAt],
            ['key' => 'jp_review', 'label' => 'Semakan Pegawai JP', 'at' => $jpAt ? Carbon::parse($jpAt) : null],
            ['key' => 'peraku', 'label' => 'Peraku (TP/Pengarah JP)', 'at' => $perakuAt ? Carbon::parse($perakuAt) : null],
            ['key' => 'pepu', 'label' => 'Kelulusan PEPU / akhir', 'at' => $pepuAt ? Carbon::parse($pepuAt) : null],
            ['key' => 'voucher', 'label' => 'Baucar disedia', 'at' => $voucherAt ? Carbon::parse($voucherAt) : null],
            ['key' => 'jkew', 'label' => 'Dihantar ke JKEW', 'at' => $jkewAt ? Carbon::parse($jkewAt) : null],
        ];

        return array_map(function (array $row) use ($submittedAt) {
            $at = $row['at'];
            $days = ($submittedAt && $at) ? (int) $submittedAt->diffInDays($at) : null;

            return [
                'key' => $row['key'],
                'label' => $row['label'],
                'at' => $at,
                'done' => $at !== null,
                'days_from_submit' => $days,
            ];
        }, $defs);
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
