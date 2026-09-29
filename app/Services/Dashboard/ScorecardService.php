<?php

namespace App\Services\Dashboard;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ProgramCategory;
use App\Enums\ReportCardStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Budget\BudgetService;
use App\Services\Reports\ApplicationReportService;
use App\Services\Reports\FinancialReportService;
use App\Services\Reports\ProgramReportService;
use App\Support\Money;
use App\Support\UrsContributionPolicy;

class ScorecardService
{
    public function __construct(
        private readonly BudgetService $budget,
        private readonly ProgramReportService $programs,
        private readonly FinancialReportService $financialReports,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function buildAlp(User $user, FinancialYear $year): ?array
    {
        if ($user->alp_id === null) {
            return null;
        }

        $alpId = $user->alp_id;
        $fyId = $year->id;

        $alpSummary = $this->budget->summaryFor($alpId, $fyId);
        $allocation = $alpSummary->allocation;
        $available = $alpSummary->available();
        $used = $allocation->minus($available);
        $utilisationPercent = $allocation->isZero()
            ? 0.0
            : min(100.0, round(((float) $used->value() / (float) $allocation->value()) * 100, 1));

        $period = null;
        if (UrsContributionPolicy::enabled()) {
            $periodWindow = UrsContributionPolicy::periodFor(now(), (int) $year->year);
            $usedPeriod = UrsContributionPolicy::periodUsage(
                $alpId,
                $fyId,
                $periodWindow['start'],
                $periodWindow['end'],
            );
            $quota = UrsContributionPolicy::maxPeriodQuota();
            $remaining = $quota->minus($usedPeriod);
            if ($remaining->isNegative()) {
                $remaining = Money::zero();
            }
            $period = [
                'label' => $periodWindow['label'],
                'quota' => $quota,
                'used' => $usedPeriod,
                'remaining' => $remaining,
                'ends_at' => $periodWindow['end'],
            ];
        }

        $programRows = $this->programs->listing([
            'financial_year_id' => $fyId,
            'alp_id' => $alpId,
        ]);
        $reportOverdue = $programRows->filter(function (array $row): bool {
            $due = $row['due_at'];
            if ($due === null || ! now()->greaterThan($due)) {
                return false;
            }

            return in_array($row['status'], [
                ProgramReportService::STATUS_PENDING,
                ApplicationReportService::FILTER_PENDING,
            ], true);
        })->count();

        return [
            'actions' => [
                'draft' => $this->count($fyId, [ApplicationStatus::DRAFT->value], $alpId),
                'revision' => $this->count($fyId, [ApplicationStatus::REVISION_REQUIRED->value], $alpId),
                'in_process' => $this->count($fyId, $this->inProcessStatuses(), $alpId),
                'report_overdue' => $reportOverdue,
            ],
            'budget' => [
                'allocation' => $allocation,
                'available' => $available,
                'utilisation_percent' => $utilisationPercent,
                'period' => $period,
            ],
            'links' => $this->alpLinks($fyId),
        ];
    }

    /**
     * @param  'dashboard'|'reports'  $context
     * @return array<string, mixed>
     */
    public function buildPepu(FinancialYear $year, string $context = 'dashboard'): array
    {
        $fyId = $year->id;
        $summary = $this->budget->totalsForYear($fyId);
        $pending = $this->pendingTotal($fyId);
        $projected = $summary->available()->minus($pending);

        return [
            'executive' => [
                'pending_approval' => $this->count($fyId, [ApplicationStatus::PENDING_APPROVAL->value]),
                'under_review' => $this->count($fyId, $this->underReviewStatuses()),
                'approved' => $this->count($fyId, [ApplicationStatus::APPROVED->value]),
                'total' => $this->count($fyId),
            ],
            'budget' => [
                'alp_count' => Alp::where('status', 'active')->count(),
                'allocation' => $summary->allocation,
                'available' => $summary->available(),
                'projected' => $projected,
                'utilisation_percent' => $summary->utilisationPercent(),
            ],
            'links' => $this->pepuLinks($fyId, $context),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sampleAlp(?FinancialYear $year): array
    {
        $calendarYear = $year?->year ?? (int) date('Y');
        $period = UrsContributionPolicy::enabled()
            ? UrsContributionPolicy::periodFor(now(), $calendarYear)
            : null;

        return [
            'actions' => [
                'draft' => 2,
                'revision' => 1,
                'in_process' => 3,
                'report_overdue' => 1,
            ],
            'budget' => [
                'allocation' => Money::of('30000'),
                'available' => Money::of('18500'),
                'utilisation_percent' => 38.3,
                'period' => $period ? [
                    'label' => $period['label'],
                    'quota' => UrsContributionPolicy::maxPeriodQuota(),
                    'used' => Money::of('6200'),
                    'remaining' => Money::of('3800'),
                    'ends_at' => $period['end'],
                ] : null,
            ],
            'links' => $this->alpLinks($year?->id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function samplePepu(?FinancialYear $year): array
    {
        return [
            'executive' => [
                'pending_approval' => 4,
                'under_review' => 12,
                'approved' => 86,
                'total' => 142,
            ],
            'budget' => [
                'alp_count' => 28,
                'allocation' => Money::of('850000'),
                'available' => Money::of('412000'),
                'projected' => Money::of('385500'),
                'utilisation_percent' => 51.5,
            ],
            'links' => $this->pepuLinks($year?->id, 'dashboard'),
        ];
    }

    /**
     * Ringkasan permohonan ALP untuk modul Laporan (jumlah + kategori aktiviti).
     *
     * @return array<string, mixed>|null
     */
    public function buildAlpReportSummary(User $user, FinancialYear $year): ?array
    {
        if ($user->alp_id === null) {
            return null;
        }

        $fyId = $year->id;
        $base = Application::query()
            ->where('alp_id', $user->alp_id)
            ->where('financial_year_id', $fyId)
            ->where('application_type', ApplicationType::SUMBANGAN->value)
            ->whereNotNull('submitted_at');

        $total = (clone $base)->count();

        $completedBase = (clone $base)
            ->where('status', ApplicationStatus::APPROVED->value)
            ->where('report_card_status', ReportCardStatus::APPROVED->value);

        $completedTotal = (clone $completedBase)->count();
        $completedByCategory = $this->completedByCategoryCounts($completedBase);

        $alpId = $user->alp_id;
        $alpSummary = $this->budget->summaryFor($alpId, $fyId);
        $annualAllocation = $alpSummary->allocation;

        $period = null;
        if (UrsContributionPolicy::enabled()) {
            $periodWindow = UrsContributionPolicy::periodFor(now(), (int) $year->year);
            $usedPeriod = UrsContributionPolicy::periodUsage(
                $alpId,
                $fyId,
                $periodWindow['start'],
                $periodWindow['end'],
            );
            $quota = UrsContributionPolicy::maxPeriodQuota();
            $remaining = $quota->minus($usedPeriod);
            if ($remaining->isNegative()) {
                $remaining = Money::zero();
            }
            $period = [
                'label' => $periodWindow['label'],
                'quota' => $quota,
                'used' => $usedPeriod,
                'remaining' => $remaining,
                'ends_at' => $periodWindow['end'],
            ];
        }

        return [
            'total' => $total,
            'completed_total' => $completedTotal,
            'completed_by_category' => $completedByCategory,
            'budget' => [
                'annual_allocation' => $annualAllocation,
                'period' => $period,
            ],
            'links' => [
                'total' => route('applications.index', ['tahun' => $fyId]),
                'completed' => route('applications.index', [
                    'tahun' => $fyId,
                    'status' => ApplicationReportService::FILTER_RECEIVED,
                ]),
                'budget' => route('budget.mine'),
            ],
        ];
    }

    /**
     * Ringkasan Laporan PEPU (keseluruhan sistem + pecahan kategori setiap ALP).
     *
     * @return array<string, mixed>
     */
    public function buildPepuReportSummary(FinancialYear $year): array
    {
        $fyId = $year->id;
        $base = Application::query()
            ->where('financial_year_id', $fyId)
            ->where('application_type', ApplicationType::SUMBANGAN->value)
            ->whereNotNull('submitted_at');

        $total = (clone $base)->count();

        $completedBase = (clone $base)
            ->where('status', ApplicationStatus::APPROVED->value)
            ->where('report_card_status', ReportCardStatus::APPROVED->value);

        $completedTotal = (clone $completedBase)->count();
        $completedByCategory = $this->completedByCategoryCounts($completedBase);

        $summary = $this->budget->totalsForYear($fyId);
        $pending = $this->pendingTotal($fyId);
        $projected = $summary->available()->minus($pending);

        $period = null;
        if (UrsContributionPolicy::enabled()) {
            $periodWindow = UrsContributionPolicy::periodFor(now(), (int) $year->year);
            $activeAlpIds = Alp::query()->where('status', 'active')->pluck('id');
            $alpCount = max(1, $activeAlpIds->count());
            $quota = Money::zero();
            $periodQuota = UrsContributionPolicy::maxPeriodQuota();
            for ($i = 0; $i < $alpCount; $i++) {
                $quota = $quota->plus($periodQuota);
            }
            $usedPeriod = Money::zero();
            foreach ($activeAlpIds as $alpId) {
                $usedPeriod = $usedPeriod->plus(UrsContributionPolicy::periodUsage(
                    (int) $alpId,
                    $fyId,
                    $periodWindow['start'],
                    $periodWindow['end'],
                ));
            }
            $remaining = $quota->minus($usedPeriod);
            if ($remaining->isNegative()) {
                $remaining = Money::zero();
            }
            $period = [
                'label' => $periodWindow['label'],
                'quota' => $quota,
                'used' => $usedPeriod,
                'remaining' => $remaining,
                'ends_at' => $periodWindow['end'],
            ];
        }

        $links = $this->pepuLinks($fyId, 'reports');
        $completedLink = route('applications.all', [
            'tahun' => $fyId,
            'status' => ApplicationReportService::FILTER_RECEIVED,
        ]);

        $byAlp = Alp::query()
            ->where('status', 'active')
            ->orderBy('ref_code')
            ->get()
            ->map(function (Alp $alp) use ($fyId, $completedLink): array {
                $alpCompleted = Application::query()
                    ->where('alp_id', $alp->id)
                    ->where('financial_year_id', $fyId)
                    ->where('application_type', ApplicationType::SUMBANGAN->value)
                    ->whereNotNull('submitted_at')
                    ->where('status', ApplicationStatus::APPROVED->value)
                    ->where('report_card_status', ReportCardStatus::APPROVED->value);

                return [
                    'alp' => $alp,
                    'completed_total' => (clone $alpCompleted)->count(),
                    'completed_by_category' => $this->completedByCategoryCounts($alpCompleted),
                    'completed_link' => route('applications.all', [
                        'tahun' => $fyId,
                        'status' => ApplicationReportService::FILTER_RECEIVED,
                        'alp' => $alp->id,
                    ]),
                    'detail_link' => route('reports.alp-completed-activities', [
                        'alp' => $alp->id,
                        'fy' => $fyId,
                    ]),
                ];
            })
            ->values()
            ->all();

        return [
            'total' => $total,
            'completed_total' => $completedTotal,
            'completed_by_category' => $completedByCategory,
            'by_alp' => $byAlp,
            'budget' => [
                'annual_allocation' => $summary->allocation,
                'available' => $summary->available(),
                'projected' => $projected,
                'utilisation_percent' => $summary->utilisationPercent(),
                'alp_count' => Alp::where('status', 'active')->count(),
                'period' => $period,
            ],
            'links' => [
                'total' => route('applications.all', ['tahun' => $fyId]),
                'completed' => $completedLink,
                'budget' => $links['budget'],
            ],
        ];
    }

    /**
     * Score card untuk hub Laporan — ikut skop pengguna.
     *
     * @return array{alpReportCards: array<string, mixed>|null, pepuScorecards: array<string, mixed>|null, pepuReportCards: array<string, mixed>|null}
     */
    public function forReportsHub(User $user, FinancialYear $year): array
    {
        if ($user->alp_id !== null) {
            return [
                'alpReportCards' => $this->buildAlpReportSummary($user, $year),
                'pepuScorecards' => null,
                'pepuReportCards' => null,
            ];
        }

        if ($user->hasRole(RoleName::PENGURUSAN->value)) {
            return [
                'alpReportCards' => null,
                'pepuScorecards' => null,
                'pepuReportCards' => $this->buildPepuReportSummary($year),
            ];
        }

        if ($user->hasRole(RoleName::PELULUS->value)) {
            return [
                'alpReportCards' => null,
                'pepuScorecards' => null,
                'pepuReportCards' => $this->buildPepuReportSummary($year),
            ];
        }

        if ($user->can('applications.view_all')) {
            return [
                'alpReportCards' => null,
                'pepuScorecards' => $this->buildPepu($year, 'reports'),
                'pepuReportCards' => null,
            ];
        }

        return [
            'alpReportCards' => null,
            'pepuScorecards' => null,
            'pepuReportCards' => null,
        ];
    }

    /**
     * Aktiviti sumbangan selesai (diluluskan + laporan aktiviti disahkan JP) bagi satu ALP.
     *
     * @return array{
     *     activities: \Illuminate\Support\Collection<int, Application>,
     *     total_amount: Money,
     *     budget: \App\Services\Reports\FinancialBreakdown
     * }
     */
    public function completedActivitiesDetail(Alp $alp, FinancialYear $year): array
    {
        $activities = Application::query()
            ->where('alp_id', $alp->id)
            ->where('financial_year_id', $year->id)
            ->where('application_type', ApplicationType::SUMBANGAN->value)
            ->whereNotNull('submitted_at')
            ->where('status', ApplicationStatus::APPROVED->value)
            ->where('report_card_status', ReportCardStatus::APPROVED->value)
            ->orderByRaw('COALESCE(program_date, submitted_at) ASC')
            ->get();

        $sum = (string) ($activities->sum('requested_amount') ?? '0');

        return [
            'activities' => $activities,
            'total_amount' => Money::of($sum === '' ? '0' : $sum),
            'budget' => $this->financialReports->alpBreakdown($alp->id, $year->id),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Application>  $completedBase
     * @return list<array{key: string, label: string, count: int, icon: string, tone: string}>
     */
    private function completedByCategoryCounts($completedBase): array
    {
        $meta = [
            ProgramCategory::KOMUNITI->value => ['icon' => 'users-group', 'tone' => 'royal'],
            ProgramCategory::SUKAN->value => ['icon' => 'chart', 'tone' => 'green'],
            ProgramCategory::PENDIDIKAN->value => ['icon' => 'building', 'tone' => 'blue'],
            ProgramCategory::KEMASYARAKATAN->value => ['icon' => 'sparkles', 'tone' => 'purple'],
        ];

        return collect(ProgramCategory::cases())
            ->map(function (ProgramCategory $category) use ($completedBase, $meta): array {
                $m = $meta[$category->value];

                return [
                    'key' => $category->value,
                    'label' => $category->label(),
                    'count' => (clone $completedBase)->where('program_category', $category->value)->count(),
                    'icon' => $m['icon'],
                    'tone' => $m['tone'],
                ];
            })
            ->all();
    }

    /** @return array<string, string|null> */
    private function alpLinks(?int $financialYearId): array
    {
        $query = $financialYearId ? ['tahun' => $financialYearId] : [];

        return [
            'draft' => route('applications.index', $query),
            'revision' => route('applications.index', $query + ['status' => ApplicationReportService::FILTER_RETURNED]),
            'in_process' => route('applications.index', $query + ['status' => ApplicationReportService::FILTER_IN_REVIEW]),
            'report_overdue' => route('applications.index', $query + ['status' => ApplicationReportService::FILTER_PENDING]),
            'budget' => route('budget.mine'),
        ];
    }

    /**
     * @param  'dashboard'|'reports'  $context
     * @return array<string, string>
     */
    private function pepuLinks(?int $financialYearId, string $context = 'dashboard'): array
    {
        $appQuery = $financialYearId ? ['tahun' => $financialYearId] : [];
        $reportQuery = $financialYearId ? ['fy' => $financialYearId] : [];

        return [
            'pending_approval' => route('approvals.queue', $appQuery),
            'under_review' => route('applications.all', $appQuery + ['status' => ApplicationReportService::FILTER_IN_REVIEW]),
            'approved' => route('applications.all', $appQuery + ['status' => ApplicationReportService::FILTER_APPROVED]),
            'total' => $context === 'reports'
                ? route('reports.applications', $reportQuery)
                : route('applications.all', $appQuery),
            'budget' => $context === 'reports'
                ? route('reports.allocation', $reportQuery)
                : route('allocations.index'),
        ];
    }

    /** @return list<string> */
    private function inProcessStatuses(): array
    {
        return UrsContributionPolicy::inProcessStatusValues();
    }

    /** @return list<string> */
    private function underReviewStatuses(): array
    {
        return [
            ApplicationStatus::SUBMITTED->value,
            ApplicationStatus::UNDER_SECRETARIAT_REVIEW->value,
            ApplicationStatus::UNDER_FINANCE_REVIEW->value,
            ApplicationStatus::UNDER_TECHNICAL_REVIEW->value,
        ];
    }

    private function count(?int $fyId, array $statuses = [], ?int $alpId = null): int
    {
        return Application::query()
            ->when($fyId, fn ($q) => $q->where('financial_year_id', $fyId))
            ->when($statuses, fn ($q) => $q->whereIn('status', $statuses))
            ->when($alpId, fn ($q) => $q->where('alp_id', $alpId))
            ->count();
    }

    private function pendingTotal(int $fyId): Money
    {
        $sum = Application::where('financial_year_id', $fyId)
            ->whereIn('status', ApplicationStatus::pendingRequestValues())
            ->sum('requested_amount');

        return Money::of($sum === null ? '0' : (string) $sum);
    }
}
