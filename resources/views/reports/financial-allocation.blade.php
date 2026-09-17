@extends('layouts.app')
@section('title', 'Peruntukan Mengikut ALP')
@section('heading', 'Peruntukan Mengikut ALP')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

    {{-- Ringkasan --}}
    <div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-gray-500">Jumlah Peruntukan</p><p class="mt-1 text-lg font-semibold text-navy-700"><x-money :value="$totals->allocation" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Komitmen</p><p class="mt-1 text-lg font-semibold text-amber-600"><x-money :value="$totals->committed" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Belanja Bersih</p><p class="mt-1 text-lg font-semibold text-purple-700"><x-money :value="$totals->netSpent()" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Baki Tersedia</p><p class="mt-1 text-lg font-semibold text-teal-600"><x-money :value="$totals->available()" /></p></div>
    </div>

    {{-- Carta penggunaan bajet mengikut ALP (SVG ringan) --}}
    @if ($rows->isNotEmpty())
        <div class="card mb-5 p-5">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Penggunaan Bajet Mengikut ALP (Belanja Bersih vs Peruntukan)</h3>
            @include('reports.partials.bar-chart', ['rows' => $rows])
        </div>
    @endif

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">ALP</th>
                <th class="px-3 py-3 text-right">Peruntukan</th>
                <th class="px-3 py-3 text-right">Menunggu</th>
                <th class="px-3 py-3 text-right">Komitmen</th>
                <th class="px-3 py-3 text-right">Kasar</th>
                <th class="px-3 py-3 text-right">Refund</th>
                <th class="px-3 py-3 text-right">Bersih</th>
                <th class="px-3 py-3 text-right">Dilepas</th>
                <th class="px-3 py-3 text-right">Baki</th>
                <th class="px-3 py-3 text-right">Unjuran</th>
                <th class="px-3 py-3 text-right">Guna Bersih %</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $r)
                    @php $b = $r['breakdown']; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono text-xs text-gray-700">{{ $r['alp']->ref_code }}</td>
                        <td class="px-3 py-2 text-right text-navy-700"><x-money :value="$b->allocation" /></td>
                        <td class="px-3 py-2 text-right text-gray-500"><x-money :value="$b->pending" /></td>
                        <td class="px-3 py-2 text-right text-amber-600"><x-money :value="$b->committed" /></td>
                        <td class="px-3 py-2 text-right text-gray-700"><x-money :value="$b->grossSpent" /></td>
                        <td class="px-3 py-2 text-right text-teal-600"><x-money :value="$b->refunded" /></td>
                        <td class="px-3 py-2 text-right font-medium text-purple-700"><x-money :value="$b->netSpent()" /></td>
                        <td class="px-3 py-2 text-right text-gray-500"><x-money :value="$b->released" /></td>
                        <td class="px-3 py-2 text-right font-medium text-teal-700"><x-money :value="$b->available()" /></td>
                        <td class="px-3 py-2 text-right text-gray-600"><x-money :value="$b->projectedAvailable()" /></td>
                        <td class="px-3 py-2 text-right text-gray-900">{{ number_format($b->netUtilisationPercent(), 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-10 text-center text-gray-400">Tiada data peruntukan bagi tahun ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="mt-2 text-xs text-gray-400">Guna Bersih % = (Belanja Bersih ÷ Peruntukan) × 100. Menunggu ≠ Komitmen. Semua nilai dari ledger.</p>
@endsection
