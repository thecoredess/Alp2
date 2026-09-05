<?php

namespace App\Http\Controllers;

use App\Enums\BudgetRequestStatus;
use App\Enums\BudgetRequestType;
use App\Http\Requests\BudgetRequestFormRequest;
use App\Models\Alp;
use App\Models\BudgetRequest;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Budget\BudgetException;
use App\Services\Budget\BudgetRequestService;
use App\Services\Budget\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * MAKER — cadangan bajet (peruntukan awal & pelarasan). Tiada kesan ledger.
 */
class BudgetRequestController extends Controller
{
    public function __construct(
        private readonly BudgetRequestService $requests,
        private readonly BudgetService $budget,
        private readonly ApplicationBudgetService $appBudget,
    ) {}

    private const MAKER_PERMS = [
        'allocations.request.create', 'adjustments.request.create',
    ];

    /** Cadangan Saya. */
    public function index(Request $request): View
    {
        abort_unless($request->user()->canAny(self::MAKER_PERMS), 403);

        $requests = BudgetRequest::query()
            ->where('created_by', $request->user()->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['alp', 'financialYear'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('budget-requests.index', [
            'requests' => $requests,
            'statusOptions' => collect(BudgetRequestStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->canAny(self::MAKER_PERMS), 403);

        // Pra-pilih kategori dari sidebar (allocation / adjustment).
        $kategori = $request->string('kategori')->toString();

        return view('budget-requests.create', [
            'types' => BudgetRequestType::options(),
            'preselect' => $kategori === 'adjustment' ? BudgetRequestType::ALLOCATION_INCREASE->value
                : ($kategori === 'allocation' ? BudgetRequestType::INITIAL_ALLOCATION->value : ''),
            'alps' => Alp::where('status', 'active')->orderBy('ref_code')->get(),
            'years' => FinancialYear::whereIn('status', ['active', 'open'])->orderByDesc('year')->get(),
        ]);
    }

    public function store(BudgetRequestFormRequest $request): RedirectResponse
    {
        $type = BudgetRequestType::from($request->validated('request_type'));
        abort_unless($request->user()->can($type->permission('request.create')), 403);

        try {
            $budgetRequest = $this->requests->createDraft($request->user(), $request->validated());
        } catch (BudgetException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('budget-requests.show', $budgetRequest)
            ->with('status', 'Cadangan bajet dicipta sebagai draf.');
    }

    public function show(BudgetRequest $budgetRequest): View
    {
        $this->authorize('view', $budgetRequest);

        $budgetRequest->load(['alp', 'financialYear', 'maker', 'submitter', 'approver', 'histories.changedBy', 'transaction']);

        return view('budget-requests.show', [
            'request' => $budgetRequest,
            'position' => $this->position($budgetRequest),
        ]);
    }

    public function edit(BudgetRequest $budgetRequest): View
    {
        $this->authorize('update', $budgetRequest);

        return view('budget-requests.edit', [
            'request' => $budgetRequest,
            'position' => $this->position($budgetRequest),
        ]);
    }

    public function update(BudgetRequestFormRequest $request, BudgetRequest $budgetRequest): RedirectResponse
    {
        $this->authorize('update', $budgetRequest);

        try {
            $this->requests->update($budgetRequest, $request->user(), $request->validated());
        } catch (BudgetException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('budget-requests.show', $budgetRequest)->with('status', 'Cadangan dikemas kini.');
    }

    public function submit(Request $request, BudgetRequest $budgetRequest): RedirectResponse
    {
        $this->authorize('submit', $budgetRequest);

        try {
            $this->requests->submit($budgetRequest, $request->user());
        } catch (BudgetException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('budget-requests.show', $budgetRequest)
            ->with('status', 'Cadangan dihantar untuk kelulusan.');
    }

    /** Kedudukan kewangan semasa ALP+tahun (untuk pratonton). */
    private function position(BudgetRequest $request): array
    {
        $summary = $this->budget->summaryFor($request->alp_id, $request->financial_year_id);
        $pending = $this->appBudget->pendingRequest($request->alp_id, $request->financial_year_id);
        $available = $summary->available();
        $delta = $request->request_type->ledgerDelta($request->amountMoney());
        $availableAfter = $available->plus($delta);
        $projectedAfter = $availableAfter->minus($pending);

        return [
            'summary' => $summary,
            'available' => $available,
            'pending' => $pending,
            'projected' => $available->minus($pending),
            'available_after' => $availableAfter,
            'projected_after' => $projectedAfter,
        ];
    }
}
