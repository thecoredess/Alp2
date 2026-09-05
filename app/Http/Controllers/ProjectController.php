<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationType;
use App\Enums\ProjectStatus;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Models\Project;
use App\Services\Project\ProjectClosureService;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectFinancialService;
use App\Services\Project\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projects,
        private readonly ProjectClosureService $closure,
        private readonly ProjectFinancialService $financial,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);
        abort_if($request->user()->alp_id === null, 403, 'Akaun anda tidak dikaitkan dengan mana-mana ALP.');

        $projects = $this->filtered($request)->where('alp_id', $request->user()->alp_id)
            ->with(['alp', 'financialYear'])->latest()->paginate(15)->withQueryString();

        return view('projects.index', ['projects' => $projects, 'scopeAll' => false] + $this->filterOptions());
    }

    public function all(Request $request): View
    {
        abort_unless($request->user()->can('projects.view_all'), 403);

        $projects = $this->filtered($request)
            ->when($request->filled('alp'), fn ($q) => $q->where('alp_id', $request->integer('alp')))
            ->with(['alp', 'financialYear'])->latest()->paginate(15)->withQueryString();

        return view('projects.index', [
            'projects' => $projects, 'scopeAll' => true, 'alps' => Alp::orderBy('ref_code')->get(),
        ] + $this->filterOptions());
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'alp', 'financialYear', 'application', 'milestones', 'expenses.maker',
            'progressHistories.updatedBy', 'statusHistories.changedBy', 'report', 'documents.uploader',
            'budgetTransactions',
        ]);

        return view('projects.show', [
            'project' => $project,
            'finance' => $this->financial->summary($project),
            'closureDocuments' => $project->documents->where('category', 'closure_evidence'),
            'missingClosureDocuments' => $this->closure->missingClosureDocuments($project),
        ]);
    }

    public function start(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        return $this->run(fn () => $this->projects->start($project, request()->user()), $project, 'Projek dimulakan.');
    }

    public function updateProgress(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('progress', $project);
        $validated = $request->validate([
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:in_progress,delayed'],
        ]);

        return $this->run(fn () => $this->projects->updateProgress(
            $project, $request->user(), (int) $validated['progress_percent'],
            $validated['remarks'] ?? null,
            isset($validated['status']) ? ProjectStatus::from($validated['status']) : null,
        ), $project, 'Kemajuan dikemas kini.');
    }

    public function complete(Project $project): RedirectResponse
    {
        $this->authorize('complete', $project);

        return $this->run(fn () => $this->projects->complete($project, request()->user()), $project, 'Projek ditandakan Selesai.');
    }

    public function close(Project $project): RedirectResponse
    {
        $this->authorize('close', $project);

        return $this->run(fn () => $this->closure->close($project, request()->user()), $project, 'Projek berjaya ditutup.');
    }

    public function cancel(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('close', $project);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        return $this->run(fn () => $this->closure->cancel($project, $request->user(), $validated['reason']), $project, 'Projek dibatalkan.');
    }

    private function run(callable $action, Project $project, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('projects.show', $project)->with('status', $success);
    }

    private function filtered(Request $request)
    {
        return Project::query()
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('project_type', $request->string('jenis')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($s) => $s
                ->where('project_number', 'like', '%'.$request->string('cari').'%')
                ->orWhere('project_name', 'like', '%'.$request->string('cari').'%')));
    }

    private function filterOptions(): array
    {
        return [
            'years' => FinancialYear::orderByDesc('year')->get(),
            'typeOptions' => ApplicationType::options(),
            'statusOptions' => collect(ProjectStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all(),
        ];
    }
}
