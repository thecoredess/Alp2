@extends('layouts.app')
@section('title', $scopeAll ? 'Semua Permohonan' : 'Permohonan Saya')
@section('heading', $scopeAll ? 'Semua Permohonan' : 'Permohonan Saya')

@section('content')
    <div class="page-shell">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="No. / tujuan / penerima…" class="inp sm:w-48">
            <select name="tahun" class="inp sm:w-auto">
                <option value="">Semua Tahun</option>
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>
                @endforeach
            </select>
            <select name="status" class="inp sm:w-auto">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($scopeAll)
                <select name="alp" class="inp sm:w-auto">
                    <option value="">Semua ALP</option>
                    @foreach ($alps as $alp)
                        <option value="{{ $alp->id }}" @selected(request('alp') == $alp->id)>{{ $alp->ref_code }}</option>
                    @endforeach
                </select>
            @endif
            <button class="btn-white">Tapis</button>
        </form>

        @can('create', \App\Models\Application::class)
            <a href="{{ route('applications.create') }}" class="btn-primary shrink-0">
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
                        <td class="px-4 py-3"><x-status-badge :label="$app->status->label()" :classes="$app->status->badgeClasses()" /></td>
                        <td class="px-4 py-3 text-right">
                            @if ($app->isDraft() && auth()->user()->can('update', $app))
                                <a href="{{ route('applications.wizard.maklumat', $app) }}" class="text-royal-600 hover:text-royal-700 font-medium">Sambung Draf</a>
                            @else
                                <a href="{{ route('applications.show', $app) }}" class="text-royal-600 hover:text-royal-700 font-medium">Lihat</a>
                            @endif
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
