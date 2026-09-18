@extends('layouts.app')
@section('title', 'Lejar Bajet')
@section('heading', 'Laporan Lejar Bajet')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <div>
            <label class="block text-xs text-gray-500">Jenis Transaksi</label>
            <select name="type" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                @foreach ($types as $t)
                    <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-xs text-gray-500">Dari</label><input type="date" name="dari" value="{{ request('dari') }}" class="inp"></div>
        <div><label class="block text-xs text-gray-500">Hingga</label><input type="date" name="hingga" value="{{ request('hingga') }}" class="inp"></div>
        <button class="btn-primary">Tapis</button>
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">Tarikh</th><th class="px-3 py-3">Rujukan</th><th class="px-3 py-3">ALP</th>
                <th class="px-3 py-3">Jenis</th><th class="px-3 py-3 text-right">Amaun</th>
                <th class="px-3 py-3 text-right">Baki Peruntukan</th><th class="px-3 py-3 text-right">Baki Komitmen</th>
                <th class="px-3 py-3 text-right">Baki Belanja</th><th class="px-3 py-3">Keterangan</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($result['rows'] as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-600">{{ $r['date']?->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-700">{{ $r['reference'] ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $r['alp'] ?? '—' }}</td>
                        <td class="px-3 py-2"><x-status-badge :label="$r['type']->label()" :classes="$r['type']->badgeClasses()" /></td>
                        <td class="px-3 py-2 text-right text-gray-900"><x-money :value="$r['amount']" /></td>
                        <td class="px-3 py-2 text-right text-navy-700"><x-money :value="$r['run_allocation']" /></td>
                        <td class="px-3 py-2 text-right text-amber-600"><x-money :value="$r['run_committed']" /></td>
                        <td class="px-3 py-2 text-right text-purple-700"><x-money :value="$r['run_spent']" /></td>
                        <td class="px-3 py-2 text-gray-500">{{ $r['description'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">Tiada transaksi lejar untuk tapisan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="mt-2 text-xs text-gray-400">{{ $result['count'] }} transaksi. Baki berjalan mengikut susunan masa. Lejar ialah sumber kebenaran kewangan.</p>
@endsection
