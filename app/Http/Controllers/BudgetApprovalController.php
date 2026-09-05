<?php

namespace App\Http\Controllers;

use App\Enums\BudgetRequestStatus;
use App\Models\BudgetRequest;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Budget\BudgetException;
use App\Services\Budget\BudgetRequestApprovalService;
use App\Services\Budget\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CHECKER — kelulusan cadangan bajet. Poskan ledger HANYA selepas kelulusan.
 */
class BudgetApprovalController extends Controller
{
    public function __construct(
        private readonly BudgetRequestApprovalService $approvals,
        private readonly BudgetService $budget,
        private readonly ApplicationBudgetService $appBudget,
    ) {}

    private const CHECKER_PERMS = [
        'allocations.approve', 'adjustments.approve',
    ];

    public function queue(Request $request): View
    {
        abort_unless($request->user()->canAny(self::CHECKER_PERMS), 403);

        $requests = BudgetRequest::query()
            ->where('status', BudgetRequestStatus::PENDING_APPROVAL->value)
            ->when($request->filled('jenis'), fn ($q) => $q->where('request_type', $request->string('jenis')))
            ->with(['alp', 'financialYear', 'maker', 'submitter'])
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('budget-approvals.queue', ['requests' => $requests]);
    }

    public function show(BudgetRequest $budgetRequest): View
    {
        $this->authorize('view', $budgetRequest);
        abort_if($budgetRequest->status !== BudgetRequestStatus::PENDING_APPROVAL, 404, 'Cadangan tiada pada peringkat kelulusan.');

        $budgetRequest->load(['alp', 'financialYear', 'maker', 'submitter']);

        $summary = $this->budget->summaryFor($budgetRequest->alp_id, $budgetRequest->financial_year_id);
        $pending = $this->appBudget->pendingRequest($budgetRequest->alp_id, $budgetRequest->financial_year_id);
        $delta = $budgetRequest->request_type->ledgerDelta($budgetRequest->amountMoney());

        return view('budget-approvals.show', [
            'request' => $budgetRequest,
            'summary' => $summary,
            'available' => $summary->available(),
            'pending' => $pending,
            'projected' => $summary->available()->minus($pending),
            'newAllocation' => $summary->allocation->plus($delta),
            'availableAfter' => $summary->available()->plus($delta),
            'projectedAfter' => $summary->available()->plus($delta)->minus($pending),
            'isMaker' => $budgetRequest->created_by === request()->user()->id || $budgetRequest->submitted_by === request()->user()->id,
        ]);
    }

    public function approve(Request $request, BudgetRequest $budgetRequest): RedirectResponse
    {
        $this->authorize('approve', $budgetRequest);
        $request->validate(['comments' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->approvals->approve($budgetRequest, $request->user());
        } catch (BudgetException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('budget-requests.show', $budgetRequest)->with('status', 'Cadangan diluluskan & diposkan ke ledger.');
    }

    public function reject(Request $request, BudgetRequest $budgetRequest): RedirectResponse
    {
        $this->authorize('reject', $budgetRequest);
        $validated = $request->validate(['comments' => ['required', 'string', 'max:2000']], [
            'comments.required' => 'Sila nyatakan sebab penolakan.',
        ]);

        try {
            $this->approvals->reject($budgetRequest, $request->user(), $validated['comments']);
        } catch (BudgetException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('budget-requests.show', $budgetRequest)->with('status', 'Cadangan ditolak.');
    }

    public function returnForRevision(Request $request, BudgetRequest $budgetRequest): RedirectResponse
    {
        $this->authorize('returnForRevision', $budgetRequest);
        $validated = $request->validate(['comments' => ['required', 'string', 'max:2000']], [
            'comments.required' => 'Sila nyatakan sebab pembetulan.',
        ]);

        try {
            $this->approvals->returnForRevision($budgetRequest, $request->user(), $validated['comments']);
        } catch (BudgetException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('budget-requests.show', $budgetRequest)->with('status', 'Cadangan dikembalikan untuk pembetulan.');
    }
}
