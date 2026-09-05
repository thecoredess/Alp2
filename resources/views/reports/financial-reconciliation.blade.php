@extends('layouts.app')
@section('title', 'Rekonsiliasi Kewangan')
@section('heading', 'Rekonsiliasi Kewangan')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <div class="ml-auto">@include('reports.partials.export-buttons')</div>
    </form>

    <div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-gray-500">Peruntukan</p><p class="mt-1 text-lg font-semibold text-navy-700"><x-money :value="$totals->allocation" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Komitmen</p><p class="mt-1 text-lg font-semibold text-amber-600"><x-money :value="$totals->committed" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Belanja Bersih</p><p class="mt-1 text-lg font-semibold text-purple-700"><x-money :value="$totals->netSpent()" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Baki + Dilepaskan</p><p class="mt-1 text-lg font-semibold text-teal-600"><x-money :value="$totals->available()->plus($totals->released)" /></p></div>
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Pengecualian Rekonsiliasi Projek</h3>
            @if ($exceptions->isEmpty())
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">✓ 0 pengecualian — semua projek seimbang</span>
            @else
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">{{ $exceptions->count() }} pengecualian</span>
            @endif
        </div>
        <p class="mt-1 text-xs text-gray-500">Invarian: Diluluskan = Baki Komitmen + Belanja Bersih + Dilepaskan (setiap projek).</p>

        @if ($exceptions->isNotEmpty())
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-3 py-3">Projek</th><th class="px-3 py-3 text-right">Diluluskan</th>
                        <th class="px-3 py-3 text-right">Dikira Semula</th><th class="px-3 py-3">Tindakan</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($exceptions as $e)
                            <tr class="bg-red-50/40">
                                <td class="px-3 py-2 font-mono text-xs">{{ $e['project']->project_number }}</td>
                                <td class="px-3 py-2 text-right"><x-money :value="$e['expected']" /></td>
                                <td class="px-3 py-2 text-right text-red-700"><x-money :value="$e['recomputed']" /></td>
                                <td class="px-3 py-2"><a href="{{ route('projects.show', $e['project']) }}" class="text-royal-600 hover:text-royal-700 font-medium">Lihat Projek</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
