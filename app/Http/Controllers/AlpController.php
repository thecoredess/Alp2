<?php

namespace App\Http\Controllers;

use App\Enums\AlpStatus;
use App\Http\Requests\AlpRequest;
use App\Models\Alp;
use App\Models\Allocation;
use App\Models\FinancialYear;
use App\Services\Budget\BudgetService;
use App\Services\Budget\BudgetSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlpController extends Controller
{
    public function __construct(private readonly BudgetService $budget) {}

    public function index(Request $request): View
    {
        $this->authorize('alps.view');

        $alps = Alp::query()
            ->when($request->string('cari')->isNotEmpty(), function ($q) use ($request) {
                $term = $request->string('cari');
                $q->where(fn ($sub) => $sub
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('ref_code', 'like', "%{$term}%")
                    ->orWhere('portfolio_zone', 'like', "%{$term}%"));
            })
            ->orderBy('ref_code')
            ->paginate(15)
            ->withQueryString();

        return view('alps.index', compact('alps'));
    }

    public function create(): View
    {
        $this->authorize('alps.create');

        return view('alps.create');
    }

    public function store(AlpRequest $request): RedirectResponse
    {
        $this->authorize('alps.create');

        $alp = Alp::create([
            ...$request->validated(),
            'status' => AlpStatus::ACTIVE,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('alps.show', $alp)
            ->with('status', 'Profil ALP berjaya dicipta.');
    }

    public function show(Alp $alp): View
    {
        $this->authorize('alps.view');

        $alp->loadCount([
            'users',
            'applications',
            'projects as active_projects_count' => fn ($q) => $q->whereIn('status', [
                \App\Enums\ProjectStatus::NOT_STARTED->value,
                \App\Enums\ProjectStatus::IN_PROGRESS->value,
                \App\Enums\ProjectStatus::DELAYED->value,
            ]),
        ]);

        $activeYear = FinancialYear::active();
        $summary = $activeYear
            ? $this->budget->summaryFor($alp->id, $activeYear->id)
            : new BudgetSummary();
        $allocation = $activeYear
            ? Allocation::where('alp_id', $alp->id)->where('financial_year_id', $activeYear->id)->first()
            : null;

        $pending = $activeYear
            ? app(\App\Services\Application\ApplicationBudgetService::class)->pendingRequest($alp->id, $activeYear->id)
            : \App\Support\Money::zero();
        $projected = $summary->available()->minus($pending);

        return view('alps.show', compact('alp', 'activeYear', 'summary', 'allocation', 'pending', 'projected'));
    }

    public function edit(Alp $alp): View
    {
        $this->authorize('alps.update');

        return view('alps.edit', compact('alp'));
    }

    public function update(AlpRequest $request, Alp $alp): RedirectResponse
    {
        $this->authorize('alps.update');

        $alp->update($request->validated());

        return redirect()->route('alps.show', $alp)
            ->with('status', 'Profil ALP berjaya dikemas kini.');
    }

    public function deactivate(Alp $alp): RedirectResponse
    {
        $this->authorize('alps.deactivate');

        $alp->update(['status' => AlpStatus::INACTIVE]);

        return back()->with('status', "ALP {$alp->name} telah dinyahaktifkan.");
    }

    public function activate(Alp $alp): RedirectResponse
    {
        $this->authorize('alps.deactivate');

        $alp->update(['status' => AlpStatus::ACTIVE]);

        return back()->with('status', "ALP {$alp->name} telah diaktifkan semula.");
    }
}
