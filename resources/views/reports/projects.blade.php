@extends('layouts.app')
@section('title', 'Laporan Projek')
@section('heading', 'Laporan Projek')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <div>
            <label class="block text-xs text-gray-500">Jenis</label>
            <select name="jenis" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                <option value="csr" @selected(request('jenis')==='csr')>CSR</option>
                <option value="development" @selected(request('jenis')==='development')>Pembangunan</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">Status</label>
            <select name="status" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                @foreach (\App\Enums\ProjectStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-primary">Tapis</button>
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

    {{-- Kad kiraan status (drill-down) --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([['Semua','',$counts['total']], ['Dalam Pelaksanaan','in_progress',$counts['in_progress']], ['Lewat','delayed',$counts['delayed']], ['Selesai','completed',$counts['completed']], ['Ditutup','closed',$counts['closed']], ['Belum Mula','not_started',$counts['not_started']]] as [$label,$st,$c])
            <a href="{{ request()->fullUrlWithQuery(['status'=>$st]) }}" class="card p-4 hover:ring-2 hover:ring-navy-100">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-xl font-semibold {{ $st==='delayed' ? 'text-orange-600' : 'text-navy-700' }}">{{ $c }}</p>
            </a>
        @endforeach
    </div>

    {{-- Ringkasan kewangan --}}
    <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
        <div class="card p-4"><p class="text-xs text-gray-500">Diluluskan</p><p class="mt-1 text-base font-semibold text-navy-700"><x-money :value="$totals['approved']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Kasar</p><p class="mt-1 text-base font-semibold text-gray-700"><x-money :value="$totals['gross']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Refund</p><p class="mt-1 text-base font-semibold text-teal-600"><x-money :value="$totals['refunded']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Bersih</p><p class="mt-1 text-base font-semibold text-purple-700"><x-money :value="$totals['net']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Baki Diluluskan</p><p class="mt-1 text-base font-semibold text-amber-600"><x-money :value="$totals['outstanding']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Dilepaskan</p><p class="mt-1 text-base font-semibold text-teal-700"><x-money :value="$totals['released']" /></p></div>
    </div>

    {{-- Perlu perhatian --}}
    @if ($attention->isNotEmpty())
        <div class="mb-5 card p-5">
            <h3 class="mb-3 text-sm font-semibold text-orange-700">Perlu Perhatian ({{ $attention->count() }})</h3>
            <ul class="divide-y divide-gray-100 text-sm">
                @foreach ($attention as $a)
                    <li class="flex items-center justify-between gap-4 py-2">
                        <a href="{{ route('projects.show', $a['project']) }}" class="font-mono text-xs text-royal-600 hover:text-royal-700">{{ $a['project']->project_number }}</a>
                        <span class="flex flex-wrap justify-end gap-1">
                            @foreach ($a['reasons'] as $reason)
                                <span class="rounded bg-orange-50 px-2 py-0.5 text-xs text-orange-700">{{ $reason }}</span>
                            @endforeach
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">No. Projek</th><th class="px-3 py-3">ALP</th><th class="px-3 py-3 text-right">Diluluskan</th>
                <th class="px-3 py-3 text-right">Kasar</th><th class="px-3 py-3 text-right">Refund</th><th class="px-3 py-3 text-right">Bersih</th>
                <th class="px-3 py-3 text-right">Baki Diluluskan</th><th class="px-3 py-3 text-right">Dilepaskan</th><th class="px-3 py-3">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($listing as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2"><a href="{{ route('projects.show', $r['project']) }}" class="font-mono text-xs text-royal-600 hover:text-royal-700">{{ $r['project']->project_number }}</a></td>
                        <td class="px-3 py-2 text-gray-600">{{ $r['project']->alp?->ref_code }}</td>
                        <td class="px-3 py-2 text-right text-navy-700"><x-money :value="$r['finance']['approved']" /></td>
                        <td class="px-3 py-2 text-right text-gray-700"><x-money :value="$r['finance']['gross']" /></td>
                        <td class="px-3 py-2 text-right text-teal-600"><x-money :value="$r['finance']['refunded']" /></td>
                        <td class="px-3 py-2 text-right font-medium text-purple-700"><x-money :value="$r['finance']['spent']" /></td>
                        <td class="px-3 py-2 text-right text-amber-600"><x-money :value="$r['finance']['outstanding']" /></td>
                        <td class="px-3 py-2 text-right text-gray-500"><x-money :value="$r['finance']['released']" /></td>
                        <td class="px-3 py-2"><x-status-badge :label="$r['project']->status->label()" :classes="$r['project']->status->badgeClasses()" /></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">Tiada projek untuk tapisan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
