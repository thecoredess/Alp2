@extends('layouts.app')
@section('title', 'Peraku / PEPU')
@section('heading', 'Menunggu Peraku / PEPU')
@section('subheading', 'Aliran URS v1.2: Peraku (TP/Pengarah JP) kemudian PEPU / Pengurusan Tertinggi')

@section('content')
    <form method="GET" class="mb-5 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="No. / tajuk…" class="inp sm:w-48">
        <select name="tahun" class="inp sm:w-auto">
            <option value="">Semua Tahun</option>
            @foreach ($years as $y)<option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>@endforeach
        </select>
        <select name="jenis" class="inp sm:w-auto">
            <option value="">Semua Jenis</option>
            @foreach ($typeOptions as $val => $label)<option value="{{ $val }}" @selected(request('jenis') === $val)>{{ $label }}</option>@endforeach
        </select>
        <select name="alp" class="inp sm:w-auto">
            <option value="">Semua ALP</option>
            @foreach ($alps as $alp)<option value="{{ $alp->id }}" @selected(request('alp') == $alp->id)>{{ $alp->ref_code }}</option>@endforeach
        </select>
        <button class="btn-white">Tapis</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No. Permohonan</th><th class="px-4 py-3">Tajuk</th>
                    <th class="px-4 py-3">Jenis</th><th class="px-4 py-3">ALP</th>
                    <th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $app->application_number }}</td>
                        <td class="px-4 py-3 text-gray-900">{{ $app->project_title }}</td>
                        <td class="px-4 py-3"><x-status-badge :label="$app->application_type->label()" :classes="$app->application_type->badgeClasses()" /></td>
                        <td class="px-4 py-3 text-gray-600">{{ $app->alp->ref_code }}</td>
                        <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('approvals.show', $app) }}" class="btn-primary !py-1 !px-3 text-xs">Semak &amp; Putus</a>
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
