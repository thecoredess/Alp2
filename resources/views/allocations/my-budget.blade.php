@extends('layouts.app')
@section('title', 'Bajet Saya')
@section('heading', 'Bajet Saya')
@section('subheading', $alp->name.' ('.$alp->ref_code.')'.($year ? ' · Tahun '.$year->year : ''))

@section('content')
    @if (! $year)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Tiada tahun kewangan aktif buat masa ini.
        </div>
    @else
        @if ($policyLimits)
            <x-urs-policy-rules :limits="$policyLimits" class="mb-6" />
        @endif

        <div class="mb-6">
            <div class="card p-5">
                <p class="text-sm text-gray-500">Baki Tersedia</p>
                <p class="mt-2 text-2xl font-semibold {{ $summary->available()->isNegative() ? 'text-danger' : 'text-green-600' }}">
                    <x-money :value="$summary->available()" />
                </p>
            </div>
        </div>

        @if ($periodSummary)
            <x-urs-period-summary :period-summary="$periodSummary" :year="$year?->year" class="mb-6" />
        @endif

        @if ($policyLimits && $periodSummary)
            <div class="mt-3 rounded-lg border border-navy-100 bg-navy-50 px-4 py-3 text-sm text-navy-800">
                Had setiap permohonan:
                <strong>RM {{ $policyLimits['max_per_application']->format() }}</strong>
                · Baki tempoh semasa:
                <strong>RM {{ $periodSummary['remaining']->format() }}</strong>
            </div>
        @endif

        <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="card p-4">
                <p class="text-xs text-gray-500">Pending Request (permohonan belum diluluskan — <strong>bukan</strong> Committed)</p>
                <p class="mt-1 text-lg font-semibold text-orange-600"><x-money :value="$pending" /></p>
            </div>
            <div class="card p-4">
                <p class="text-xs text-gray-500">Baki Peruntukan Semasa (Baki Peruntukan Diluluskan − Pending Request)</p>
                <p class="mt-1 text-lg font-semibold {{ $projected->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$projected" /></p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Sejarah Transaksi Bajet</h3>
            @if ($allocation)
                <x-ledger-table :statement="$statement" />
            @else
                <div class="card p-10 text-center text-gray-400">
                    Belum ada peruntukan direkodkan untuk anda bagi tahun {{ $year->year }}.
                </div>
            @endif
        </div>
    @endif
@endsection
