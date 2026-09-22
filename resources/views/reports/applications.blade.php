@extends('layouts.app')
@section('title', 'Laporan Permohonan')
@section('heading', 'Laporan Permohonan')
@section('subheading', 'Tahun Kewangan '.$year->year)

@php
    use App\Services\Reports\ApplicationReportService;

    $statusTone = [
        ApplicationReportService::FILTER_RECEIVED => 'bg-green-100 text-green-800',
        ApplicationReportService::FILTER_RECOMMENDED => 'bg-indigo-100 text-indigo-800',
        ApplicationReportService::FILTER_APPROVED => 'bg-emerald-100 text-emerald-800',
        ApplicationReportService::FILTER_REJECTED => 'bg-red-100 text-red-800',
        ApplicationReportService::FILTER_IN_REVIEW => 'bg-blue-100 text-blue-800',
        ApplicationReportService::FILTER_RETURNED => 'bg-orange-100 text-orange-800',
        ApplicationReportService::FILTER_AWAITING_VOUCHER => 'bg-gray-100 text-gray-700',
        ApplicationReportService::FILTER_PENDING => 'bg-amber-100 text-amber-800',
    ];
@endphp

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <div>
            <label class="block text-xs text-gray-500">Jenis</label>
            <select name="jenis" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                <option value="sumbangan" @selected(request('jenis')==='sumbangan')>Sumbangan</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">Status</label>
            <select name="status" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua Status</option>
                @foreach ($statusFilterOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-xs text-gray-500">Dari</label><input type="date" name="dari" value="{{ request('dari') }}" class="inp"></div>
        <div><label class="block text-xs text-gray-500">Hingga</label><input type="date" name="hingga" value="{{ request('hingga') }}" class="inp"></div>
        <button class="btn-primary">Tapis</button>
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

    {{-- Kad status & amaun --}}
    <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-gray-500">Jumlah Permohonan</p><p class="mt-1 text-lg font-semibold text-navy-700">{{ $counts['total'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Jumlah Dipohon</p><p class="mt-1 text-lg font-semibold text-gray-700"><x-money :value="$amounts['total_requested']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Peruntukan Permohonan Dalam Proses (Belum diluluskan)</p><p class="mt-1 text-lg font-semibold text-amber-600"><x-money :value="$amounts['pending_request']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Amaun Diluluskan</p><p class="mt-1 text-lg font-semibold text-green-700"><x-money :value="$amounts['approved_amount']" /></p></div>
    </div>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">No. Permohonan</th><th class="px-3 py-3">ALP</th>
                <th class="px-3 py-3">Kategori / Penerima</th><th class="px-3 py-3 text-right">Amaun</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Tarikh</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($listing as $a)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono text-xs text-gray-700">{{ $a->application_number }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $a->alp?->ref_code }}</td>
                        <td class="px-3 py-2 text-gray-900">
                            <p>{{ $a->programCategoryLabelForReport(40) }}</p>
                            <p class="text-xs text-gray-500">{{ $a->recipientLabelForReport() }}</p>
                        </td>
                        <td class="px-3 py-2 text-right"><x-money :value="$a->requested_amount" /></td>
                        <td class="px-3 py-2">
                            @php $operationalStatus = ApplicationReportService::resolveOperationalStatus($a); @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusTone[$operationalStatus] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ApplicationReportService::statusLabelFor($a, auth()->user()) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ $a->created_at?->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tiada permohonan untuk tapisan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
