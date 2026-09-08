<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Models\Alp;
use App\Models\Application;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Lapisan polisi URS sumbangan ALP — selaras URS v1.2.
 *
 * Lalai DIAKTIFKAN (wajib untuk pematuhan URS v1.2).
 *
 * Pemetaan ID v1.2:
 * - BR-001: maks RM30,000 / ALP / tahun
 * - BR-002: 3 tempoh × kuota (lalai RM10,000) setiap 4 bulan
 * - BR-003: baki tempoh luput — tiada bawa ke hadapan
 * - BR-005: maks RM3,000 setiap permohonan
 * - BR-007: kelayakan ikut tarikh lantikan (bilangan tempoh 4-bulan × kuota tempoh)
 * - BR-010 / BR-014 / BR-015: alamat KL; lead time 2 bulan
 */
final class UrsContributionPolicy
{
    public const KEY_ENABLED = 'urs_policy_enabled';

    public const KEY_MAX_ANNUAL = 'urs_max_annual_allocation';

    public const KEY_MAX_PER_APPLICATION = 'urs_max_per_application';

    public const KEY_PERIOD_QUOTA = 'urs_period_quota';

    public const KEY_OVERDUE_DAYS = 'urs_overdue_days';

    /** Lead time minimum (bulan) sebelum tarikh program — BR-014. */
    public const PROGRAM_LEAD_MONTHS = 2;

    public static function enabled(): bool
    {
        // URS v1.2: lalai ON. Hanya ujian eksplisit boleh set false.
        return SystemSetting::getBool(self::KEY_ENABLED, true);
    }

    public static function maxAnnualAllocation(): Money
    {
        return Money::of((string) SystemSetting::get(self::KEY_MAX_ANNUAL, '30000.00'));
    }

    public static function maxPerApplication(): Money
    {
        return Money::of((string) SystemSetting::get(self::KEY_MAX_PER_APPLICATION, '3000.00'));
    }

    public static function maxPeriodQuota(): Money
    {
        return Money::of((string) SystemSetting::get(self::KEY_PERIOD_QUOTA, '10000.00'));
    }

    public static function overdueDays(): int
    {
        // URS v1.2 / UR-M02-005: KPI 14 hari hingga JKEW (PV-001).
        $days = (int) SystemSetting::get(self::KEY_OVERDUE_DAYS, '14');

        return max(1, $days);
    }

    /**
     * BR-007: bilangan tempoh 4-bulan layak dari tarikh lantikan hingga akhir tahun kalendar.
     * Tempoh lantikan dapat kuota penuh (tiada proration dalam tempoh).
     * Contoh: lantikan 9/2/2026 (Tempoh 1) → 3 tempoh; lantikan Okt 2026 (Tempoh 3) → 1 tempoh.
     */
    public static function eligiblePeriodCountForAlp(Alp $alp, int $calendarYear): int
    {
        if (! $alp->appointment_start) {
            return 3;
        }

        $start = Carbon::parse($alp->appointment_start)->startOfDay();

        if ((int) $start->year > $calendarYear) {
            return 0;
        }

        if ((int) $start->year < $calendarYear) {
            return 3;
        }

        $startPeriodIndex = self::periodFor($start, $calendarYear)['index'];

        return max(0, 3 - $startPeriodIndex + 1);
    }

    /**
     * BR-007: siling peruntukan tahunan = bilangan tempoh layak × kuota setiap tempoh (maks RM30,000).
     */
    public static function maxAnnualForAlp(Alp $alp, int $calendarYear): Money
    {
        $full = self::maxAnnualAllocation();
        $periods = self::eligiblePeriodCountForAlp($alp, $calendarYear);

        if ($periods <= 0) {
            return Money::zero();
        }

        if ($periods >= 3) {
            return $full;
        }

        $entitlement = self::maxPeriodQuota()->times($periods);

        return $entitlement->greaterThan($full) ? $full : $entitlement;
    }

    /**
     * @return list<string>
     */
    public static function validateAnnualAllocationForAlp(Money $allocationAmount, Alp $alp, int $calendarYear): array
    {
        if (! self::enabled()) {
            return [];
        }

        $max = self::maxAnnualForAlp($alp, $calendarYear);
        if ($allocationAmount->greaterThan($max)) {
            return [sprintf(
                'Polisi URS (BR-007): peruntukan (RM%s) melebihi kelayakan ALP mengikut tarikh lantikan (RM%s) bagi tahun %d.',
                $allocationAmount->format(),
                $max->format(),
                $calendarYear,
            )];
        }

        return [];
    }

