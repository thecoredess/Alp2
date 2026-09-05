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

    /** Peta jenis semakan → status giliran. */
    private const QUEUE_STATUS = [
        'secretariat' => ApplicationStatus::SUBMITTED,
        'finance' => ApplicationStatus::UNDER_FINANCE_REVIEW,
        'technical' => ApplicationStatus::UNDER_TECHNICAL_REVIEW,
    ];

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

    private function queue(Request $request, ReviewType $type): View
    {
        abort_unless($request->user()->can($type->permission()), 403);

        $status = self::QUEUE_STATUS[$type->value];

        $applications = Application::query()
            ->where('status', $status->value)
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('alp'), fn ($q) => $q->where('alp_id', $request->integer('alp')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('application_type', $request->string('jenis')))
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($s) => $s
                ->where('application_number', 'like', '%'.$request->string('cari').'%')
                ->orWhere('project_title', 'like', '%'.$request->string('cari').'%')))
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
    public function show(Request $request, Application $application, string $type): View
    {
        $reviewType = ReviewType::from($type);
        // URS v1.2: hanya semakan Pegawai JP (secretariat) dalam aliran aktif.
        abort_unless($reviewType === ReviewType::SECRETARIAT, 404, 'Jenis semakan ini telah dinyahaktif.');
        abort_unless($request->user()->can($reviewType->permission()), 403);

        $expected = $this->reviews->expectedReviewType($application);
        abort_if($expected !== $reviewType, 404, 'Permohonan tiada pada peringkat semakan ini.');

        $application->load(['alp', 'financialYear', 'budgetItems', 'documents', 'reviews.reviewer']);

        $summary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $otherPending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id, $application->id);
        $thisRequest = $application->requestedAmountMoney();
        $projected = $summary->available()->minus($otherPending)->minus($thisRequest);

        return view('reviews.show', [
            'application' => $application,
            'reviewType' => $reviewType,
            'summary' => $summary,
            'otherPending' => $otherPending,
            'thisRequest' => $thisRequest,
            'checklistItems' => \App\Support\JpReviewChecklist::items(),
            'checklistHints' => \App\Support\JpReviewChecklist::hints($application, $projected),
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
