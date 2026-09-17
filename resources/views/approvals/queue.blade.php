@extends('layouts.app')
@section('title', 'Peraku / PEPU')
@section('heading', 'Menunggu Peraku PEPU')
@section('subheading', 'Aliran URS v1.2: Peraku (TP/Pengarah JP) kemudian PEPU / Pengurusan Tertinggi')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="No. / tujuan / penerima…" class="inp w-full min-w-[12rem] shrink-0 sm:w-48">
        <select name="tahun" class="inp-select shrink-0">
            <option value="">Semua Tahun</option>
            @foreach ($years as $y)<option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>@endforeach
        </select>
        <select name="jenis" class="inp-select shrink-0">
            <option value="">Semua Kategori</option>
            @foreach ($categoryOptions as $val => $label)<option value="{{ $val }}" @selected(request('jenis') === $val)>{{ $label }}</option>@endforeach
        </select>
        <select name="alp" class="inp-select inp-select--alp shrink-0">
            <option value="">Semua ALP</option>
            @foreach ($alps as $alp)<option value="{{ $alp->id }}" @selected(request('alp') == $alp->id)>{{ $alp->ref_code }}</option>@endforeach
        </select>
        <button type="submit" class="btn-primary shrink-0">Tapis</button>
        <x-filter-reset />
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No. Permohonan</th><th class="px-4 py-3">Tujuan / Penerima</th>
                    <th class="px-4 py-3">Kategori</th><th class="px-4 py-3">ALP</th>
                    <th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $app->application_number }}</td>
                        <td class="px-4 py-3 text-gray-900">
                            <p>{{ $app->programLabelForReport(60) }}</p>
                            <p class="text-xs text-gray-500">{{ $app->recipientLabelForReport() }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $app->program_category?->label() ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $app->alp->ref_code }}</td>
                        <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-4 py-3 text-right">
                            @php
                                $approvalActionLabel = auth()->user()->hasRole(\App\Enums\RoleName::PELULUS->value)
                                    ? 'Syor'
                                    : (auth()->user()->hasRole(\App\Enums\RoleName::PENGURUSAN->value) ? 'Kelulusan' : 'Semak & Putus');
                            @endphp
                            <x-table-actions>
                                <x-table-action href="{{ route('approvals.show', $app) }}" icon="shield-check" :label="$approvalActionLabel" variant="primary" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tiada permohonan menunggu kelulusan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $applications->links() }}</div>
@endsection
