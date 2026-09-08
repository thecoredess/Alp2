<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Budget\BudgetException;
use App\Services\Budget\BudgetService;
use App\Support\Money;
use App\Support\UrsContributionPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Paparan + set peruntukan Admin JP (URS v1.2 Fasa 7.12).
 * Maker-checker BudgetRequest dinyahaktifkan dari route aktif.
 */
class AllocationController extends Controller
{
    public function __construct(
        private readonly BudgetService $budget,
        private readonly ApplicationBudgetService $appBudget,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Allocation::class);

        $years = FinancialYear::orderByDesc('year')->get();
        $year = $this->resolveYear($request, $years);

        $summaries = $year ? $this->budget->summariesForYear($year->id) : collect();
        $allocations = $year
            ? Allocation::where('financial_year_id', $year->id)->get()->keyBy('alp_id')
            : collect();
        $alps = Alp::orderBy('ref_code')->get();
        $totals = $year ? $this->budget->totalsForYear($year->id) : new \App\Services\Budget\BudgetSummary();

        $pendingByAlp = collect();
        $totalPending = Money::zero();

        if ($year) {
            $pendingByAlp = Application::query()
                ->where('financial_year_id', $year->id)
                ->whereIn('status', ApplicationStatus::pendingRequestValues())
                ->groupBy('alp_id')
                ->selectRaw('alp_id, SUM(requested_amount) as total')
                ->get()
                ->mapWithKeys(fn ($r) => [$r->alp_id => Money::of((string) $r->total)]);
            $totalPending = $pendingByAlp->reduce(fn (Money $c, Money $m) => $c->plus($m), Money::zero());
        }
        $projectedTotal = $totals->available()->minus($totalPending);

