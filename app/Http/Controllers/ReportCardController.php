<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationReportCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportCardController extends Controller
{
    public function __construct(private readonly ApplicationReportCardService $reportCards) {}

    /** Senarai pemantauan JP — status penerimaan report card (UR-M07-003). */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->can('applications.view_all')
            || $request->user()->can('applications.review.secretariat'),
            403
        );

        $yearId = $request->integer('tahun') ?: FinancialYear::active()?->id;
        $apps = Application::query()
            ->with(['alp:id,ref_code,name', 'financialYear:id,year'])
            ->where('status', \App\Enums\ApplicationStatus::APPROVED->value)
            ->when($yearId, fn ($q) => $q->where('financial_year_id', $yearId))
            ->when($request->string('status') === 'missing', fn ($q) => $q->whereNull('report_card_submitted_at'))
            ->when($request->string('status') === 'submitted', fn ($q) => $q->whereNotNull('report_card_submitted_at'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('report-cards.index', [
            'applications' => $apps,
            'years' => FinancialYear::orderByDesc('year')->get(),
            'reportCards' => $this->reportCards,
        ]);
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

        return back()->with('status', 'Report card / laporan aktiviti dimuat naik.');
    }
}
