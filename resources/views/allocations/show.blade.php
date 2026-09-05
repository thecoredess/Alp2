@extends('layouts.app')
@section('title', 'Peruntukan — '.$allocation->alp->ref_code)
@section('heading', 'Peruntukan '.$allocation->alp->name)
@section('subheading', $allocation->alp->ref_code.' · Tahun '.$allocation->financialYear->year)

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('allocations.index', ['tahun' => $allocation->financial_year_id]) }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke senarai
        </a>
        @can('adjust', $allocation)
            @unless($allocation->financialYear->isClosed())
                <a href="{{ route('allocations.adjust', $allocation) }}" class="btn-primary">Laras Peruntukan</a>
            @endunless
        @endcan
    </div>

    <x-budget-cards :summary="$summary" />
    @if ($entitlement ?? null)
        <p class="mt-2 text-xs text-gray-500">
            Siling kelayakan BR-007: <strong>RM {{ number_format((float) $entitlement->value(), 2) }}</strong>
            (ikut tarikh lantikan ALP)
        </p>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-6">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Maklumat Peruntukan</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">No. Rujukan</dt><dd class="font-mono text-gray-900">{{ $allocation->reference_no ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tahun Kewangan</dt><dd class="text-gray-900">{{ $allocation->financialYear->year }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Status Tahun</dt>
                    <dd><x-status-badge :label="$allocation->financialYear->status->label()" :classes="$allocation->financialYear->status->badgeClasses()" /></dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Dicipta Oleh</dt><dd class="text-gray-900">{{ $allocation->creator?->name ?? '—' }}</dd></div>
                @if($allocation->remarks)
                    <div><dt class="text-gray-500">Catatan</dt><dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $allocation->remarks }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="lg:col-span-2">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Ledger Transaksi</h3>
            <x-ledger-table :statement="$statement" />
            <p class="mt-2 text-xs text-gray-400">Ledger bersifat kekal (immutable). Sebarang perubahan direkodkan sebagai transaksi baharu.</p>
        </div>
    </div>
@endsection