        return view('allocations.index', compact(
            'years', 'year', 'summaries', 'allocations', 'alps', 'totals',
            'pendingByAlp', 'totalPending', 'projectedTotal'
        ));
    }

    public function show(Allocation $allocation): View
    {
        $this->authorize('view', $allocation);

        $allocation->load(['alp', 'financialYear', 'creator']);
        $summary = $this->budget->summaryFor($allocation->alp_id, $allocation->financial_year_id);
        $statement = $this->budget->statement($allocation);
        $entitlement = UrsContributionPolicy::enabled()
            ? UrsContributionPolicy::maxAnnualForAlp($allocation->alp, (int) $allocation->financialYear->year)
            : null;

        return view('allocations.show', compact('allocation', 'summary', 'statement', 'entitlement'));
    }

    /** "Bajet Saya" — paparan bajet DILULUSKAN untuk ALP pengguna semasa. */
    public function myBudget(Request $request): View
    {
        $user = $request->user();
        abort_if($user->alp_id === null, 403, 'Akaun anda tidak dikaitkan dengan mana-mana ALP.');

        $alp = $user->alp;
        $year = FinancialYear::active();
        $allocation = $year
            ? Allocation::where('alp_id', $alp->id)->where('financial_year_id', $year->id)->first()
            : null;

        $summary = $year
            ? $this->budget->summaryFor($alp->id, $year->id)
            : new \App\Services\Budget\BudgetSummary();
        $statement = $allocation ? $this->budget->statement($allocation) : [];

        $pending = $year ? $this->appBudget->pendingRequest($alp->id, $year->id) : Money::zero();
        $projected = $summary->available()->minus($pending);

        $policyEnabled = UrsContributionPolicy::enabled();
        $periodSummary = null;
        $policyLimits = null;

        if ($year && $policyEnabled) {
            $calendarYear = (int) $year->year;
            $period = UrsContributionPolicy::periodFor(now(), $calendarYear);
            $used = UrsContributionPolicy::periodUsage(
                $alp->id,
                $year->id,
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

            $policyLimits = [
                'max_annual_policy' => UrsContributionPolicy::maxAnnualAllocation(),
                'max_annual_entitlement' => UrsContributionPolicy::maxAnnualForAlp($alp, $calendarYear),
                'entitlement_periods' => UrsContributionPolicy::eligiblePeriodCountForAlp($alp, $calendarYear),
                'appointment_start' => $alp->appointment_start?->format('d/m/Y'),
                'max_per_application' => UrsContributionPolicy::maxPerApplication(),
                'period_quota' => UrsContributionPolicy::maxPeriodQuota(),
                'periods' => [
                    UrsContributionPolicy::periodByIndex(1, $calendarYear),
                    UrsContributionPolicy::periodByIndex(2, $calendarYear),
                    UrsContributionPolicy::periodByIndex(3, $calendarYear),
                ],
            ];
        }

        return view('allocations.my-budget', compact(
            'alp', 'year', 'allocation', 'summary', 'statement', 'pending', 'projected',
            'policyEnabled', 'periodSummary', 'policyLimits',
        ));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Allocation::class);

        $years = FinancialYear::orderByDesc('year')->get();
        $year = $this->resolveYear($request, $years);
        $alps = Alp::where('status', \App\Enums\AlpStatus::ACTIVE)->orderBy('ref_code')->get();

        $already = $year
            ? Allocation::where('financial_year_id', $year->id)->pluck('alp_id')->all()
            : [];

        $eligibleAlps = $alps->reject(fn (Alp $a) => in_array($a->id, $already, true))->values();

        $entitlements = [];
        if ($year && UrsContributionPolicy::enabled()) {
            foreach ($eligibleAlps as $alp) {
                $entitlements[$alp->id] = UrsContributionPolicy::maxAnnualForAlp($alp, (int) $year->year)->value();
            }
        }

        return view('allocations.create', [
            'years' => $years,
            'year' => $year,
            'alps' => $eligibleAlps,
            'entitlements' => $entitlements,
            'selectedAlpId' => $request->integer('alp') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Allocation::class);

        $data = $request->validate([
            'alp_id' => ['required', 'exists:alps,id'],
            'financial_year_id' => ['required', 'exists:financial_years,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $alp = Alp::findOrFail($data['alp_id']);
        $year = FinancialYear::findOrFail($data['financial_year_id']);

        try {
            $allocation = $this->budget->allocate(
                $alp,
                $year,
                $data['amount'],
                $data['reference_no'] ?? null,
                $data['remarks'] ?? null,
            );
        } catch (BudgetException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('allocations.show', $allocation)
            ->with('status', 'Peruntukan awal ditetapkan untuk '.$alp->ref_code.'.');
    }

    public function adjustForm(Allocation $allocation): View
    {
        $this->authorize('adjust', $allocation);

        $allocation->load(['alp', 'financialYear']);
        $summary = $this->budget->summaryFor($allocation->alp_id, $allocation->financial_year_id);
        $entitlement = UrsContributionPolicy::enabled()
            ? UrsContributionPolicy::maxAnnualForAlp($allocation->alp, (int) $allocation->financialYear->year)
            : null;

        return view('allocations.adjust', compact('allocation', 'summary', 'entitlement'));
    }

    public function adjustStore(Request $request, Allocation $allocation): RedirectResponse
    {
        $this->authorize('adjust', $allocation);

        $data = $request->validate([
            'direction' => ['required', 'in:increase,decrease'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $delta = Money::of($data['amount']);
        if ($data['direction'] === 'decrease') {
            $delta = $delta->negate();
        }

        try {
            $this->budget->adjust(
                $allocation,
                $delta,
                $data['reference_no'] ?? null,
                $data['remarks'] ?? null,
            );
        } catch (BudgetException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('allocations.show', $allocation)
            ->with('status', 'Pelarasan peruntukan direkodkan.');
    }

    private function resolveYear(Request $request, $years): ?FinancialYear
    {
        if ($request->filled('tahun') || $request->filled('financial_year_id')) {
            $id = $request->integer('tahun') ?: $request->integer('financial_year_id');

            return $years->firstWhere('id', $id) ?? FinancialYear::find($id);
        }

        return $years->firstWhere('is_active', true) ?? $years->first();
    }
}
