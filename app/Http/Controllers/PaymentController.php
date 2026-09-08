<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Enums\JkewCrosscheckStatus;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Application\ApplicationPaymentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly ApplicationPaymentService $payments) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('payments.view'), 403);

        $years = FinancialYear::orderByDesc('year')->get();
        $statusFilter = $request->string('status')->toString();
        $jkewScoped = $this->usesJkewScope($request->user());

        $query = Application::query()
            ->with(['alp:id,ref_code,name', 'financialYear:id,year'])
            ->where('status', ApplicationStatus::APPROVED->value)
            ->whereNotNull('payment_status')
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $term = '%'.$request->string('cari').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('application_number', 'like', $term)
                        ->orWhere('purpose', 'like', $term)
                        ->orWhere('payment_voucher_no', 'like', $term);
                });
            })
            ->when(
                $statusFilter !== '' && $statusFilter !== 'open',
                fn ($q) => $q->where('payment_status', $statusFilter),
            )
            ->when(
                $statusFilter === '' || $statusFilter === 'open',
                fn ($q) => $q->whereIn('payment_status', ApplicationPaymentStatus::openValues()),
            )
            ->tap(fn (Builder $q) => $this->applyJkewScope($q, $request->user()))
            ->latest('id');

        return view('payments.index', [
            'applications' => $query->paginate(20)->withQueryString(),
            'years' => $years,
            'statusOptions' => collect(ApplicationPaymentStatus::cases())
                ->mapWithKeys(fn (ApplicationPaymentStatus $s) => [$s->value => $s->label()]),
            'jkewScoped' => $jkewScoped,
        ]);
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->can('payments.manage'), 403);
        abort_unless($application->status === ApplicationStatus::APPROVED, 403);

        $status = $request->string('payment_status')->toString();

        $rules = [
            'payment_status' => ['required', 'in:'.implode(',', array_column(ApplicationPaymentStatus::cases(), 'value'))],
            'payment_supplier_no' => ['nullable', 'string', 'max:100'],
            'payment_voucher_no' => ['nullable', 'string', 'max:100'],
            'payment_voucher_date' => ['nullable', 'date'],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'payment_remarks' => ['nullable', 'string', 'max:2000'],
            'paid_at' => ['nullable', 'date'],
            'sent_to_jkew_at' => ['nullable', 'date'],
            'jkew_crosscheck_status' => ['nullable', 'in:'.implode(',', array_column(JkewCrosscheckStatus::cases(), 'value'))],
            'jkew_crosscheck_remarks' => ['nullable', 'string', 'max:2000'],
        ];

        if ($status === ApplicationPaymentStatus::VOUCHER_PREPARED->value) {
            $rules['payment_supplier_no'] = ['required', 'string', 'max:100'];
            $rules['payment_voucher_no'] = ['required', 'string', 'max:100'];
            $rules['payment_voucher_date'] = ['required', 'date'];
        }

        $data = $request->validate($rules);

        try {
            $this->payments->update($application, $request->user(), $data);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('status', 'Status pembayaran dikemas kini.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('payments.view'), 403);

        $rows = Application::query()
            ->with(['alp:id,ref_code,name', 'financialYear:id,year'])
            ->where('status', ApplicationStatus::APPROVED->value)
            ->whereNotNull('payment_status')
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('status') && $request->string('status') !== 'open',
                fn ($q) => $q->where('payment_status', $request->string('status')))
            ->when(
                ! $request->filled('status') || $request->string('status') === 'open',
                fn ($q) => $q->whereIn('payment_status', ApplicationPaymentStatus::openValues()),
            )
            ->tap(fn (Builder $q) => $this->applyJkewScope($q, $request->user()))
            ->orderBy('id')
            ->get();

        $filename = 'pembayaran-alp-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 untuk Excel
            fputcsv($out, [
                'No. Permohonan', 'ALP', 'Tajuk', 'Tahun', 'Jumlah (RM)',
                'Status Bayaran', 'No. Pembekal', 'No. Baucar', 'Tarikh Baucar', 'Rujukan', 'Dihantar JKEW', 'Semakan Silang', 'Tarikh Bayar', 'Catatan',
            ]);

            foreach ($rows as $app) {
                fputcsv($out, [
                    $app->application_number,
                    $app->alp?->ref_code,
                    $app->project_title,
                    $app->financialYear?->year,
                    $app->requested_amount,
                    $app->payment_status?->label(),
                    $app->payment_supplier_no,
                    $app->payment_voucher_no,
                    $app->payment_voucher_date?->format('Y-m-d'),
                    $app->payment_reference,
                    $app->sent_to_jkew_at?->format('Y-m-d H:i'),
                    $app->jkew_crosscheck_status?->label(),
                    $app->paid_at?->format('Y-m-d H:i'),
                    $app->payment_remarks,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** SEC-007: pengguna skop JKEW (tanpa manage) hanya nampak rekod dihantar ke JKEW. */
    private function usesJkewScope(User $user): bool
    {
        return $user->can('payments.jkew_scope') && ! $user->can('payments.manage');
    }

    private function applyJkewScope(Builder $query, User $user): void
    {
        if (! $this->usesJkewScope($user)) {
            return;
        }

        $query->where(function (Builder $q) {
            $q->whereNotNull('sent_to_jkew_at')
                ->orWhere('payment_status', ApplicationPaymentStatus::SENT_TO_JKEW->value);
        });
    }
}
