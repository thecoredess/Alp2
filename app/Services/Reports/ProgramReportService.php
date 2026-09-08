<?php

namespace App\Services\Reports;

use App\Enums\ApplicationStatus;
use App\Enums\ReportCardStatus;
use App\Models\Application;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Laporan program (URS M07 — BR-018/019).
 *
 * Satu "program" ialah permohonan sumbangan yang telah DILULUSKAN. Laporan
 * aktiviti wajib dikemukakan satu bulan selepas baucar disedia.
 *
 * "Laporan diterima" hanya selepas Pegawai JP mengesahkan (report_card_status = approved).
 */
class ProgramReportService
{
    public const STATUS_RECEIVED = 'diterima';

    public const STATUS_IN_REVIEW = 'dalam_semakan';

    public const STATUS_RETURNED = 'dikembalikan';

    public const STATUS_AWAITING_VOUCHER = 'menunggu_baucar';

    public const STATUS_PENDING = 'menunggu';

    public const STATUS_OVERDUE = 'tertunggak';

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_RECEIVED => 'Laporan diterima',
            self::STATUS_IN_REVIEW => 'Dalam semakan JP',
            self::STATUS_RETURNED => 'Dikembalikan untuk pembetulan',
            self::STATUS_AWAITING_VOUCHER => 'Menunggu baucar',
            self::STATUS_PENDING => 'Menunggu laporan',
            self::STATUS_OVERDUE => 'Tertunggak',
        ];
    }

    /**
     * Senarai program berserta status laporan aktiviti.
     *
     * @return Collection<int, array{application: Application, due_at: ?Carbon, has_report: bool, status: string}>
     */
    public function listing(array $filters): Collection
    {
        $applications = $this->base($filters)
            ->with(['alp:id,ref_code,name', 'financialYear:id,year'])
            ->orderBy('program_date')
            ->get();

        if ($applications->isEmpty()) {
            return collect();
        }

        $rows = $applications->map(function (Application $application) {
            $due = $this->dueDateFor($application);
            $status = $this->resolveStatus($application, $due);

            return [
                'application' => $application,
                'due_at' => $due,
                'has_report' => $status === self::STATUS_RECEIVED,
                'status' => $status,
            ];
        });

        if ($status = ($filters['report_status'] ?? null)) {
            $rows = $rows->where('status', $status);
        }

        return $rows->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    public function summary(Collection $rows): array
    {
        $countBy = fn (string $status): int => $rows->where('status', $status)->count();

        $total = $rows->count();
        $received = $countBy(self::STATUS_RECEIVED);

        return [
            'total' => $total,
            'amount' => $rows->reduce(
                fn (Money $carry, array $row) => $carry->plus($row['application']->requestedAmountMoney()),
                Money::zero(),
            ),
            'received' => $received,
            'in_review' => $countBy(self::STATUS_IN_REVIEW),
            'returned' => $countBy(self::STATUS_RETURNED),
            'awaiting_voucher' => $countBy(self::STATUS_AWAITING_VOUCHER),
            'pending' => $countBy(self::STATUS_PENDING),
            'overdue' => $countBy(self::STATUS_OVERDUE),
            'compliance' => $total > 0 ? round(($received / $total) * 100, 1) : 0.0,
        ];
    }

    /**
     * Pecahan mengikut kategori program (BR-011).
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return list<array{label: string, count: int, amount: Money}>
     */
    public function byCategory(Collection $rows): array
    {
        return collect(\App\Enums\ProgramCategory::cases())
            ->map(function (\App\Enums\ProgramCategory $category) use ($rows) {
                $matching = $rows->filter(
                    fn (array $row) => $row['application']->program_category === $category,
                );

                return [
                    'label' => $category->label(),
                    'count' => $matching->count(),
                    'amount' => $matching->reduce(
                        fn (Money $carry, array $row) => $carry->plus($row['application']->requestedAmountMoney()),
                        Money::zero(),
                    ),
                ];
            })
            ->all();
    }

    private function resolveStatus(Application $application, ?Carbon $due): string
    {
        $rcStatus = $application->report_card_status;

        if ($rcStatus === ReportCardStatus::APPROVED) {
            return self::STATUS_RECEIVED;
        }

        if (in_array($rcStatus, [ReportCardStatus::AWAITING_ADMIN_JP, ReportCardStatus::AWAITING_PEGAWAI_JP], true)) {
            return self::STATUS_IN_REVIEW;
        }

        if ($rcStatus === ReportCardStatus::RETURNED) {
            return self::STATUS_RETURNED;
        }

        if (! $application->hasVoucherPrepared()) {
            return self::STATUS_AWAITING_VOUCHER;
        }

        return $due !== null && now()->greaterThan($due)
            ? self::STATUS_OVERDUE
            : self::STATUS_PENDING;
    }

    private function dueDateFor(Application $application): ?Carbon
    {
        if (! $application->hasVoucherPrepared()) {
            return null;
        }

        $start = $application->payment_voucher_date
            ?? $application->payment_updated_at
            ?? $application->updated_at;

        return $start?->copy()->startOfDay()->addMonthNoOverflow()->endOfDay();
    }

    private function base(array $filters)
    {
        return Application::query()
            ->where('status', ApplicationStatus::APPROVED->value)
            ->when($filters['financial_year_id'] ?? null, fn ($q, $v) => $q->where('financial_year_id', $v))
            ->when($filters['alp_id'] ?? null, fn ($q, $v) => $q->where('alp_id', $v))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('program_category', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('program_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('program_date', '<=', $v));
    }
}
