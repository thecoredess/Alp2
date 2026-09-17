@extends('layouts.app')
@section('title', 'Dashboard Eksekutif')
@section('heading', 'Dashboard Eksekutif')
@section('subheading', $year ? 'Tahun Kewangan '.$year->year : 'Tiada tahun kewangan aktif')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500">Tahun Kewangan</label>
            <select name="fy" class="inp" onchange="this.form.requestSubmit()">
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected($year?->id === $y->id)>{{ $y->year }} @if($y->is_active) (Aktif) @endif</option>
                @endforeach
            </select>
        </div>
        @can('reports.view')<a href="{{ route('reports.index', ['fy' => $year?->id]) }}" class="btn-white">Laporan Penuh</a>@endcan
    </form>

    @if (! $year)
        <div class="card p-10 text-center text-gray-400">Tiada tahun kewangan untuk dipaparkan.</div>
    @else
        {{-- Baris utama --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="card p-5"><p class="text-sm text-gray-500">Jumlah Peruntukan</p><p class="mt-2 text-2xl font-semibold text-navy-700"><x-money :value="$totals->allocation" /></p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Committed</p><p class="mt-2 text-2xl font-semibold text-amber-600"><x-money :value="$totals->committed" /></p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Net Spent</p><p class="mt-2 text-2xl font-semibold text-purple-700"><x-money :value="$totals->netSpent()" /></p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Baki Tersedia</p><p class="mt-2 text-2xl font-semibold text-teal-600"><x-money :value="$totals->available()" /></p></div>
        </div>

        {{-- Baris kedua --}}
        <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="card p-5"><p class="text-sm text-gray-500">Pending Application</p><p class="mt-2 text-xl font-semibold text-gray-700"><x-money :value="$totals->pending" /></p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Baki Peruntukan Semasa</p><p class="mt-2 text-xl font-semibold text-teal-700"><x-money :value="$totals->projectedAvailable()" /></p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Refund</p><p class="mt-2 text-xl font-semibold text-green-600"><x-money :value="$totals->refunded" /></p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Released Commitment</p><p class="mt-2 text-xl font-semibold text-gray-600"><x-money :value="$totals->released" /></p></div>
        </div>
        <p class="mt-2 text-[11px] text-gray-400">Pending Application ≠ Committed. Net Spent = Gross − Refund. Semua dari ledger.</p>

        {{-- Kad operasi --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div class="card p-4"><p class="text-xs text-gray-500">Jumlah ALP</p><p class="mt-1 text-xl font-semibold text-navy-700">{{ $alpCount }}</p></div>
            <a href="{{ route('reports.applications', ['fy'=>$year->id]) }}" class="card p-4 hover:ring-2 hover:ring-navy-100"><p class="text-xs text-gray-500">Permohonan Aktif</p><p class="mt-1 text-xl font-semibold text-blue-700">{{ ($appCounts['under_review'] ?? 0) + ($appCounts['pending_approval'] ?? 0) }}</p></a>
            <a href="{{ route('reports.projects', ['fy'=>$year->id,'status'=>'in_progress']) }}" class="card p-4 hover:ring-2 hover:ring-navy-100"><p class="text-xs text-gray-500">Projek Aktif</p><p class="mt-1 text-xl font-semibold text-blue-700">{{ $projectCounts['in_progress'] ?? 0 }}</p></a>
            <a href="{{ route('reports.projects', ['fy'=>$year->id,'status'=>'delayed']) }}" class="card p-4 hover:ring-2 hover:ring-orange-100"><p class="text-xs text-gray-500">Projek Lewat</p><p class="mt-1 text-xl font-semibold text-orange-600">{{ $projectCounts['delayed'] ?? 0 }}</p></a>
            <a href="{{ route('reports.projects', ['fy'=>$year->id,'status'=>'completed']) }}" class="card p-4 hover:ring-2 hover:ring-navy-100"><p class="text-xs text-gray-500">Projek Selesai</p><p class="mt-1 text-xl font-semibold text-green-700">{{ $projectCounts['completed'] ?? 0 }}</p></a>
            <a href="{{ route('reports.projects', ['fy'=>$year->id,'status'=>'closed']) }}" class="card p-4 hover:ring-2 hover:ring-navy-100"><p class="text-xs text-gray-500">Projek Ditutup</p><p class="mt-1 text-xl font-semibold text-gray-700">{{ $projectCounts['closed'] ?? 0 }}</p></a>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Carta penggunaan ALP --}}
            <div class="card p-5 lg:col-span-2">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Penggunaan Bajet Mengikut ALP</h3>
                @if ($utilisation->isNotEmpty())
                    @include('reports.partials.bar-chart', ['rows' => $utilisation])
                @else
                    <p class="text-sm text-gray-400">Tiada data peruntukan.</p>
                @endif
            </div>

            {{-- Integriti / perhatian --}}
            <div class="space-y-4">
                <div class="card p-5">
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Integriti Kewangan</h3>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Pengecualian Rekonsiliasi</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $reconExceptions === 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $reconExceptions }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Isu Kualiti Data</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $dataQualityTotal === 0 ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $dataQualityTotal }}</span>
                    </div>
                    @can('reports.financial')
                        <a href="{{ route('reports.reconciliation', ['fy'=>$year->id]) }}" class="mt-3 block text-xs text-royal-600 hover:text-royal-700">Lihat rekonsiliasi →</a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Trend belanja bulanan --}}
        <div class="mt-6 card p-5">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Trend Belanja Bersih Bulanan</h3>
            @include('reports.partials.trend-chart', ['monthly' => $monthly])
        </div>
    @endif
@endsection
