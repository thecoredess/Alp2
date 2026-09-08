<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationTimelineService;
use App\Services\Budget\BudgetService;
use App\Services\Reports\ApplicationReportService;
use App\Services\Reports\DataQualityService;
use App\Services\Reports\FinancialReportService;
use App\Services\Reports\ProjectReportService;
use App\Support\Money;
use App\Support\UrsContributionPolicy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BudgetService $budget,
        private readonly ApplicationBudgetService $appBudget,
        private readonly FinancialReportService $financial,
        private readonly ProjectReportService $projectReports,
        private readonly ApplicationReportService $applicationReports,
        private readonly DataQualityService $dataQuality,
        private readonly ApplicationTimelineService $timeline,
    ) {}

    /** Dashboard Eksekutif (pengurusan) — KPI kewangan menyeluruh. */
    public function executive(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.executive'), 403);
        $year = $this->resolveYear($request);
        $totals = $year ? $this->financial->totals($year->id) : new \App\Services\Reports\FinancialBreakdown();

        $projectCounts = $year ? $this->projectReports->statusCounts(['financial_year_id' => $year->id]) : [];
        $appCounts = $year ? $this->applicationReports->statusCounts(['financial_year_id' => $year->id]) : [];

        return view('dashboard.executive', [
            'years' => FinancialYear::orderByDesc('year')->get(),
            'year' => $year,
            'totals' => $totals,
            'utilisation' => $year ? $this->financial->alpUtilisation($year->id) : collect(),
            'projectCounts' => $projectCounts,
            'appCounts' => $appCounts,
            'alpCount' => Alp::where('status', 'active')->count(),
            'monthly' => $year ? $this->monthlyNetSpend($year->id) : [],
            'reconExceptions' => $year ? $this->financial->reconciliationExceptions($year->id)->count() : 0,
            'dataQualityTotal' => $year ? $this->dataQuality->totalExceptions($year->id) : 0,
        ]);
    }

    /** Dashboard Kewangan operasi — giliran & ringkasan. */
    public function finance(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.finance'), 403);
        $year = $this->resolveYear($request);
        $totals = $year ? $this->financial->totals($year->id) : new \App\Services\Reports\FinancialBreakdown();

        $queues = [
            'allocation' => \App\Models\BudgetRequest::where('status', \App\Enums\BudgetRequestStatus::PENDING_APPROVAL->value)
                ->whereIn('request_type', [\App\Enums\BudgetRequestType::INITIAL_ALLOCATION->value])->count(),
            'adjustment' => \App\Models\BudgetRequest::where('status', \App\Enums\BudgetRequestStatus::PENDING_APPROVAL->value)
                ->whereIn('request_type', [\App\Enums\BudgetRequestType::ALLOCATION_INCREASE->value, \App\Enums\BudgetRequestType::ALLOCATION_DECREASE->value])->count(),
            'expense' => \App\Models\ProjectExpense::where('status', \App\Enums\ProjectExpenseStatus::PENDING_VERIFICATION->value)->count(),
            'refund' => \App\Models\ProjectExpenseRefund::where('status', \App\Enums\RefundStatus::PENDING_VERIFICATION->value)->count(),
        ];

        return view('dashboard.finance', [
            'years' => FinancialYear::orderByDesc('year')->get(),
            'year' => $year,
            'totals' => $totals,
            'queues' => $queues,
        ]);
    }

    private function resolveYear(Request $request): ?FinancialYear
    {
        $id = $request->integer('fy');
        if ($id && ($y = FinancialYear::find($id))) {
            return $y;
        }

        return FinancialYear::active() ?? FinancialYear::orderByDesc('year')->first();
    }

    /**
     * Trend belanja bersih bulanan (EXPENDITURE − REFUND) bagi tahun kewangan.
     *
     * @return array<int, array{month: int, net: Money}>
     */
    private function monthlyNetSpend(int $fyId): array
    {
        $rows = \App\Models\BudgetTransaction::query()
            ->where('financial_year_id', $fyId)
            ->whereIn('type', [\App\Enums\BudgetTransactionType::EXPENDITURE->value, \App\Enums\BudgetTransactionType::REFUND->value])
            ->selectRaw('MONTH(created_at) as m, type, SUM(amount) as total')
            ->groupBy('m', 'type')->get();

        $net = array_fill(1, 12, null);
        for ($i = 1; $i <= 12; $i++) {
            $net[$i] = Money::zero();
        }
        foreach ($rows as $row) {
            $amt = Money::of((string) $row->total);
            $m = (int) $row->m;
            $net[$m] = $row->type === \App\Enums\BudgetTransactionType::EXPENDITURE
                ? $net[$m]->plus($amt) : $net[$m]->minus($amt);
        }

        return collect($net)->map(fn ($v, $m) => ['month' => $m, 'net' => $v])->values()->all();
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $activeYear = FinancialYear::active();

        $isManager = $user->hasAnyRole([
            RoleName::SUPER_ADMIN->value, RoleName::SYSTEM_ADMIN->value,
            RoleName::PENGURUSAN->value, RoleName::PEGAWAI_KEWANGAN->value,
        ]);

        $summary = null;
        $scope = null;
        $pending = Money::zero();
        $projected = Money::zero();
        $appStats = null;
        $annualAllocation = null;
        $annualAvailable = null;

        if ($activeYear) {
            if ($user->alp_id) {
                $scope = 'own';
                $alpSummary = $this->budget->summaryFor($user->alp_id, $activeYear->id);
                $annualAllocation = $alpSummary->allocation;
                $annualAvailable = $alpSummary->available();
                $appStats = [
                    'draft' => $this->count($activeYear->id, [ApplicationStatus::DRAFT->value], $user->alp_id),
                    'in_process' => $this->count($activeYear->id, $this->inProcessStatuses(), $user->alp_id),
                    'revision' => $this->count($activeYear->id, [ApplicationStatus::REVISION_REQUIRED->value], $user->alp_id),
                    'approved' => $this->count($activeYear->id, [ApplicationStatus::APPROVED->value], $user->alp_id),
                    'rejected' => $this->count($activeYear->id, [ApplicationStatus::REJECTED->value], $user->alp_id),
                ];
            } elseif ($isManager) {
                $summary = $this->budget->totalsForYear($activeYear->id);
                $pending = $this->pendingTotal($activeYear->id);
                $projected = $summary->available()->minus($pending);
                $scope = 'all';
                $appStats = [
                    'total' => $this->count($activeYear->id),
                    'under_review' => $this->count($activeYear->id, $this->underReviewStatuses()),
                    'pending_approval' => $this->count($activeYear->id, [ApplicationStatus::PENDING_APPROVAL->value]),
                    'approved' => $this->count($activeYear->id, [ApplicationStatus::APPROVED->value]),
                    'rejected' => $this->count($activeYear->id, [ApplicationStatus::REJECTED->value]),
                ];
            }
        }

        // Giliran pegawai (URS v1.2 — semakan JP + kelulusan + baucar).
        $officerQueues = null;
        if ($user->canAny(['applications.review.secretariat', 'applications.approve', 'payments.view'])) {
            $officerQueues = [];
            if ($user->can('applications.review.secretariat')) {
                $officerQueues[] = [
                    'label' => 'Menunggu Semakan JP',
                    'description' => 'Permohonan dihantar ALP — perlu semakan & senarai semak JP',
                    'count' => $this->count($activeYear?->id, [ApplicationStatus::SUBMITTED->value]),
                    'route' => 'reviews.secretariat',
                    'icon' => 'clipboard',
                    'tone' => 'blue',
                ];
            }
            if ($user->can('applications.approve')) {
                $officerQueues[] = [
                    'label' => 'Menunggu Peraku / Pelulus',
                    'description' => 'Permohonan disyorkan JP — menunggu kelulusan Peraku / PEPU',
                    'count' => $this->count($activeYear?->id, [ApplicationStatus::PENDING_APPROVAL->value]),
                    'route' => 'approvals.queue',
                    'icon' => 'shield-check',
                    'tone' => 'amber',
                ];
            }
            if ($user->can('payments.view')) {
                $officerQueues[] = [
                    'label' => 'Pembayaran / Baucar',
                    'description' => 'Permohonan diluluskan — kemas kini baucar & status bayaran',
                    'count' => Application::query()
                        ->where('status', ApplicationStatus::APPROVED->value)
                        ->whereIn('payment_status', \App\Enums\ApplicationPaymentStatus::openValues())
                        ->count(),
                    'route' => 'payments.index',
                    'icon' => 'receipt',
                    'tone' => 'green',
                ];
            }
        }

        // Modul projek diasingkan ke legacy — tiada statistik projek pada dashboard aktif.
        $projectStats = null;

        $stats = null;
        if ($user->hasAnyRole([RoleName::SUPER_ADMIN->value, RoleName::SYSTEM_ADMIN->value, RoleName::PENGURUSAN->value])) {
            $stats = [
                'alp_count' => Alp::where('status', 'active')->count(),
                'user_count' => User::where('status', 'active')->count(),
                'financial_year' => $activeYear?->year,
            ];
        }

        $periodSummary = null;
        if ($activeYear && $user->alp_id && UrsContributionPolicy::enabled()) {
            $period = UrsContributionPolicy::periodFor(now(), (int) $activeYear->year);
            $used = UrsContributionPolicy::periodUsage(
                $user->alp_id,
                $activeYear->id,
                $period['start'],
                $period['end'],
            );
            $quota = UrsContributionPolicy::maxPeriodQuota();
            $remaining = $quota->minus($used);
            if ($remaining->isNegative()) {
                $remaining = Money::zero();
            }
            $periodSummary = [
                'label' => $period['label'],
                'quota' => $quota,
                'used' => $used,
                'remaining' => $remaining,
                'ends_at' => $period['end'],
            ];
        }

        $overdueApplications = $this->overdueApplications(
            $activeYear?->id,
            $user->alp_id && ! $user->can('applications.view_all') ? $user->alp_id : null,
        );

        $kpiWatchlist = $user->can('applications.view_all')
            ? $this->kpiWatchlist($activeYear?->id, null)
            : collect();

        return view('dashboard.index', [
            'activeYear' => $activeYear,
            'stats' => $stats,
            'summary' => $summary,
            'scope' => $scope,
            'pending' => $pending,
            'projected' => $projected,
            'appStats' => $appStats,
            'officerQueues' => $officerQueues,
            'projectStats' => $projectStats,
            'recentNotifications' => $user->notifications()->latest()->limit(5)->get(),
            'periodSummary' => $periodSummary,
            'overdueApplications' => $overdueApplications,
            'overdueDays' => UrsContributionPolicy::overdueDays(),
            'kpiWatchlist' => $kpiWatchlist,
            'kpiDays' => ApplicationTimelineService::KPI_DAYS,
            'annualAllocation' => $annualAllocation,
            'annualAvailable' => $annualAvailable,
        ]);
    }

    /**
     * Permohonan dalam proses / menunggu JKEW untuk indikator KPI 14 hari.
     *
     * @return \Illuminate\Support\Collection<int, array{application: Application, kpi: array}>
     */
    private function kpiWatchlist(?int $fyId, ?int $alpId)
    {
        $apps = Application::query()
            ->with(['alp:id,ref_code,name'])
            ->when($fyId, fn ($q) => $q->where('financial_year_id', $fyId))
            ->when($alpId, fn ($q) => $q->where('alp_id', $alpId))
            ->whereNotNull('submitted_at')
            ->where(function ($q) {
                $q->whereIn('status', UrsContributionPolicy::inProcessStatusValues())
                    ->orWhere(function ($inner) {
                        $inner->where('status', ApplicationStatus::APPROVED->value)
                            ->whereNull('sent_to_jkew_at');
                    });
            })
            ->orderBy('submitted_at')
            ->limit(8)
            ->get();

        return $apps->map(fn (Application $app) => [
            'application' => $app,
            'kpi' => $this->timeline->kpi($app),
        ]);
    }

    /**
     * Permohonan dalam proses yang melebihi ambang hari tertunggak.
     *
     * @return \Illuminate\Support\Collection<int, Application>
     */
    private function overdueApplications(?int $fyId, ?int $alpId)
    {
        $cutoff = now()->subDays(UrsContributionPolicy::overdueDays());

        return Application::query()
            ->with(['alp:id,ref_code,name', 'financialYear:id,year'])
            ->when($fyId, fn ($q) => $q->where('financial_year_id', $fyId))
            ->when($alpId, fn ($q) => $q->where('alp_id', $alpId))
            ->whereIn('status', UrsContributionPolicy::inProcessStatusValues())
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<=', $cutoff)
            ->orderBy('submitted_at')
            ->limit(10)
            ->get();
    }

    private function inProcessStatuses(): array
    {
        return UrsContributionPolicy::inProcessStatusValues();
    }

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
