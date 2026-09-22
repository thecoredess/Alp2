@extends('layouts.app')
@section('title', 'Laporan Program')
@section('heading', 'Laporan Program & Laporan Aktiviti')
@section('subheading', 'Tahun Kewangan '.$year->year.' · 1 bulan selepas baucar disedia')

@php
    use App\Services\Reports\ApplicationReportService;
    use App\Services\Reports\ProgramReportService;

    $staffStatusFilters = ApplicationReportService::usesStaffStatusFilters(auth()->user());
    $statusTone = [
        ApplicationReportService::FILTER_RECEIVED => 'bg-green-100 text-green-800',
        ApplicationReportService::FILTER_RECOMMENDED => 'bg-indigo-100 text-indigo-800',
        ApplicationReportService::FILTER_APPROVED => 'bg-emerald-100 text-emerald-800',
        ApplicationReportService::FILTER_REJECTED => 'bg-red-100 text-red-800',
        ApplicationReportService::FILTER_IN_REVIEW => 'bg-blue-100 text-blue-800',
        ApplicationReportService::FILTER_RETURNED => 'bg-orange-100 text-orange-800',
        ApplicationReportService::FILTER_AWAITING_VOUCHER => 'bg-gray-100 text-gray-700',
        ApplicationReportService::FILTER_PENDING => 'bg-amber-100 text-amber-800',
        ProgramReportService::STATUS_OVERDUE => 'bg-red-100 text-red-800',
    ];
@endphp

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter', ['submitOnChange' => false])
        <div>
            <label class="block text-xs text-gray-500">Kategori program</label>
            <select name="kategori" class="inp">
                <option value="">Semua</option>
                @foreach (\App\Enums\ProgramCategory::options() as $value => $label)
                    <option value="{{ $value }}" @selected(request('kategori') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">Status laporan</label>
            <select name="laporan" class="inp">
                <option value="">Semua Status</option>
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
        <button type="submit" class="btn-primary">Tapis</button>
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

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
                            <p>{{ $app->programLabelForReport(50) }}</p>
                            <p class="text-xs text-gray-500">{{ $app->recipientLabelForReport() }}</p>
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ $app->program_category?->label() ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $app->program_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-2 text-right"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-3 py-2 text-gray-500">{{ $row['due_at']?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusTone[$row['status']] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ApplicationReportService::statusLabelFor($app, auth()->user()) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">Tiada rekod untuk tapisan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
