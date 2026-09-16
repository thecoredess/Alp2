<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\DocumentType;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Http\Requests\ApplicationReviewRequest;
use App\Http\Requests\ReviewApplicationInfoRequest;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationReviewService;
use App\Services\Application\RecipientRegistry;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use App\Support\ApplicationAmountValidator;
use App\Support\UrsContributionPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ApplicationReviewService $reviews,
        private readonly ApplicationBudgetService $appBudget,
        private readonly BudgetService $budget,
        private readonly RecipientRegistry $recipients,
        private readonly AuditService $audit,
    ) {}

    public function secretariat(Request $request): View
    {
        return $this->queue($request, ReviewType::SECRETARIAT);
    }

    public function finance(Request $request): View
    {
        abort(404, 'Semakan Kewangan pra-kelulusan telah dinyahaktif (URS v1.2).');
    }

    public function technical(Request $request): View
    {
        abort(404, 'Semakan Teknikal telah dinyahaktif (URS v1.2).');
    }

    /** Admin JP: SUBMITTED · Pegawai JP: UNDER_SECRETARIAT_REVIEW. */
    private function secretariatQueueStatus(\App\Models\User $user): ApplicationStatus
    {
        return $user->canMakeFullJpReviewDecision()
            ? ApplicationStatus::SUBMITTED
            : ApplicationStatus::UNDER_SECRETARIAT_REVIEW;
    }

    private function queue(Request $request, ReviewType $type): View
    {
        abort_unless($request->user()->can($type->permission()), 403);

        $status = match ($type) {
            ReviewType::SECRETARIAT => $this->secretariatQueueStatus($request->user()),
            ReviewType::FINANCE => ApplicationStatus::UNDER_FINANCE_REVIEW,
            ReviewType::TECHNICAL => ApplicationStatus::UNDER_TECHNICAL_REVIEW,
        };

        $applications = Application::query()
            ->where('status', $status->value)
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('alp'), fn ($q) => $q->where('alp_id', $request->integer('alp')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('application_type', $request->string('jenis')))
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($s) => $s
                ->where('application_number', 'like', '%'.$request->string('cari').'%')
                ->orWhere('purpose', 'like', '%'.$request->string('cari').'%')
                ->orWhere('recipient_name', 'like', '%'.$request->string('cari').'%')))
            ->with(['alp', 'financialYear'])
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('reviews.queue', [
            'reviewType' => $type,
            'applications' => $applications,
            'years' => FinancialYear::orderByDesc('year')->get(),
            'alps' => Alp::orderBy('ref_code')->get(),
            'typeOptions' => ApplicationType::options(),
        ]);
    }

    /** Papar borang semakan untuk satu permohonan. */
    public function show(Request $request, Application $application, string $type): View|RedirectResponse
    {
        $reviewType = ReviewType::from($type);
        // URS v1.2: hanya semakan Pegawai JP (secretariat) dalam aliran aktif.
        abort_unless($reviewType === ReviewType::SECRETARIAT, 404, 'Jenis semakan ini telah dinyahaktif.');
        abort_unless($request->user()->can($reviewType->permission()), 403);

        // Permohonan sudah melepasi peringkat semakan — bawa pengguna ke halaman permohonan.
        if ($this->reviews->expectedReviewType($application) !== $reviewType) {
            return redirect()
                ->route('applications.show', $application)
                ->with('error', 'Permohonan '.$application->application_number.' tiada pada peringkat semakan JP. Status semasa: '.$application->status->label().'.');
        }

        // Masih dalam semakan JP, tetapi bukan giliran pengguna ini.
        if ($application->status !== $this->secretariatQueueStatus($request->user())) {
            return redirect()
                ->route('reviews.secretariat')
                ->with('error', $request->user()->canMakeFullJpReviewDecision()
                    ? 'Permohonan '.$application->application_number.' sudah disemak Admin JP dan kini menunggu perakuan Pegawai JP.'
                    : 'Permohonan '.$application->application_number.' masih menunggu semakan Admin JP.');
        }

        $application->load(['alp', 'financialYear', 'documents.uploader', 'reviews.reviewer']);

        $summary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $otherPending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id, $application->id);
        $thisRequest = $application->requestedAmountMoney();
        $projected = $summary->available()->minus($otherPending)->minus($thisRequest);

        $approvedApplications = $this->appBudget->approvedApplications($application->alp_id, $application->financial_year_id);
        $pendingApplications = $this->appBudget->pendingApplications($application->alp_id, $application->financial_year_id, $application->id);

        $periodSummary = null;
        if (UrsContributionPolicy::enabled() && $application->financialYear) {
            $period = UrsContributionPolicy::periodFor(now(), (int) $application->financialYear->year);
            $used = UrsContributionPolicy::periodUsage(
                $application->alp_id,
                $application->financial_year_id,
                $period['start'],
                $period['end'],
            );
            $quota = UrsContributionPolicy::maxPeriodQuota();
            $remaining = $quota->minus($used);
            if ($remaining->isNegative()) {
                $remaining = \App\Support\Money::zero();
            }
            $periodSummary = [
                'label' => $period['label'],
                'quota' => $quota,
                'used' => $used,
                'remaining' => $remaining,
            ];
        }

        $fullJpDecision = $request->user()->canMakeFullJpReviewDecision();

        $crosscheckDocument = $application->documents
            ->first(fn ($d) => $d->document_type === DocumentType::SEMAKAN_SILANG_JKEW);
        $attachmentDocuments = $application->documents
            ->reject(fn ($d) => $d->document_type === DocumentType::SEMAKAN_SILANG_JKEW);

        return view('reviews.show', [
            'application' => $application,
            'reviewType' => $reviewType,
            'crosscheckDocument' => $crosscheckDocument,
            'attachmentDocuments' => $attachmentDocuments,
            'summary' => $summary,
            'otherPending' => $otherPending,
            'thisRequest' => $thisRequest,
            'approvedApplications' => $approvedApplications,
            'pendingApplications' => $pendingApplications,
            'periodSummary' => $periodSummary,
            'checklistItems' => \App\Support\JpReviewChecklist::items(),
            'checklistHints' => \App\Support\JpReviewChecklist::hints($application, $projected),
            'fullJpDecision' => $fullJpDecision,
            'canEditBorang' => $fullJpDecision && $request->user()->can('updateBorangDuringReview', $application),
            'amountLimits' => $fullJpDecision
                ? ApplicationAmountValidator::limitsFor(
                    $application->alp,
                    $application->financialYear,
                    $application->id,
                    forceMaxPerApplication: true,
                )
                : null,
            ...($fullJpDecision ? $this->programDateContext($request) : []),
        ]);
    }

    /** Kemaskini Borang Penyaluran semasa semakan Admin JP. */
    public function updateBorang(ReviewApplicationInfoRequest $request, Application $application, string $type): RedirectResponse
    {
        $reviewType = ReviewType::from($type);
        abort_unless($reviewType === ReviewType::SECRETARIAT, 404, 'Jenis semakan ini telah dinyahaktif.');
        abort_unless($request->user()->can($reviewType->permission()), 403);

        if ($this->reviews->expectedReviewType($application) !== $reviewType
            || $application->status !== ApplicationStatus::SUBMITTED) {
            return redirect()
                ->route('applications.show', $application)
                ->with('error', 'Borang hanya boleh dikemaskini semasa giliran semakan Admin JP.');
        }

        $data = $request->validated();
        $application->update([
            'purpose' => $data['purpose'],
            'recipient_name' => $data['recipient_name'],
            'recipient_ros_number' => $data['recipient_ros_number'],
            'program_date' => $data['program_date'],
            'program_category' => $data['program_category'],
            'recipient_bank_account' => $data['recipient_bank_account'],
            'recipient_address' => $data['recipient_address'],
            'requested_amount' => number_format((float) $data['requested_amount'], 2, '.', ''),
            'updated_by' => $request->user()->id,
        ]);
        $this->recipients->syncFromApplication($application->fresh());
        $this->audit->log('APPLICATION_UPDATED', $application, null, ['step' => 'borang', 'during_review' => true]);

        return redirect()
            ->route('reviews.show', [$application, $reviewType->value])
            ->with('status', 'Borang Penyaluran dikemas kini.');
    }

    /** Rekod keputusan semakan. */
    public function store(ApplicationReviewRequest $request, Application $application, string $type): RedirectResponse
    {
        $reviewType = ReviewType::from($type);
        abort_unless($reviewType === ReviewType::SECRETARIAT, 404, 'Jenis semakan ini telah dinyahaktif.');
        abort_unless($request->user()->can($reviewType->permission()), 403);

        $validated = $request->validated();

        try {
            $this->reviews->review(
                $application,
                $reviewType,
                $request->user(),
                ReviewDecision::from($validated['decision']),
                $validated['comments'] ?? null,
                $validated['checklist'] ?? null,
            );
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('reviews.secretariat')
            ->with('status', 'Semakan direkodkan.');
    }

    /** @return array{programDateMin: string, waiveProgramLeadTime: bool} */
    private function programDateContext(Request $request): array
    {
        $waive = $request->user()?->canWaiveProgramLeadTime() ?? false;

        return [
            'programDateMin' => $waive
                ? now()->toDateString()
                : UrsContributionPolicy::minimumProgramDate()->format('Y-m-d'),
            'waiveProgramLeadTime' => $waive,
        ];
    }
}
