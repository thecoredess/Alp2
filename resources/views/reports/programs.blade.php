@extends('layouts.app')
@section('title', 'Laporan Program')
@section('heading', 'Laporan Program & Laporan Aktiviti')
@section('subheading', 'Tahun Kewangan '.$year->year.' · 1 bulan selepas baucar disedia')

@php
    $statusTone = [
        \App\Services\Reports\ProgramReportService::STATUS_RECEIVED => 'bg-green-100 text-green-800',
        \App\Services\Reports\ProgramReportService::STATUS_IN_REVIEW => 'bg-blue-100 text-blue-800',
        \App\Services\Reports\ProgramReportService::STATUS_RETURNED => 'bg-orange-100 text-orange-800',
        \App\Services\Reports\ProgramReportService::STATUS_AWAITING_VOUCHER => 'bg-gray-100 text-gray-700',
        \App\Services\Reports\ProgramReportService::STATUS_PENDING => 'bg-amber-100 text-amber-800',
        \App\Services\Reports\ProgramReportService::STATUS_OVERDUE => 'bg-red-100 text-red-800',
    ];
@endphp

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <div>
            <label class="block text-xs text-gray-500">Kategori program</label>
            <select name="kategori" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                @foreach (\App\Enums\ProgramCategory::options() as $value => $label)
                    <option value="{{ $value }}" @selected(request('kategori') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">Status laporan</label>
            <select name="laporan" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('laporan') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">Tarikh program dari</label>
            <input type="date" name="dari" value="{{ request('dari') }}" class="inp">
        </div>
        <div>
            <label class="block text-xs text-gray-500">Hingga</label>
            <input type="date" name="hingga" value="{{ request('hingga') }}" class="inp">
        </div>
        <button class="btn-white">Tapis</button>
        <div class="ml-auto">@include('reports.partials.export-buttons')</div>
    </form>

    <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-7">
        <div class="card p-4">
            <p class="text-xs text-gray-500">Program diluluskan</p>
            <p class="mt-1 text-lg font-semibold text-navy-700">{{ $summary['total'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Jumlah sumbangan</p>
            <p class="mt-1 text-lg font-semibold text-gray-800"><x-money :value="$summary['amount']" /></p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Laporan diterima</p>
            <p class="mt-1 text-lg font-semibold text-green-700">{{ $summary['received'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Dalam semakan JP</p>
            <p class="mt-1 text-lg font-semibold text-blue-700">{{ $summary['in_review'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Menunggu baucar</p>
            <p class="mt-1 text-lg font-semibold text-gray-700">{{ $summary['awaiting_voucher'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Menunggu laporan</p>
            <p class="mt-1 text-lg font-semibold text-amber-600">{{ $summary['pending'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Tertunggak</p>
            <p class="mt-1 text-lg font-semibold text-red-700">{{ $summary['overdue'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Pematuhan</p>
            <p class="mt-1 text-lg font-semibold text-navy-700">{{ number_format($summary['compliance'], 1) }}%</p>
        </div>
    </div>

    <div class="mb-5 card p-5">
        <h3 class="mb-3 text-sm font-semibold text-gray-900">Pecahan mengikut kategori</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($byCategory as $cat)
                <div class="rounded-lg border border-gray-200 px-4 py-3">
                    <p class="text-xs text-gray-500">{{ $cat['label'] }}</p>
                    <p class="mt-1 text-lg font-semibold text-navy-700">{{ $cat['count'] }}</p>
                    <p class="text-xs text-gray-500"><x-money :value="$cat['amount']" /></p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-3 py-3">No. Permohonan</th>
                    <th class="px-3 py-3">ALP</th>
                    <th class="px-3 py-3">Program / Persatuan</th>
                    <th class="px-3 py-3">Kategori</th>
                    <th class="px-3 py-3">Tarikh program</th>
                    <th class="px-3 py-3 text-right">Sumbangan</th>
                    <th class="px-3 py-3">Tarikh akhir laporan</th>
                    <th class="px-3 py-3">Status laporan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                    @php $app = $row['application']; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono text-xs text-gray-700">
                            <a href="{{ route('applications.show', [$app, 'tab' => 'report']) }}" class="font-medium text-royal-600 hover:text-royal-700">
                                {{ $app->application_number }}
                            </a>
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ $app->alp?->ref_code }}</td>
                        <td class="px-3 py-2 text-gray-900">
                            <p>{{ \Illuminate\Support\Str::limit($app->purpose, 50) }}</p>
                            <p class="text-xs text-gray-500">{{ $app->recipient_name }}</p>
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ $app->program_category?->label() ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $app->program_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-2 text-right"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-3 py-2 text-gray-500">{{ $row['due_at']?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusTone[$row['status']] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$row['status']] ?? $row['status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">Tiada program diluluskan untuk tapisan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
