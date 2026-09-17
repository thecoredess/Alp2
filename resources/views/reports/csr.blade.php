@extends('layouts.app')
@section('title', 'Laporan CSR')
@section('heading', 'Laporan Impak CSR')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <button class="btn-primary">Tapis</button>
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

    <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
        <div class="card p-4"><p class="text-xs text-gray-500">Projek CSR</p><p class="mt-1 text-lg font-semibold text-navy-700">{{ $summary['total'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Nilai Diluluskan</p><p class="mt-1 text-base font-semibold text-navy-700"><x-money :value="$summary['approved']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Belanja Bersih</p><p class="mt-1 text-base font-semibold text-purple-700"><x-money :value="$summary['net']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Penerima Manfaat</p><p class="mt-1 text-lg font-semibold text-teal-700">{{ number_format($summary['beneficiary_total']) }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Selesai</p><p class="mt-1 text-lg font-semibold text-green-700">{{ $summary['completed'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Ditutup</p><p class="mt-1 text-lg font-semibold text-gray-700">{{ $summary['closed'] }}</p></div>
    </div>

    @if ($summary['beneficiary_missing'] > 0)
        <p class="mb-4 text-xs text-amber-600">Nota: {{ $summary['beneficiary_missing'] }} projek CSR belum melaporkan bilangan penerima (dikecualikan daripada jumlah).</p>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">CSR Mengikut ALP</h3>
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-500"><th class="py-1">ALP</th><th class="py-1 text-right">Projek</th><th class="py-1 text-right">Bersih</th><th class="py-1 text-right">Penerima</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($byAlp as $r)
                        <tr><td class="py-1.5 font-mono text-xs">{{ $r['alp']?->ref_code }}</td><td class="py-1.5 text-right">{{ $r['count'] }}</td><td class="py-1.5 text-right"><x-money :value="$r['net']" /></td><td class="py-1.5 text-right">{{ number_format($r['beneficiaries']) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-gray-400">Tiada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">CSR Mengikut Kawasan</h3>
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-500"><th class="py-1">Kawasan</th><th class="py-1 text-right">Projek</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($byArea as $r)
                        <tr><td class="py-1.5 text-gray-700">{{ $r['area'] }}</td><td class="py-1.5 text-right">{{ $r['count'] }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="py-4 text-center text-gray-400">Tiada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">No. Projek</th><th class="px-3 py-3">ALP</th><th class="px-3 py-3">Lokasi</th>
                <th class="px-3 py-3 text-right">Diluluskan</th><th class="px-3 py-3 text-right">Bersih</th><th class="px-3 py-3 text-right">Penerima</th><th class="px-3 py-3">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($listing as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2"><a href="{{ route('projects.show', $r['project']) }}" class="font-mono text-xs text-royal-600 hover:text-royal-700">{{ $r['project']->project_number }}</a></td>
                        <td class="px-3 py-2 text-gray-600">{{ $r['project']->alp?->ref_code }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $r['project']->application?->location ?? '—' }}</td>
                        <td class="px-3 py-2 text-right text-navy-700"><x-money :value="$r['finance']['approved']" /></td>
                        <td class="px-3 py-2 text-right text-purple-700"><x-money :value="$r['finance']['spent']" /></td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $r['project']->report?->beneficiary_count !== null ? number_format($r['project']->report->beneficiary_count) : '—' }}</td>
                        <td class="px-3 py-2"><x-status-badge :label="$r['project']->status->label()" :classes="$r['project']->status->badgeClasses()" /></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Tiada projek CSR.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
