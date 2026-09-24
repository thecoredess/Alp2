<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationType;
use App\Enums\BudgetTransactionType;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Services\Reports\ApplicationReportService;
use App\Services\Reports\AuditReportService;
use App\Services\Reports\CsrReportService;
use App\Services\Reports\DataQualityService;
use App\Services\Reports\FinancialReportService;
use App\Services\Reports\MakerCheckerReportService;
use App\Services\Reports\ProjectReportService;
use App\Support\Export\PdfWriter;
use App\Support\Export\ReportData;
use App\Support\Export\XlsxWriter;
use Illuminate\Http\Request;

/**
 * Hab Laporan. Semua nilai kewangan rasmi berasal dari ledger melalui
 * perkhidmatan laporan. Tapisan tahun kewangan global. Skop ALP dikuatkuasa
 * untuk pengguna bukan-pengurusan. Eksport memerlukan kebenaran reports.export.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly FinancialReportService $financial,
        private readonly ApplicationReportService $applications,
        private readonly ProjectReportService $projects,
        private readonly CsrReportService $csr,
        private readonly AuditReportService $audit,
        private readonly MakerCheckerReportService $makerChecker,
        private readonly DataQualityService $dataQuality,
    ) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        return view('reports.index', $this->shell($request));
    }

    // ── Kewangan ────────────────────────────────────────────────

    public function allocation(Request $request)
    {
        abort_unless($request->user()->can('reports.financial'), 403);
        $year = $this->year($request);
        $rows = $this->financial->alpUtilisation($year->id);

        $data = new ReportData(
            'Laporan Peruntukan Mengikut ALP',
            [
                ['label' => 'ALP', 'width' => 3], ['label' => 'Peruntukan', 'type' => 'money', 'width' => 3],
                ['label' => 'Menunggu', 'type' => 'money', 'width' => 3], ['label' => 'Diluluskan', 'type' => 'money', 'width' => 3],
                ['label' => 'Belanja Kasar', 'type' => 'money', 'width' => 3], ['label' => 'Refund', 'type' => 'money', 'width' => 2],
                ['label' => 'Belanja Bersih', 'type' => 'money', 'width' => 3], ['label' => 'Dilepaskan', 'type' => 'money', 'width' => 2],
                ['label' => 'Baki', 'type' => 'money', 'width' => 3], ['label' => 'Unjuran Baki', 'type' => 'money', 'width' => 3],
                ['label' => 'Guna Bersih %', 'type' => 'number', 'width' => 2],
            ],
            $rows->map(fn ($r) => [
                $r['alp']->ref_code, $r['breakdown']->allocation->value(), $r['breakdown']->pending->value(),
                $r['breakdown']->committed->value(), $r['breakdown']->grossSpent->value(), $r['breakdown']->refunded->value(),
                $r['breakdown']->netSpent()->value(), $r['breakdown']->released->value(), $r['breakdown']->available()->value(),
                $r['breakdown']->projectedAvailable()->value(), (string) $r['breakdown']->netUtilisationPercent(),
            ])->all(),
            $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.financial-allocation',
            $this->shell($request) + ['year' => $year, 'rows' => $rows, 'totals' => $this->financial->totals($year->id)],
            'peruntukan-alp', true);
    }

    public function ledger(Request $request)
    {
        abort_unless($request->user()->can('reports.financial'), 403);
        $year = $this->year($request);
        $filters = [
            'financial_year_id' => $year->id,
            'alp_id' => $request->integer('alp') ?: null,
            'type' => $request->string('type')->toString() ?: null,
            'date_from' => $request->date('dari')?->toDateString(),
            'date_to' => $request->date('hingga')?->toDateString(),
        ];
        $result = $this->financial->ledgerRows($filters);

        $data = new ReportData(
            'Laporan Lejar Bajet',
            [
                ['label' => 'Tarikh', 'type' => 'date', 'width' => 2], ['label' => 'Rujukan', 'width' => 2],
                ['label' => 'ALP', 'width' => 1], ['label' => 'Jenis', 'width' => 2], ['label' => 'Amaun', 'type' => 'money', 'width' => 2],
                ['label' => 'Baki Peruntukan', 'type' => 'money', 'width' => 2], ['label' => 'Baki Diluluskan', 'type' => 'money', 'width' => 2],
                ['label' => 'Keterangan', 'width' => 4],
            ],
            array_map(fn ($r) => [
                $r['date']?->format('d/m/Y'), $r['reference'], $r['alp'], $r['type']->label(), $r['amount']->value(),
                $r['run_allocation']->value(), $r['run_committed']->value(), $r['description'],
            ], $result['rows']),
            $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.financial-ledger',
            $this->shell($request) + ['year' => $year, 'result' => $result, 'types' => BudgetTransactionType::cases(), 'filters' => $filters],
            'lejar-bajet', true);
    }

    public function reconciliation(Request $request)
    {
        abort_unless($request->user()->can('reports.financial'), 403);
        $year = $this->year($request);
        $exceptions = $this->financial->reconciliationExceptions($year->id);
        $totals = $this->financial->totals($year->id);

        $data = new ReportData(
            'Laporan Rekonsiliasi Kewangan',
            [
                ['label' => 'Projek', 'width' => 3], ['label' => 'Diluluskan', 'type' => 'money', 'width' => 2],
                ['label' => 'Dikira Semula', 'type' => 'money', 'width' => 2], ['label' => 'Status', 'width' => 2],
            ],
            $exceptions->map(fn ($r) => [
                $r['project']->project_number, $r['expected']->value(), $r['recomputed']->value(), 'TIDAK SEIMBANG',
            ])->all(),
            $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.financial-reconciliation',
            $this->shell($request) + ['year' => $year, 'exceptions' => $exceptions, 'totals' => $totals],
            'rekonsiliasi', false);
    }

    public function makerChecker(Request $request)
    {
        abort_unless($request->user()->can('reports.financial'), 403);
        $year = $this->year($request);
        $rows = $this->makerChecker->listing(['financial_year_id' => $year->id, 'alp_id' => $request->integer('alp') ?: null]);

        $data = new ReportData(
            'Laporan Maker-Checker Kewangan',
            [
                ['label' => 'Kategori', 'width' => 2], ['label' => 'Rujukan', 'width' => 2], ['label' => 'Jenis', 'width' => 3],
                ['label' => 'Maker', 'width' => 2], ['label' => 'Checker', 'width' => 2], ['label' => 'Amaun', 'type' => 'money', 'width' => 2],
                ['label' => 'Status', 'width' => 2], ['label' => 'Dihantar', 'type' => 'date', 'width' => 2], ['label' => 'Diluluskan', 'type' => 'date', 'width' => 2],
            ],
            $rows->map(fn ($r) => [
                $r['category'], $r['reference'], $r['type'], $r['maker'], $r['checker'], $r['amount']->value(),
                $r['status'], $r['submitted_at']?->format('d/m/Y'), $r['approved_at']?->format('d/m/Y'),
            ])->all(),
            $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.maker-checker',
            $this->shell($request) + ['year' => $year, 'rows' => $rows], 'maker-checker', true);
    }

    public function dataQuality(Request $request)
    {
        abort_unless($request->user()->canAny(['reports.financial', 'reports.audit']), 403);
        $year = $this->year($request);
        $checks = $this->dataQuality->run($year->id);

        $flat = [];
        foreach ($checks as $c) {
            foreach ($c['items'] as $item) {
                $flat[] = [$c['label'], $item['ref'], $item['detail']];
            }
        }
        $data = new ReportData(
            'Laporan Kualiti Data / Kawalan Dalaman',
            [['label' => 'Semakan', 'width' => 4], ['label' => 'Rujukan', 'width' => 2], ['label' => 'Butiran', 'width' => 5]],
            $flat, $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.data-quality',
            $this->shell($request) + ['year' => $year, 'checks' => $checks, 'total' => $this->dataQuality->totalExceptions($year->id)],
            'kualiti-data', false);
    }

    // ── Permohonan ──────────────────────────────────────────────

    public function applications(Request $request)
    {
        abort_unless($request->user()->can('reports.applications'), 403);
        $year = $this->year($request);
        $filters = $this->applicationFilters($request, $year);
        $listing = $this->applications->listing($filters);

        $data = new ReportData(
            'Laporan Permohonan',
            [
                ['label' => 'No. Permohonan', 'width' => 3], ['label' => 'ALP', 'width' => 2], ['label' => 'Jenis', 'width' => 2],
                ['label' => 'Tajuk', 'width' => 5], ['label' => 'Amaun Dipohon', 'type' => 'money', 'width' => 3],
                ['label' => 'Status', 'width' => 2], ['label' => 'Tarikh', 'type' => 'date', 'width' => 2],
            ],
            $listing->map(fn ($a) => [
                $a->application_number, $a->alp?->ref_code, $a->application_type->label(), $a->programLabelForReport(),
                (string) $a->requested_amount, $a->status->label(), $a->created_at?->format('d/m/Y'),
            ])->all(),
            $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.applications',
            $this->shell($request) + [
                'year' => $year, 'listing' => $listing, 'filters' => $filters,
                'counts' => $this->applications->statusCounts($filters), 'amounts' => $this->applications->amounts($filters),
                'byType' => $this->applications->byType($filters),
                'statusFilterOptions' => ApplicationReportService::statusFilterOptionsForUser($request->user()),
            ], 'permohonan', true);
    }

    // ── Projek ──────────────────────────────────────────────────

    public function projects(Request $request)
    {
        abort_unless($request->user()->can('reports.projects'), 403);
        $year = $this->year($request);
        $filters = $this->projectFilters($request, $year);
        $listing = $this->projects->listing($filters);

        $data = new ReportData(
            'Laporan Projek — Ringkasan Kewangan',
            [
                ['label' => 'No. Projek', 'width' => 3], ['label' => 'ALP', 'width' => 2], ['label' => 'Diluluskan', 'type' => 'money', 'width' => 3],
                ['label' => 'Belanja Kasar', 'type' => 'money', 'width' => 3], ['label' => 'Refund', 'type' => 'money', 'width' => 2],
                ['label' => 'Belanja Bersih', 'type' => 'money', 'width' => 3], ['label' => 'Baki Diluluskan', 'type' => 'money', 'width' => 3],
                ['label' => 'Dilepaskan', 'type' => 'money', 'width' => 2], ['label' => 'Status', 'width' => 2],
            ],
            $listing->map(fn ($r) => [
                $r['project']->project_number, $r['project']->alp?->ref_code, $r['finance']['approved']->value(),
                $r['finance']['gross']->value(), $r['finance']['refunded']->value(), $r['finance']['spent']->value(),
                $r['finance']['outstanding']->value(), $r['finance']['released']->value(), $r['project']->status->label(),
            ])->all(),
            $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.projects',
            $this->shell($request) + [
                'year' => $year, 'listing' => $listing, 'filters' => $filters,
                'counts' => $this->projects->statusCounts($filters), 'totals' => $this->projects->financialTotals($filters),
                'attention' => $this->projects->attentionList($filters),
            ], 'projek', true);
    }

    // ── CSR ─────────────────────────────────────────────────────

    public function csr(Request $request)
    {
        abort_unless($request->user()->can('reports.csr'), 403);
        $year = $this->year($request);
        $filters = $this->projectFilters($request, $year);
        $listing = $this->csr->listing($filters);

        $data = new ReportData(
            'Laporan Impak CSR',
            [
                ['label' => 'No. Projek', 'width' => 3], ['label' => 'ALP', 'width' => 2], ['label' => 'Lokasi', 'width' => 3],
                ['label' => 'Diluluskan', 'type' => 'money', 'width' => 3], ['label' => 'Belanja Bersih', 'type' => 'money', 'width' => 3],
                ['label' => 'Penerima', 'type' => 'number', 'width' => 2], ['label' => 'Status', 'width' => 2],
            ],
            $listing->map(fn ($r) => [
                $r['project']->project_number, $r['project']->alp?->ref_code, $r['project']->application?->location,
                $r['finance']['approved']->value(), $r['finance']['spent']->value(),
                $r['project']->report?->beneficiary_count !== null ? (string) $r['project']->report->beneficiary_count : '',
                $r['project']->status->label(),
            ])->all(),
            $this->meta($request, $year),
        );

        return $this->respond($request, $data, 'reports.csr',
            $this->shell($request) + [
                'year' => $year, 'listing' => $listing, 'filters' => $filters,
                'summary' => $this->csr->summary($filters), 'byAlp' => $this->csr->byAlp($filters), 'byArea' => $this->csr->byArea($filters),
            ], 'csr', true);
    }

    // ── Audit ───────────────────────────────────────────────────

    public function auditTrail(Request $request)
    {
        abort_unless($request->user()->can('reports.audit'), 403);
        $year = $this->year($request);
        $filters = [
            'user_id' => $request->integer('user') ?: null,
            'action' => $request->string('action')->toString() ?: null,
            'entity_type' => $request->string('entiti')->toString() ?: null,
            'entity_id' => $request->integer('entity_id') ?: null,
            'date_from' => $request->date('dari')?->toDateString(),
            'date_to' => $request->date('hingga')?->toDateString(),
        ];
        $rows = $this->audit->listing($filters);

        $data = new ReportData(
            'Laporan Jejak Audit',
            [
                ['label' => 'Masa', 'type' => 'date', 'width' => 3], ['label' => 'Pengguna', 'width' => 3], ['label' => 'Peranan', 'width' => 2],
                ['label' => 'Tindakan', 'width' => 3], ['label' => 'Entiti', 'width' => 3], ['label' => 'Butiran', 'width' => 6], ['label' => 'IP', 'width' => 2],
            ],
            $rows->map(fn ($r) => [
                $r['timestamp']?->format('d/m/Y H:i'), $r['user'], $r['role'], $r['action'], $r['entity'], $r['description'], $r['ip'],
            ])->all(),
            $this->meta($request, $year, false),
        );

        return $this->respond($request, $data, 'reports.audit',
            $this->shell($request) + ['rows' => $rows, 'actions' => $this->audit->distinctActions(), 'filters' => $filters],
            'audit', true);
    }

    // ── Pembantu ────────────────────────────────────────────────

    /** Data rangka bersama untuk semua paparan laporan (tahun + skop). */
    private function shell(Request $request): array
    {
        return [
            'years' => FinancialYear::orderByDesc('year')->get(),
            'selectedYear' => $this->year($request),
            'alps' => $request->user()->can('projects.view_all') ? Alp::orderBy('ref_code')->get() : collect(),
            'canExport' => $request->user()->can('reports.export'),
        ];
    }

    private function year(Request $request): FinancialYear
    {
        $id = $request->integer('fy');
        if ($id) {
            $year = FinancialYear::find($id);
            if ($year) {
                return $year;
            }
        }

        return FinancialYear::active() ?? FinancialYear::orderByDesc('year')->firstOrFail();
    }

    /** Paksa skop ALP sendiri untuk pengguna tanpa capaian menyeluruh. */
    private function scopeAlp(Request $request): ?int
    {
        $user = $request->user();
        if (! $user->can('projects.view_all') && ! $user->can('applications.view_all')) {
            return $user->alp_id; // ALP / urus setia ALP: data sendiri sahaja
        }

        return $request->integer('alp') ?: null;
    }

    private function applicationFilters(Request $request, FinancialYear $year): array
    {
        return [
            'financial_year_id' => $year->id,
            'alp_id' => $this->scopeAlp($request),
            'type' => $request->string('jenis')->toString() ?: null,
            'status' => ApplicationReportService::sanitizeStatusFilter(
                $request->string('status')->toString() ?: null,
                $request->user(),
            ),
            'date_from' => $request->date('dari')?->toDateString(),
            'date_to' => $request->date('hingga')?->toDateString(),
            'amount_min' => $request->input('amaun_min') ?: null,
            'amount_max' => $request->input('amaun_max') ?: null,
        ];
    }

    private function projectFilters(Request $request, FinancialYear $year): array
    {
        return [
            'financial_year_id' => $year->id,
            'alp_id' => $this->scopeAlp($request),
            'type' => $request->string('jenis')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];
    }

    private function meta(Request $request, FinancialYear $year, bool $withYear = true): array
    {
        $meta = [];
        if ($withYear) {
            $meta['Tahun Kewangan'] = (string) $year->year;
        }
        if ($alp = $this->scopeAlp($request)) {
            $meta['ALP'] = Alp::find($alp)?->ref_code ?? (string) $alp;
        }
        $meta['Dijana'] = now()->format('d/m/Y H:i');

        return $meta;
    }

    /** Papar HTML atau hasilkan eksport ikut ?format=. */
    private function respond(Request $request, ReportData $data, string $view, array $viewData, string $filenameBase, bool $landscape)
    {
        $format = $request->query('format');
        if (in_array($format, ['xlsx', 'pdf'], true)) {
            abort_unless($request->user()->can('reports.export'), 403);
            $filename = $filenameBase.'-'.now()->format('Ymd');

            return $format === 'xlsx'
                ? XlsxWriter::response($data, $filename)
                : PdfWriter::response($data, $filename, $landscape);
        }

        return view($view, $viewData + ['reportData' => $data]);
    }
}
