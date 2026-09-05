<?php

namespace App\Http\Controllers;

use App\Enums\ProjectDocumentType;
use App\Enums\RefundStatus;
use App\Models\ProjectExpense;
use App\Models\ProjectExpenseRefund;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectRefundService;
use App\Services\Project\ProjectRefundVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectRefundController extends Controller
{
    public function __construct(
        private readonly ProjectRefundService $maker,
        private readonly ProjectRefundVerificationService $checker,
    ) {}

    /** Giliran pengesahan refund (checker). */
    public function verificationQueue(Request $request): View
    {
        abort_unless($request->user()->can('refunds.verify'), 403);

        $refunds = ProjectExpenseRefund::query()
            ->where('status', RefundStatus::PENDING_VERIFICATION->value)
            ->with(['project.alp', 'expense', 'maker'])->latest('submitted_at')->paginate(15);

        return view('refunds.queue', compact('refunds'));
    }

    public function create(Request $request, ProjectExpense $expense): View
    {
        abort_unless($request->user()->can('refunds.create'), 403);
        $expense->loadMissing('project.alp');

        return view('refunds.create', [
            'expense' => $expense,
            'refundable' => $expense->refundableRemaining(),
        ]);
    }

    public function store(Request $request, ProjectExpense $expense): RedirectResponse
    {
        abort_unless($request->user()->can('refunds.create'), 403);
        $validated = $this->validateRefund($request);

        try {
            $refund = $this->maker->createDraft($expense, $request->user(), $validated);
        } catch (ProjectException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('refunds.show', $refund)->with('status', 'Refund dicipta sebagai draf. Sila muat naik bukti.');
    }

    public function show(ProjectExpenseRefund $refund): View
    {
        $this->authorize('view', $refund);
        $refund->load(['project.alp', 'expense', 'maker', 'verifier', 'histories.changedBy', 'transaction', 'documents.uploader']);

        return view('refunds.show', [
            'refund' => $refund,
            'documentTypes' => ProjectDocumentType::forCategory('refund_evidence'),
            'isMaker' => $refund->created_by === request()->user()->id || $refund->submitted_by === request()->user()->id,
        ]);
    }

    public function edit(ProjectExpenseRefund $refund): View
    {
        $this->authorize('update', $refund);

        return view('refunds.edit', [
            'refund' => $refund,
            'refundable' => $refund->expense->refundableRemaining()->plus($refund->amountMoney()),
        ]);
    }

    public function update(Request $request, ProjectExpenseRefund $refund): RedirectResponse
    {
        $this->authorize('update', $refund);
        $validated = $this->validateRefund($request);

        try {
            $this->maker->update($refund, $request->user(), $validated);
        } catch (ProjectException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('refunds.show', $refund)->with('status', 'Refund dikemas kini.');
    }

    public function submit(Request $request, ProjectExpenseRefund $refund): RedirectResponse
    {
        $this->authorize('submit', $refund);

        return $this->act(fn () => $this->maker->submit($refund, $request->user()), $refund, 'Refund dihantar untuk pengesahan.');
    }

    public function verify(Request $request, ProjectExpenseRefund $refund): RedirectResponse
    {
        $this->authorize('verify', $refund);

        return $this->act(fn () => $this->checker->verify($refund, $request->user()), $refund, 'Refund disahkan & diposkan ke ledger.');
    }

    public function reject(Request $request, ProjectExpenseRefund $refund): RedirectResponse
    {
        $this->authorize('reject', $refund);
        $v = $request->validate(['comments' => ['required', 'string', 'max:2000']], ['comments.required' => 'Sila nyatakan sebab penolakan.']);

        return $this->act(fn () => $this->checker->reject($refund, $request->user(), $v['comments']), $refund, 'Refund ditolak.');
    }

    public function returnForRevision(Request $request, ProjectExpenseRefund $refund): RedirectResponse
    {
        $this->authorize('returnForRevision', $refund);
        $v = $request->validate(['comments' => ['required', 'string', 'max:2000']], ['comments.required' => 'Sila nyatakan sebab pembetulan.']);

        return $this->act(fn () => $this->checker->returnForRevision($refund, $request->user(), $v['comments']), $refund, 'Refund dikembalikan untuk pembetulan.');
    }

    private function act(callable $action, ProjectExpenseRefund $refund, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('refunds.show', $refund)->with('status', $success);
    }

    private function validateRefund(Request $request): array
    {
        return $request->validate([
            'refund_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99', 'decimal:0,2'],
        ], ['amount.gt' => 'Jumlah mesti lebih daripada sifar.', 'reason.required' => 'Sila nyatakan sebab refund.']);
    }
}