    /** BR-010: alamat/lokasi mesti menunjukkan Kuala Lumpur. */
    public static function isKualaLumpurAddress(?string $address, ?string $location = null): bool
    {
        $hay = mb_strtolower(trim(($address ?? '').' '.($location ?? '')));
        if ($hay === '') {
            return false;
        }

        return str_contains($hay, 'kuala lumpur')
            || preg_match('/\bkl\b/', $hay) === 1
            || str_contains($hay, 'w.p. kuala lumpur')
            || str_contains($hay, 'wilayah persekutuan');
    }

    /**
     * BR-014/015: kurang 2 bulan sebelum program = short notice (masih boleh diproses).
     */
    public static function isShortNotice(?CarbonInterface $programStart, ?CarbonInterface $at = null): bool
    {
        if (! $programStart) {
            return false;
        }

        $at = $at ? Carbon::instance($at)->startOfDay() : now()->startOfDay();
        $deadline = Carbon::instance($programStart)->startOfDay()->subMonthsNoOverflow(self::PROGRAM_LEAD_MONTHS);

        return $at->greaterThan($deadline);
    }

    /** BR-014: tarikh program paling awal = tarikh permohonan + 2 bulan. */
    public static function minimumProgramDate(?CarbonInterface $appliedAt = null): Carbon
    {
        $appliedAt = ($appliedAt ? Carbon::instance($appliedAt) : now())->startOfDay();

        return $appliedAt->copy()->addMonthsNoOverflow(self::PROGRAM_LEAD_MONTHS);
    }

    /**
     * @return list<string>
     */
    public static function validateProgramLeadTime(?CarbonInterface $programDate, ?CarbonInterface $appliedAt = null): array
    {
        if (! $programDate) {
            return [];
        }

        $min = self::minimumProgramDate($appliedAt);
        $program = Carbon::instance($programDate)->startOfDay();

        if ($program->lessThan($min)) {
            return [sprintf(
                'Tarikh program mesti sekurang-kurangnya %d bulan dari tarikh hantar (tidak sebelum %s).',
                self::PROGRAM_LEAD_MONTHS,
                $min->format('d/m/Y'),
            )];
        }

        return [];
    }

    /**
     * @return list<string> ralat jika tarikh program tidak memenuhi BR-014 pada tarikh hantar.
     */
    public static function validateProgramLeadTimeForSubmission(?CarbonInterface $programDate, ?CarbonInterface $submitAt = null): array
    {
        return self::validateProgramLeadTime($programDate, $submitAt ?? now());
    }

    /** @return list<string> */
    public static function programDateSubmitErrors(?CarbonInterface $programDate): array
    {
        return self::validateProgramLeadTimeForSubmission($programDate);
    }

    /**
     * Tempoh 4-bulan bagi tarikh & tahun kalendar (biasanya tahun kewangan).
     *
     * @return array{index: int, label: string, start: Carbon, end: Carbon}
     */
    public static function periodFor(?CarbonInterface $at = null, ?int $calendarYear = null): array
    {
        $at = $at ? Carbon::instance($at)->copy() : now();
        $year = $calendarYear ?? (int) $at->year;
        $month = (int) $at->month;

        $index = match (true) {
            $month <= 4 => 1,
            $month <= 8 => 2,
            default => 3,
        };

        return self::periodByIndex($index, $year);
    }

    /**
     * @return array{index: int, label: string, start: Carbon, end: Carbon}
     */
    public static function periodByIndex(int $index, int $calendarYear): array
    {
        $index = max(1, min(3, $index));

        [$startMd, $endMd, $label] = match ($index) {
            1 => ['01-01 00:00:00', '04-30 23:59:59', 'Penggal 1 (Jan-Apr (4 bulan))'],
            2 => ['05-01 00:00:00', '08-31 23:59:59', 'Penggal 2 (Mei-Ogos (4 bulan))'],
            default => ['09-01 00:00:00', '12-31 23:59:59', 'Penggal 3 (Sep-Dis (4 bulan))'],
        };

        return [
            'index' => $index,
            'label' => $label,
            'start' => Carbon::parse(sprintf('%d-%s', $calendarYear, $startMd)),
            'end' => Carbon::parse(sprintf('%d-%s', $calendarYear, $endMd)),
        ];
    }

