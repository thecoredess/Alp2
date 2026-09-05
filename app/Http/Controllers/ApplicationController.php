<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Http\Requests\ApplicationCreateRequest;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationNumberGenerator;
use App\Services\Application\ApplicationReportCardService;
use App\Services\Application\ApplicationTimelineService;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationBudgetService $appBudget,
        private readonly AuditService $audit,
        private readonly ApplicationTimelineService $timeline,
        private readonly ApplicationReportCardService $reportCards,
        private readonly BudgetService $budget,
    ) {}

    /** Permohonan Saya (permohonan ALP pengguna). */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);
        abort_if($request->user()->alp_id === null, 403, 'Akaun anda tidak dikaitkan dengan mana-mana ALP.');

        $applications = $this->filteredQuery($request)
            ->where('alp_id', $request->user()->alp_id)
            ->with(['financialYear', 'alp'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('applications.index', [
            'applications' => $applications,
            'scopeAll' => false,
        ] + $this->filterOptions());
    }

    /** Semua Permohonan (staf DBKL berkuasa). */
    public function all(Request $request): View
    {
        $this->authorize('viewAll', Application::class);

        $applications = $this->filteredQuery($request)
            ->when($request->filled('alp'), fn ($q) => $q->where('alp_id', $request->integer('alp')))
            ->with(['financialYear', 'alp'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('applications.index', [
            'applications' => $applications,
            'scopeAll' => true,
            'alps' => Alp::orderBy('ref_code')->get(),
        ] + $this->filterOptions());
    }

    /** Borang mula permohonan baharu (Langkah 1). */
    public function create(Request $request): View
    {
        $this->authorize('create', Application::class);
        $activeYear = FinancialYear::active();

        return view('applications.create', [
            'activeYear' => $activeYear,
            'types' => ApplicationType::options(),
        ]);
    }

    /** Cipta draf (Langkah 1 → seterusnya Objektif). */
    public function store(ApplicationCreateRequest $request, ApplicationNumberGenerator $numbers): RedirectResponse
    {
        $this->authorize('create', Application::class);

        $year = FinancialYear::active();
        abort_if($year === null, 422, 'Tiada tahun kewangan aktif.');

        $user = $request->user();
        $type = ApplicationType::from($request->validated('application_type'));

        $application = DB::transaction(function () use ($request, $numbers, $year, $user, $type) {
            $number = $numbers->next($type, $year->year);

            return Application::create([
                'application_number' => $number,
                'financial_year_id' => $year->id,
                'alp_id' => $user->alp_id,
                'application_type' => $type,
                'project_title' => $request->validated('project_title'),
                'project_summary' => $request->validated('project_summary'),
                'location' => $request->validated('location'),
                'proposed_start_date' => $request->validated('proposed_start_date'),
                'proposed_end_date' => $request->validated('proposed_end_date'),
                'status' => ApplicationStatus::DRAFT,
                'requested_amount' => '0.00',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });

        $this->audit->log('APPLICATION_CREATED', $application, null, [
            'application_number' => $application->application_number,
            'type' => $type->value,
        ]);

        return redirect()->route('applications.wizard.maklumat', $application)
            ->with('status', 'Draf dicipta — lengkapkan maklumat penerima (TBL-10): '.$application->application_number);
    }

    /** Halaman butiran permohonan (baca sahaja untuk yang telah dihantar). */
    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'financialYear', 'alp', 'budgetItems', 'documents.uploader',
            'statusHistories.changedBy', 'creator',
            'reviews.reviewer', 'approvals.approver', 'approvals.approvalLevel',
            'revisions.returnedBy', 'commitmentTransaction',
        ]);

        $budgetSummary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $pending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id);
        $tbl10 = \App\Support\Tbl10BudgetSnapshot::from($application, $budgetSummary, $pending);

        // Revisi semasa yang belum dihantar semula (untuk banner pembetulan).
        $activeRevision = $application->status === \App\Enums\ApplicationStatus::REVISION_REQUIRED
            ? $application->revisions->whereNull('resubmitted_at')->last()
            : null;

        return view('applications.show', [
            'application' => $application,
            'ledgerAvailable' => $budgetSummary->available(),
            'pending' => $pending,
            'tbl10Snapshot' => $tbl10,
            'activeRevision' => $activeRevision,
            'timelineStages' => $this->timeline->stages($application),
            'timelineKpi' => $this->timeline->kpi($application),
            'reportCardDue' => $this->reportCards->dueDate($application),
            'reportCardOverdue' => $this->reportCards->isOverdue($application),
            'hasReportCard' => $this->reportCards->hasReportCard($application),
        ]);
    }

    /** Surat / ringkasan kelulusan untuk dicetak (UR-M05-006). */
    public function letter(Application $application): View
    {
        $this->authorize('view', $application);

        abort_unless(
            $application->status === \App\Enums\ApplicationStatus::APPROVED,
            403,
            'Surat kelulusan hanya untuk permohonan yang telah diluluskan.'
        );

        $application->load([
            'financialYear', 'alp', 'approvals.approver', 'approvals.approvalLevel',
            'commitmentTransaction',
        ]);

        return view('applications.letter', [
            'application' => $application,
            'templates' => \App\Support\UrsDocumentTemplates::all(),
        ]);
    }

    /** Borang Penyaluran Sumbangan (BR-020 / TBL-10). */
    public function borang(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'financialYear', 'alp', 'budgetItems', 'documents',
            'reviews.reviewer', 'approvals.approver', 'approvals.approvalLevel',
        ]);

        $summary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $pending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id);
        $tbl10 = \App\Support\Tbl10BudgetSnapshot::from($application, $summary, $pending);

        return view('applications.borang', [
            'application' => $application,
            'tbl10' => $tbl10,
            'templates' => \App\Support\UrsDocumentTemplates::all(),
        ]);
    }

    // ── Bantuan ─────────────────────────────────────────────────

    private function filteredQuery(Request $request)
    {
        return Application::query()
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('application_type', $request->string('jenis')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $term = $request->string('cari');
                $q->where(fn ($sub) => $sub
                    ->where('application_number', 'like', "%{$term}%")
                    ->orWhere('project_title', 'like', "%{$term}%"));
            });
    }

    private function filterOptions(): array
    {
        return [
            'years' => FinancialYear::orderByDesc('year')->get(),
            'typeOptions' => ApplicationType::options(),
            'statusOptions' => collect(ApplicationStatus::cases())
                ->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all(),
        ];
    }
}
