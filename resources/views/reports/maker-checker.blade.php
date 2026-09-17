@extends('layouts.app')
@section('title', 'Maker-Checker Kewangan')
@section('heading', 'Laporan Maker-Checker Kewangan')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">Kategori</th><th class="px-3 py-3">Rujukan</th><th class="px-3 py-3">Jenis</th>
                <th class="px-3 py-3">Maker</th><th class="px-3 py-3">Checker</th><th class="px-3 py-3 text-right">Amaun</th>
                <th class="px-3 py-3">Status</th><th class="px-3 py-3">Dihantar</th><th class="px-3 py-3">Diluluskan</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2"><span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ $r['category'] }}</span></td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-700">{{ $r['reference'] ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $r['type'] }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $r['maker'] }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $r['checker'] }}</td>
                        <td class="px-3 py-2 text-right text-gray-900"><x-money :value="$r['amount']" /></td>
                        <td class="px-3 py-2 text-gray-600">{{ $r['status'] }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $r['submitted_at']?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $r['approved_at']?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">Tiada rekod maker-checker.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="mt-2 text-xs text-gray-400">Meliputi Peruntukan, Pelarasan, Perbelanjaan & Refund. Untuk kawalan dalaman.</p>
@endsection