    /**
     * Jumlah permohonan yang dikira terhadap kuota tempoh (pending + diluluskan).
     * Attribution: submitted_at (atau created_at jika tiada) dalam tetingkap tempoh.
     */
    public static function periodUsage(
        int $alpId,
        int $financialYearId,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
        ?int $excludeApplicationId = null,
    ): Money {
        $statuses = array_merge(
            ApplicationStatus::pendingRequestValues(),
            [ApplicationStatus::APPROVED->value],
        );

        $sum = Application::query()
            ->officialSumbangan()
            ->where('alp_id', $alpId)
            ->where('financial_year_id', $financialYearId)
            ->whereIn('status', $statuses)
            ->when($excludeApplicationId, fn ($q) => $q->where('id', '!=', $excludeApplicationId))
            ->whereRaw('COALESCE(submitted_at, created_at) BETWEEN ? AND ?', [
                $periodStart->toDateTimeString(),
                $periodEnd->toDateTimeString(),
            ])
            ->sum('requested_amount');

        return Money::of($sum === null ? '0' : (string) $sum);
    }

    public static function periodRemaining(
        int $alpId,
        int $financialYearId,
        ?CarbonInterface $at = null,
        ?int $calendarYear = null,
        ?int $excludeApplicationId = null,
    ): Money {
        $period = self::periodFor($at, $calendarYear);
        $used = self::periodUsage(
            $alpId,
            $financialYearId,
            $period['start'],
            $period['end'],
            $excludeApplicationId,
        );

        $remaining = self::maxPeriodQuota()->minus($used);

        return $remaining->isNegative() ? Money::zero() : $remaining;
    }

    /**
     * @return list<string> mesej ralat (kosong jika lulus)
     */
    public static function validateApplicationAmount(Money $requested): array
    {
        if (! self::enabled()) {
            return [];
        }

        $max = self::maxPerApplication();
        if ($requested->greaterThan($max)) {
            return [sprintf(
                'Polisi URS: jumlah permohonan (RM%s) melebihi had maksimum setiap permohonan (RM%s).',
                $requested->format(),
                $max->format(),
            )];
        }

        return [];
    }

    /**
     * BR-003/004: kuota tempoh semasa; baki tempoh lepas tidak dibawa ke hadapan.
     *
     * @return list<string>
     */
    public static function validatePeriodQuota(
        Money $requested,
        int $alpId,
        int $financialYearId,
        int $calendarYear,
        ?int $excludeApplicationId = null,
        ?CarbonInterface $at = null,
    ): array {
        if (! self::enabled()) {
            return [];
        }

        $period = self::periodFor($at, $calendarYear);
        $used = self::periodUsage(
            $alpId,
            $financialYearId,
            $period['start'],
            $period['end'],
            $excludeApplicationId,
        );
        $projected = $used->plus($requested);
        $max = self::maxPeriodQuota();

        if ($projected->greaterThan($max)) {
            $remaining = $max->minus($used);
            if ($remaining->isNegative()) {
                $remaining = Money::zero();
            }

            return [sprintf(
                'Polisi URS (%s): kuota tempoh RM%s. Digunakan RM%s; baki RM%s. Jumlah dipohon RM%s melebihi baki tempoh. Baki tempoh lepas tidak dibawa ke hadapan (BR-003).',
                $period['label'],
                $max->format(),
                $used->format(),
                $remaining->format(),
                $requested->format(),
            )];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    public static function validateAnnualAllocation(Money $allocationAmount): array
    {
        if (! self::enabled()) {
            return [];
        }

        $max = self::maxAnnualAllocation();
        if ($allocationAmount->greaterThan($max)) {
            return [sprintf(
                'Polisi URS: peruntukan tahunan (RM%s) melebihi had maksimum ALP setahun (RM%s).',
                $allocationAmount->format(),
                $max->format(),
            )];
        }

        return [];
    }

    /**
     * Status dalam proses (belum keputusan akhir) untuk senarai tertunggak.
     *
     * @return list<string>
     */
    public static function inProcessStatusValues(): array
    {
        return [
            ApplicationStatus::SUBMITTED->value,
            ApplicationStatus::UNDER_SECRETARIAT_REVIEW->value,
            ApplicationStatus::UNDER_FINANCE_REVIEW->value,
            ApplicationStatus::UNDER_TECHNICAL_REVIEW->value,
            ApplicationStatus::PENDING_APPROVAL->value,
        ];
    }
}
