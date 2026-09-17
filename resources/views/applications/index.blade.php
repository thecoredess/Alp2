@extends('layouts.app')
@section('title', $scopeAll ? 'Semua Permohonan' : 'Permohonan Saya')
@section('heading', $scopeAll ? 'Semua Permohonan' : 'Permohonan Saya')

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
    <div class="page-shell">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
        <form method="GET" class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="No. / tujuan / penerima…" class="inp w-full min-w-[12rem] sm:w-48 sm:shrink-0">
            <select name="tahun" class="inp-select shrink-0">
                <option value="">Semua Tahun</option>
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>
                @endforeach
            </select>
            <select name="status" class="inp-select inp-select--status min-w-[14rem] max-w-full shrink-0">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($scopeAll)
                <select name="alp" class="inp-select inp-select--alp shrink-0">
                    <option value="">Semua ALP</option>
                    @foreach ($alps as $alp)
                        <option value="{{ $alp->id }}" @selected(request('alp') == $alp->id)>{{ $alp->ref_code }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn-white shrink-0">Tapis</button>
        </form>

        @can('create', \App\Models\Application::class)
            <a href="{{ route('applications.create') }}" class="btn-primary shrink-0 whitespace-nowrap">
                <x-icon name="plus" class="h-4 w-4" /> Permohonan Baharu
            </a>
        @endcan
    </div>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No. Permohonan</th>
                    <th class="px-4 py-3">Tujuan / Penerima</th>
                    @if ($scopeAll)<th class="px-4 py-3">ALP</th>@endif
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Tarikh</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $app->application_number }}</td>
                        <td class="px-4 py-3 text-gray-900">
                            <p>{{ $app->purpose }}</p>
                            <p class="text-xs text-gray-500">{{ $app->recipient_name }}</p>
                        </td>
                        @if ($scopeAll)<td class="px-4 py-3 text-gray-600">{{ $app->alp->ref_code }}</td>@endif
                        <td class="px-4 py-3 text-gray-600">{{ $app->financialYear->year }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ ($app->submitted_at ?? $app->created_at)?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-4 py-3">
                            @if ($usesOperationalStatus ?? false)
                                @php $operationalStatus = ApplicationReportService::resolveOperationalStatus($app); @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusTone[$operationalStatus] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ApplicationReportService::statusLabelFor($app, auth()->user()) }}
                                </span>
                            @else
                                <x-status-badge :label="$app->status->label()" :classes="$app->status->badgeClasses()" />
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <x-table-actions>
                                @if ($app->isDraft() && auth()->user()->can('update', $app))
                                    <x-table-action href="{{ route('applications.wizard.maklumat', $app) }}" icon="pencil-square" label="Sambung Draf" variant="primary" />
                                @else
                                    <x-table-action href="{{ route('applications.show', $app) }}" icon="eye" label="Lihat" variant="primary" />
                                @endif
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $scopeAll ? 8 : 7 }}" class="px-4 py-10 text-center text-gray-400">Tiada permohonan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $applications->links() }}</div>
    </div>
@endsection
