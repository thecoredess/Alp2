<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\ReportCardStatus;
use App\Enums\ReviewDecision;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationReportCardService;
use App\Services\Application\ReportCardReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportCardController extends Controller
{
    public function __construct(
        private readonly ApplicationReportCardService $reportCards,
        private readonly ReportCardReviewService $reviews,
    ) {}

    /** Senarai pemantauan JP — status penerimaan report card (UR-M07-003). */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->can('applications.view_all')
            || $request->user()->can('applications.review.secretariat'),
            403
        );

        $yearId = $request->integer('tahun') ?: FinancialYear::active()?->id;
        $statusFilter = $request->string('status')->toString();

        $apps = Application::query()
            ->with(['alp:id,ref_code,name', 'financialYear:id,year'])
            ->where('status', ApplicationStatus::APPROVED->value)
            ->when($yearId, fn ($q) => $q->where('financial_year_id', $yearId))
            ->when($statusFilter === 'missing', fn ($q) => $q->whereNull('report_card_submitted_at'))
            ->when($statusFilter === 'in_review', fn ($q) => $q->whereIn('report_card_status', [
                ReportCardStatus::AWAITING_ADMIN_JP->value,
                ReportCardStatus::AWAITING_PEGAWAI_JP->value,
            ]))
            ->when($statusFilter === 'approved', fn ($q) => $q->where('report_card_status', ReportCardStatus::APPROVED->value))
            ->when($statusFilter === 'returned', fn ($q) => $q->where('report_card_status', ReportCardStatus::RETURNED->value))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('report-cards.index', [
            'applications' => $apps,
            'years' => FinancialYear::orderByDesc('year')->get(),
            'reportCards' => $this->reportCards,
            'canReview' => $request->user()->can('applications.review.secretariat'),
        ]);
    }

    /** Giliran semakan laporan aktiviti (Admin JP / Pegawai JP). */
    public function reviewQueue(Request $request): View
    {
        abort_unless($request->user()->can('applications.review.secretariat'), 403);

        $queueStatus = $this->reviews->queueStatusFor($request->user());
        $yearId = $request->integer('tahun') ?: null;
        $search = $request->string('cari')->trim()->toString();

        $apps = Application::query()
            ->with(['alp:id,ref_code,name', 'financialYear:id,year', 'revisions:id,application_id,snapshot'])
            ->where('status', ApplicationStatus::APPROVED->value)
            ->where('report_card_status', $queueStatus)
            ->when($yearId, fn ($q) => $q->where('financial_year_id', $yearId))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%");
            }))
            ->latest('report_card_submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('report-cards.review-queue', [
            'applications' => $apps,
            'years' => FinancialYear::orderByDesc('year')->get(),
            'queueStatus' => $queueStatus,
            'fullJpDecision' => $request->user()->canMakeFullJpReviewDecision(),
        ]);
    }

    public function reviewShow(Request $request, Application $application): View|RedirectResponse
    {
        abort_unless($request->user()->can('applications.review.secretariat'), 403);

        if (! $this->reviews->canReview($application, $request->user())) {
            return redirect()
                ->route('report-cards.review.index')
                ->with('error', 'Laporan aktiviti ini tiada pada giliran semakan anda.');
        }

        $application->load([
            'alp',
            'financialYear',
            'documents',
            'reportCardReviews.reviewer',
        ]);

        return view('report-cards.review-show', [
            'application' => $application,
            'fullJpDecision' => $request->user()->canMakeFullJpReviewDecision(),
        ]);
    }

    public function reviewStore(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->can('applications.review.secretariat'), 403);

        $fullJp = $request->user()->canMakeFullJpReviewDecision();
        $allowed = $fullJp
            ? array_map(fn (ReviewDecision $d) => $d->value, ReviewDecision::cases())
            : array_map(fn (ReviewDecision $d) => $d->value, ReviewDecision::forPegawaiJp());

        $data = $request->validate([
            'decision' => ['required', Rule::in($allowed)],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['decision'] === ReviewDecision::RETURN_FOR_REVISION->value
            && trim((string) ($data['comments'] ?? '')) === ''
        ) {
            return back()
                ->withErrors(['comments' => 'Ulasan wajib jika laporan dikembalikan.'])
                ->withInput();
        }

        try {
            $this->reviews->review(
                $application,
                $request->user(),
                ReviewDecision::from($data['decision']),
                $data['comments'] ?? null,
            );
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('report-cards.review.index')
            ->with('status', 'Keputusan semakan laporan aktiviti direkod.');
    }

    /** Halaman muat naik laporan aktiviti (tab dalam permohonan). */
    public function show(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);

        return redirect()->route('applications.show', [$application, 'tab' => 'report']);
    }

    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('uploadReportCard', $application);

        $data = $request->validate([
            'document_type' => ['required', 'in:'.DocumentType::REPORT_CARD->value.','.DocumentType::LAPORAN_AKTIVITI->value],
            'file' => [
                'required', 'file', 'max:10240',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'extensions:pdf,jpg,jpeg,png,doc,docx',
            ],
            'report_card_remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->reportCards->upload(
                $application,
                $request->user(),
                $request->file('file'),
                DocumentType::from($data['document_type']),
                $data['report_card_remarks'] ?? null,
            );
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('applications.show', [$application, 'tab' => 'report'])
            ->with('status', 'Fail laporan aktiviti disimpan sebagai draf. Sila semak dan hantar ke Admin JP.');
    }

    public function submit(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('submitReportCard', $application);

        try {
            $this->reportCards->submit($application, $request->user());
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('applications.show', [$application, 'tab' => 'report'])
            ->with('status', 'Laporan aktiviti dihantar ke Admin JP untuk semakan.');
    }

    public function destroyDraft(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('discardReportCardDraft', $application);

        try {
            $this->reportCards->discardDraft($application, $request->user());
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('applications.show', [$application, 'tab' => 'report'])
            ->with('status', 'Draf laporan aktiviti dibatalkan.');
    }
}
