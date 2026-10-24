<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Http\Requests\ApplicationCreateRequest;
use App\Http\Requests\ApplicationShellDraftRequest;
use App\Services\Application\ApplicationAssociationInviteService;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationNumberGenerator;
use App\Services\Application\ApplicationReportCardService;
use App\Services\Application\ApplicationTimelineService;
use App\Services\Application\RecipientRegistry;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use App\Services\Documents\ApprovalLetterService;
use App\Services\Documents\AssociationDocumentGuideService;
use App\Services\Documents\BorangPenyaluranPdfService;
use App\Services\Reports\ApplicationReportService;
use App\Support\ApplicationAmountValidator;
use App\Support\Tbl10BudgetSnapshot;
use App\Support\UrsContributionPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        private readonly RecipientRegistry $recipients,
        private readonly ApprovalLetterService $approvalLetters,
        private readonly AssociationDocumentGuideService $documentGuide,
        private readonly ApplicationReportService $applicationReports,
        private readonly BorangPenyaluranPdfService $borangPdf,
        private readonly ApplicationAssociationInviteService $associationInvites,
    ) {}

    /** Permohonan Saya (permohonan ALP pengguna). */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);
        abort_if($request->user()->alp_id === null, 403, 'Akaun anda tidak dikaitkan dengan mana-mana ALP.');

        $statusOptions = ApplicationReportService::statusFilterOptionsForAlpApplications();

        $applications = $this->filteredQuery(
            $request,
            useOperationalStatus: true,
            allowedStatusOptions: $statusOptions,
        )
            ->where('alp_id', $request->user()->alp_id)
            ->with(['financialYear', 'alp', 'approvals', 'reportCardReviews'])
            ->orderByRaw('COALESCE(submitted_at, created_at) ASC')
            ->paginate(10)
            ->withQueryString();

        return view('applications.index', [
            'applications' => $applications,
            'scopeAll' => false,
            'usesOperationalStatus' => true,
        ] + $this->filterOptions($request, scopeAll: false, statusOptions: $statusOptions));
    }

    /** Semua Permohonan (staf DBKL berkuasa). */
    public function all(Request $request): View
    {
        $this->authorize('viewAll', Application::class);

        $applications = $this->filteredQuery($request, useOperationalStatus: true)
            ->when($request->filled('alp'), fn ($q) => $q->where('alp_id', $request->integer('alp')))
            ->with(['financialYear', 'alp', 'approvals', 'reportCardReviews'])
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('applications.index', [
            'applications' => $applications,
            'scopeAll' => true,
            'usesOperationalStatus' => true,
            'alps' => Alp::orderBy('ref_code')->get(),
        ] + $this->filterOptions($request, scopeAll: true));
    }

    /** Borang mula permohonan baharu (Langkah 1). */
    public function create(Request $request): View
    {
        $this->authorize('create', Application::class);
        $activeYear = FinancialYear::active();
        $user = $request->user();
        $onBehalf = $user->canCreateApplicationOnBehalf();

        $alp = $onBehalf
            ? ($request->filled('alp') || old('alp_id')
                ? Alp::find($request->integer('alp') ?: (int) old('alp_id'))
                : null)
            : $user->alp;

        $programDateMin = $user->canWaiveProgramLeadTime()
            ? now()->toDateString()
            : UrsContributionPolicy::minimumProgramDate()->format('Y-m-d');

        $amountLimits = null;
        if ($activeYear) {
            if ($alp) {
                $amountLimits = ApplicationAmountValidator::limitsFor(
                    $alp,
                    $activeYear,
                    forceMaxPerApplication: $onBehalf,
                );
            } elseif ($onBehalf) {
                // Admin JP: paparan amaran RM3,000 terus seperti modul ALP, walaupun ALP belum dipilih.
                $amountLimits = ApplicationAmountValidator::limitsBaseline(forceMaxPerApplication: true);
            }
        }

        return view('applications.create', [
            'activeYear' => $activeYear,
            'alp' => $alp,
            'alps' => $onBehalf ? Alp::orderBy('ref_code')->get() : collect(),
            'onBehalf' => $onBehalf,
            'amountLimits' => $amountLimits,
            'programDateMin' => $programDateMin,
            'waiveProgramLeadTime' => $user->canWaiveProgramLeadTime(),
        ]);
    }

    /** Cadangan nama persatuan (permohonan lepas ALP yang sama) — JSON untuk borang. */
    public function suggestRecipients(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Application::class);

        $alpId = $this->resolveAlpIdForRecipientLookup($request);
        if ($alpId === null) {
            return response()->json(['data' => []]);
        }

        $q = $request->string('q')->trim()->toString();
        if (mb_strlen($q) > 100) {
            $q = mb_substr($q, 0, 100);
        }

        return response()->json([
            'data' => $this->recipients->suggestionsForAlp($alpId, $q !== '' ? $q : null),
        ]);
    }

    /** Semak No. ROS berbanding rekod permohonan lepas (nama persatuan sama). */
    public function checkPersatuanRos(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Application::class);

        $alpId = $this->resolveAlpIdForRecipientLookup($request);
        if ($alpId === null) {
            return response()->json(['mismatch' => false]);
        }

        $name = $request->string('name')->trim()->toString();
        $ros = $request->string('ros')->trim()->toString();
        if (mb_strlen($name) > 200) {
            $name = mb_substr($name, 0, 200);
        }
        if (mb_strlen($ros) > 80) {
            $ros = mb_substr($ros, 0, 80);
        }

        return response()->json(
            $this->recipients->rosMismatchForPersatuan($alpId, $name, $ros),
        );
    }

    /** Cipta draf Borang Penyaluran, kemudian lampiran senarai semak. */
    public function store(ApplicationCreateRequest $request, ApplicationNumberGenerator $numbers): RedirectResponse
    {
        $this->authorize('create', Application::class);

        $year = FinancialYear::active();
        abort_if($year === null, 422, 'Tiada tahun kewangan aktif.');

        $user = $request->user();
        $data = $request->validated();
        $alpId = $user->canCreateApplicationOnBehalf()
            ? (int) $data['alp_id']
            : (int) $user->alp_id;
        abort_if($alpId <= 0, 422, 'ALP tidak sah.');

        $application = DB::transaction(function () use ($data, $numbers, $year, $user, $alpId) {
            $number = $numbers->next(ApplicationType::SUMBANGAN, $year->year);

            return Application::create([
                'application_number' => $number,
                'financial_year_id' => $year->id,
                'alp_id' => $alpId,
                'application_type' => ApplicationType::SUMBANGAN,
                ...\App\Support\KertasKerjaGuide::normalizedAttributes($data),
                'recipient_name' => $data['recipient_name'],
                'recipient_ros_number' => $data['recipient_ros_number'],
                'program_date' => $data['program_date'],
                'program_category' => $data['program_category'],
                'recipient_bank_account' => $data['recipient_bank_account'],
                'recipient_address' => $data['recipient_address'],
                'requested_amount' => number_format((float) $data['requested_amount'], 2, '.', ''),
                'status' => ApplicationStatus::DRAFT,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });

        $this->recipients->syncFromApplication($application);

        $this->audit->log('APPLICATION_CREATED', $application, null, [
            'application_number' => $application->application_number,
            'amount' => $application->requested_amount,
            'on_behalf' => $user->canCreateApplicationOnBehalf(),
        ]);

        $nextMsg = $user->canCreateApplicationOnBehalf()
            ? 'Borang disimpan. Sila muat naik lampiran, kemudian hantar kepada Pegawai JP.'
            : 'Borang disimpan. Sila muat naik lampiran senarai semak, kemudian hantar kepada Jabatan Pentadbiran.';

        return redirect()->route('applications.wizard.dokumen', $application)
            ->with('status', $nextMsg);
    }

    /** Draf kosong + pautan persatuan (7 hari) — tiada medan borang wajib diisi ALP. */
    public function storeShellDraft(ApplicationShellDraftRequest $request, ApplicationNumberGenerator $numbers): RedirectResponse
    {
        $this->authorize('create', Application::class);

        $year = FinancialYear::active();
        abort_if($year === null, 422, 'Tiada tahun kewangan aktif.');

        $user = $request->user();
        $data = $request->validated();
        $alpId = $user->canCreateApplicationOnBehalf()
            ? (int) $data['alp_id']
            : (int) $user->alp_id;
        abort_if($alpId <= 0, 422, 'Sila pilih ALP.');

        $application = DB::transaction(function () use ($numbers, $year, $user, $alpId) {
            $number = $numbers->next(ApplicationType::SUMBANGAN, $year->year);

            return Application::create([
                'application_number' => $number,
                'financial_year_id' => $year->id,
                'alp_id' => $alpId,
                'application_type' => ApplicationType::SUMBANGAN,
                'purpose' => '',
                'requested_amount' => '0.00',
                'status' => ApplicationStatus::DRAFT,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });

        $this->audit->log('APPLICATION_CREATED', $application, null, [
            'application_number' => $application->application_number,
            'shell_draft' => true,
        ]);

        $inviteUrl = $this->associationInvites->issue($application, $user);

        return redirect()->route('applications.wizard.dokumen', $application)
            ->with([
                'status' => 'Draf kosong dicipta. Salin pautan di bawah untuk persatuan isi borang (7 hari). Selepas persatuan hantar, semak borang di Langkah 1.',
                'association_invite_url' => $inviteUrl,
            ]);
    }

    /** Halaman butiran permohonan (baca sahaja untuk yang telah dihantar). */
    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'financialYear', 'alp', 'documents.uploader',
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

        $user = auth()->user();
        $isAlpView = $user->alp_id === $application->alp_id && ! $user->can('applications.view_all');

        return view('applications.show', [
            'application' => $application,
            'ledgerAvailable' => $budgetSummary->available(),
            'pending' => $pending,
            'tbl10Snapshot' => $tbl10,
            'activeRevision' => $activeRevision,
            'jpIncomplete' => \App\Support\JpReviewChecklist::latestIncompleteFor($application),
            'timelineStages' => $this->timeline->stages($application, $isAlpView),
            'timelineKpi' => $isAlpView ? null : $this->timeline->kpi($application),
            'isAlpView' => $isAlpView,
            'reportCardDue' => $this->reportCards->dueDate($application),
            'reportCardOverdue' => $this->reportCards->isOverdue($application),
            'hasReportCard' => $this->reportCards->hasReportCard($application),
            'reportCardDraft' => $this->reportCards->draftDocument($application),
        ]);
    }

    /** Surat pemakluman keputusan (template DBKL — LULUS / TIDAK LULUS). */
    public function letter(Application $application): View
    {
        $this->authorize('view', $application);
        $this->assertLetterAvailable($application);

        return $this->approvalLetters->html($application);
    }

    /** Muat turun surat pemakluman sebagai PDF. */
    public function letterPdf(Application $application): Response
    {
        $this->authorize('view', $application);
        $this->assertLetterAvailable($application);

        return $this->approvalLetters->pdf($application);
    }

    /** Muat turun template Format Laporan Program ALP. */
    public function reportTemplatePdf(Application $application): Response
    {
        $this->authorize('view', $application);

        $application->loadMissing('financialYear');
        $year = (int) ($application->financialYear?->year ?? now()->year);
        $pdf = $this->documentGuide->generateReportCardTemplate($year);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->documentGuide->reportCardTemplateFilename($year).'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function assertLetterAvailable(Application $application): void
    {
        abort_unless(
            in_array($application->status, [ApplicationStatus::APPROVED, ApplicationStatus::REJECTED], true),
            403,
            'Surat pemakluman hanya untuk permohonan yang telah diluluskan atau ditolak.'
        );
    }

    /** Borang Penyaluran Sumbangan (BR-020 / TBL-10). */
    public function borang(Application $application): View
    {
        $this->authorize('view', $application);

        return view('applications.borang', $this->borangViewData($application));
    }

    /** Borang Penyaluran + lampiran dimuat naik (satu PDF). */
    public function borangPdf(Application $application): Response
    {
        $this->authorize('view', $application);

        $data = $this->borangViewData($application);
        $pdf = $this->borangPdf->generate($data['application'], $data['tbl10']);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->borangPdf->filename($application).'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /** @return array{application: Application, tbl10: Tbl10BudgetSnapshot, templates: array<string, string>, logoSrc: string} */
    private function borangViewData(Application $application): array
    {
        $application->load([
            'financialYear', 'alp', 'documents',
            'reviews.reviewer', 'approvals.approver', 'approvals.approvalLevel',
        ]);

        $summary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $pending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id);
        $tbl10 = Tbl10BudgetSnapshot::from($application, $summary, $pending);

        return $this->borangPdf->viewData($application, $tbl10);
    }

    // ── Bantuan ─────────────────────────────────────────────────

    private function resolveAlpIdForRecipientLookup(Request $request): ?int
    {
        $user = $request->user();
        $onBehalf = $user->canCreateApplicationOnBehalf();
        $alpId = $onBehalf
            ? $request->integer('alp_id')
            : (int) $user->alp_id;

        if ($alpId <= 0) {
            return null;
        }

        if (! $onBehalf && $alpId !== (int) $user->alp_id) {
            abort(403);
        }

        if ($onBehalf && ! Alp::query()->whereKey($alpId)->exists()) {
            abort(422, 'ALP tidak sah.');
        }

        return $alpId;
    }

    /** @param  array<string, string>|null  $allowedStatusOptions */
    private function filteredQuery(Request $request, bool $useOperationalStatus = false, ?array $allowedStatusOptions = null)
    {
        $query = Application::query()
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('application_type', $request->string('jenis')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $term = $request->string('cari');
                $q->where(fn ($sub) => $sub
                    ->where('application_number', 'like', "%{$term}%")
                    ->orWhere('purpose', 'like', "%{$term}%")
                    ->orWhere('recipient_name', 'like', "%{$term}%"));
            });

        if ($request->filled('status')) {
            if ($useOperationalStatus) {
                $status = ApplicationReportService::sanitizeStatusFilter(
                    $request->string('status')->toString(),
                    $request->user(),
                    $allowedStatusOptions,
                );
                if ($status !== null) {
                    $this->applicationReports->applyStatusFilterToQuery($query, $status);
                }
            } else {
                $query->where('status', $request->string('status'));
            }
        }

        return $query;
    }

    /** @param  array<string, string>|null  $statusOptions */
    private function filterOptions(Request $request, bool $scopeAll, ?array $statusOptions = null): array
    {
        $user = $request->user();

        if ($statusOptions === null) {
            $statusOptions = $scopeAll && ApplicationReportService::usesStaffStatusFilters($user)
                ? ApplicationReportService::statusFilterOptionsForUser($user)
                : collect(ApplicationStatus::cases())
                    ->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
        }

        return [
            'years' => FinancialYear::orderByDesc('year')->get(),
            'statusOptions' => $statusOptions,
        ];
    }
}
