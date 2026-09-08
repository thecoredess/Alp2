<?php

namespace App\Support;

use App\Models\Alp;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Budget\BudgetService;

/** Semakan jumlah sumbangan ALP — selaras penghantaran & borang. */
final class ApplicationAmountValidator
{
    /**
     * @return list<string> mesej ralat (kosong jika lulus)
     */
    public static function errorsFor(
        Money $amount,
        int $alpId,
        int $financialYearId,
        int $calendarYear,
        ?int $excludeApplicationId = null,
        bool $forceMaxPerApplication = false,
    ): array {
        $errors = [];

        // BR-005: Admin JP sentiasa terikat had RM3,000 setiap permohonan.
        if ($forceMaxPerApplication || auth()->user()?->canCreateApplicationOnBehalf()) {
            $max = UrsContributionPolicy::maxPerApplication();
            if ($amount->greaterThan($max)) {
                $errors[] = sprintf(
                    'Jumlah permohonan (RM%s) melebihi had maksimum setiap permohonan (RM%s).',
                    $amount->format(),
                    $max->format(),
                );
            }
        } else {
            foreach (UrsContributionPolicy::validateApplicationAmount($amount) as $error) {
                $errors[] = $error;
            }
        }

        $summary = app(BudgetService::class)->summaryFor($alpId, $financialYearId);
        if ($summary->allocation->isPositive()) {
            $available = app(ApplicationBudgetService::class)->availableForNewRequest(
                $alpId,
                $financialYearId,
                $excludeApplicationId ?? 0,
            );

            if ($amount->greaterThan($available)) {
                $errors[] = sprintf(
                    'Baki peruntukan tidak mencukupi. Tersedia untuk permohonan baharu: RM%s; jumlah dimasukkan: RM%s.',
                    $available->format(),
                    $amount->format(),
                );
            }
        }

        foreach (UrsContributionPolicy::validatePeriodQuota(
            $amount,
            $alpId,
            $financialYearId,
            $calendarYear,
            $excludeApplicationId,
        ) as $error) {
            $errors[] = $error;
        }

        return $errors;
    }

    /** Had & baki untuk paparan borang (ALP). */
    public static function limitsFor(Alp $alp, FinancialYear $year, ?int $excludeApplicationId = null, bool $forceMaxPerApplication = false): array
    {
        $forceMax = $forceMaxPerApplication || (auth()->user()?->canCreateApplicationOnBehalf() ?? false);

        $summary = app(BudgetService::class)->summaryFor($alp->id, $year->id);
        $hasAllocation = $summary->allocation->isPositive();
        $available = $hasAllocation
            ? app(ApplicationBudgetService::class)->availableForNewRequest(
                $alp->id,
                $year->id,
                $excludeApplicationId ?? 0,
            )
            : Money::zero();

        $periodRemaining = UrsContributionPolicy::periodRemaining(
            $alp->id,
            $year->id,
            calendarYear: (int) $year->year,
            excludeApplicationId: $excludeApplicationId,
        );

        $maxPerApp = UrsContributionPolicy::maxPerApplication();
        $effectiveMax = $hasAllocation ? $available : $maxPerApp;

        if (UrsContributionPolicy::enabled() || $forceMax) {
            $caps = [$maxPerApp];
            if (UrsContributionPolicy::enabled()) {
                $caps[] = $periodRemaining;
            }
            foreach ($caps as $cap) {
                if ($cap->lessThan($effectiveMax)) {
                    $effectiveMax = $cap;
                }
            }
        } elseif (! $hasAllocation) {
            $effectiveMax = Money::of('999999999.99');
        }

        // Admin JP: pastikan effective max tidak melebihi RM3,000.
        if ($forceMax && $maxPerApp->lessThan($effectiveMax)) {
            $effectiveMax = $maxPerApp;
        }

        return [
            'policy_enabled' => UrsContributionPolicy::enabled(),
            'force_max_per_application' => $forceMax,
            'has_allocation' => $hasAllocation,
            'calendar_year' => (int) $year->year,
            'max_per_application' => $maxPerApp->value(),
            'period_remaining' => $periodRemaining->value(),
            'available' => $available->value(),
            'effective_max' => $effectiveMax->value(),
        ];
    }

    /**
     * Had asas untuk Admin JP sebelum ALP dipilih — paparan amaran langsung seperti modul ALP.
     *
     * @return array<string, mixed>
     */
    public static function limitsBaseline(bool $forceMaxPerApplication = true): array
    {
        $maxPerApp = UrsContributionPolicy::maxPerApplication();

        return [
            'policy_enabled' => UrsContributionPolicy::enabled(),
            'force_max_per_application' => $forceMaxPerApplication,
            'has_allocation' => false,
            'calendar_year' => (int) now()->year,
            'max_per_application' => $maxPerApp->value(),
            'period_remaining' => $maxPerApp->value(),
            'available' => '0.00',
            'effective_max' => $maxPerApp->value(),
        ];
    }
}
