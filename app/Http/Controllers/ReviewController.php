<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Http\Requests\ApplicationReviewRequest;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationReviewService;
use App\Services\Budget\BudgetService;
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

        $application->load(['alp', 'financialYear', 'documents', 'reviews.reviewer']);

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

        return view('reviews.show', [
            'application' => $application,
            'reviewType' => $reviewType,
            'summary' => $summary,
            'otherPending' => $otherPending,
            'thisRequest' => $thisRequest,
            'approvedApplications' => $approvedApplications,
            'pendingApplications' => $pendingApplications,
            'periodSummary' => $periodSummary,
            'checklistItems' => \App\Support\JpReviewChecklist::items(),
            'checklistHints' => \App\Support\JpReviewChecklist::hints($application, $projected),
            'fullJpDecision' => $request->user()->canMakeFullJpReviewDecision(),
        ]);
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
}
