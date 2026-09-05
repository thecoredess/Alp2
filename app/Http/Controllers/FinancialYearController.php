<?php

namespace App\Http\Controllers;

use App\Enums\FinancialYearStatus;
use App\Http\Requests\FinancialYearRequest;
use App\Models\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialYearController extends Controller
{
    public function index(): View
    {
        $this->authorize('financial_years.view');

        $years = FinancialYear::orderByDesc('year')->paginate(15);

        return view('financial-years.index', compact('years'));
    }

    public function create(): View
    {
        $this->authorize('financial_years.create');

        return view('financial-years.create');
    }

    public function store(FinancialYearRequest $request): RedirectResponse
    {
        $this->authorize('financial_years.create');

        FinancialYear::create([
            ...$request->validated(),
            'status' => FinancialYearStatus::DRAFT,
            'is_active' => false,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('financial-years.index')
            ->with('status', 'Tahun kewangan berjaya dicipta.');
    }

    public function edit(FinancialYear $financialYear): View
    {
        $this->authorize('financial_years.update');
        $this->ensureEditable($financialYear);

        return view('financial-years.edit', ['year' => $financialYear]);
    }

    public function update(FinancialYearRequest $request, FinancialYear $financialYear): RedirectResponse
    {
        $this->authorize('financial_years.update');
        $this->ensureEditable($financialYear);

        $financialYear->update($request->validated());

        return redirect()->route('financial-years.index')
            ->with('status', 'Tahun kewangan berjaya dikemas kini.');
    }

    /** Draf → Dibuka. */
    public function open(FinancialYear $financialYear): RedirectResponse
    {
        $this->authorize('financial_years.manage');

        if ($financialYear->status !== FinancialYearStatus::DRAFT) {
            return back()->with('error', 'Hanya tahun berstatus Draf boleh dibuka.');
        }

        $financialYear->update([
            'status' => FinancialYearStatus::OPEN,
            'opened_at' => now(),
        ]);

        return back()->with('status', "Tahun kewangan {$financialYear->year} telah dibuka.");
    }

    /** Tetapkan sebagai tahun aktif (hanya satu boleh aktif). */
    public function activate(FinancialYear $financialYear): RedirectResponse
    {
        $this->authorize('financial_years.manage');

        if ($financialYear->status === FinancialYearStatus::CLOSED) {
            return back()->with('error', 'Tahun yang telah ditutup tidak boleh diaktifkan.');
        }

        DB::transaction(function () use ($financialYear) {
            // Nyahaktif tahun aktif sedia ada (kembali ke status "dibuka").
            FinancialYear::where('is_active', true)
                ->where('id', '!=', $financialYear->id)
                ->update(['is_active' => false, 'status' => FinancialYearStatus::OPEN]);

            $financialYear->update([
                'is_active' => true,
                'status' => FinancialYearStatus::ACTIVE,
                'opened_at' => $financialYear->opened_at ?? now(),
            ]);
        });

        return back()->with('status', "Tahun kewangan {$financialYear->year} kini aktif.");
    }

    /** Tutup tahun kewangan (dilindungi selepas ini). */
    public function close(FinancialYear $financialYear): RedirectResponse
    {
        $this->authorize('financial_years.manage');

        if ($financialYear->status === FinancialYearStatus::CLOSED) {
            return back()->with('error', 'Tahun ini telah pun ditutup.');
        }

        $financialYear->update([
            'status' => FinancialYearStatus::CLOSED,
            'is_active' => false,
            'closed_at' => now(),
        ]);

        return back()->with('status', "Tahun kewangan {$financialYear->year} telah ditutup.");
    }

    /** Pastikan tahun belum ditutup sebelum dibenarkan menyunting. */
    private function ensureEditable(FinancialYear $financialYear): void
    {
        abort_if($financialYear->isClosed(), 403, 'Tahun kewangan yang telah ditutup tidak boleh diubah.');
    }
}
