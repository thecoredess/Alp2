<?php

namespace App\Http\Controllers;

use App\Enums\ProjectExpenseStatus;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectExpenseService;
use App\Services\Project\ProjectExpenseVerificationService;
use App\Services\Project\ProjectFinancialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectExpenseController extends Controller
{
    public function __construct(
        private readonly ProjectExpenseService $maker,
        private readonly ProjectExpenseVerificationService $checker,
        private readonly ProjectFinancialService $financial,
    ) {}

    /** Giliran pengesahan (checker). */
    public function verificationQueue(Request $request): View
    {
        abort_unless($request->user()->can('expenses.verify'), 403);

        $expenses = ProjectExpense::query()
            ->where('status', ProjectExpenseStatus::PENDING_VERIFICATION->value)
            ->with(['project.alp', 'maker'])->latest('submitted_at')->paginate(15);

        return view('expenses.queue', compact('expenses'));
    }

    public function create(Request $request, Project $project): View
    {
        abort_unless($request->user()->can('expenses.create'), 403);

        return view('expenses.create', ['project' => $project, 'finance' => $this->financial->summary($project)]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->can('expenses.create'), 403);
        $validated = $this->validateExpense($request);

        try {
            $expense = $this->maker->createDraft($project, $request->user(), $validated);
        } catch (ProjectException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('expenses.show', $expense)->with('status', 'Perbelanjaan dicipta sebagai draf.');
    }

    public function show(ProjectExpense $expense): View
    {
        $this->authorize('view', $expense);
        $expense->load(['project.alp', 'maker', 'verifier', 'histories.changedBy', 'transaction', 'documents']);

        return view('expenses.show', [
            'expense' => $expense,
            'finance' => $this->financial->summary($expense->project),
            'isMaker' => $expense->created_by === request()->user()->id || $expense->submitted_by === request()->user()->id,
        ]);
    }

    public function edit(ProjectExpense $expense): View
    {
        $this->authorize('update', $expense);

        return view('expenses.edit', ['expense' => $expense, 'project' => $expense->project]);
    }

    public function update(Request $request, ProjectExpense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);
        $validated = $this->validateExpense($request);

        try {
            $this->maker->update($expense, $request->user(), $validated);
        } catch (ProjectException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('expenses.show', $expense)->with('status', 'Perbelanjaan dikemas kini.');
    }

    public function submit(Request $request, ProjectExpense $expense): RedirectResponse
    {
        $this->authorize('submit', $expense);

        return $this->act(fn () => $this->maker->submit($expense, $request->user()), $expense, 'Perbelanjaan dihantar untuk pengesahan.');
    }

    public function verify(Request $request, ProjectExpense $expense): RedirectResponse
    {
        $this->authorize('verify', $expense);

        return $this->act(fn () => $this->checker->verify($expense, $request->user()), $expense, 'Perbelanjaan disahkan & diposkan ke ledger.');
    }

    public function reject(Request $request, ProjectExpense $expense): RedirectResponse
    {
        $this->authorize('reject', $expense);
        $v = $request->validate(['comments' => ['required', 'string', 'max:2000']], ['comments.required' => 'Sila nyatakan sebab penolakan.']);

        return $this->act(fn () => $this->checker->reject($expense, $request->user(), $v['comments']), $expense, 'Perbelanjaan ditolak.');
    }

    public function returnForRevision(Request $request, ProjectExpense $expense): RedirectResponse
    {
        $this->authorize('returnForRevision', $expense);
        $v = $request->validate(['comments' => ['required', 'string', 'max:2000']], ['comments.required' => 'Sila nyatakan sebab pembetulan.']);

        return $this->act(fn () => $this->checker->returnForRevision($expense, $request->user(), $v['comments']), $expense, 'Perbelanjaan dikembalikan untuk pembetulan.');
    }

    private function act(callable $action, ProjectExpense $expense, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('expenses.show', $expense)->with('status', $success);
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_date' => ['required', 'date'],
            'reference_number' => ['required', 'string', 'max:100'],
            'payee' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99', 'decimal:0,2'],
        ], ['amount.gt' => 'Jumlah mesti lebih daripada sifar.']);
    }
}
