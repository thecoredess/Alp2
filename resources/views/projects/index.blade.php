@extends('layouts.app')
@section('title', $scopeAll ? 'Semua Projek' : 'Projek Saya')
@section('heading', $scopeAll ? 'Semua Projek' : 'Projek Saya')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="No. / nama…" class="inp w-full min-w-[12rem] shrink-0 sm:w-48">
        <select name="tahun" class="inp-select shrink-0"><option value="">Semua Tahun</option>
            @foreach ($years as $y)<option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>@endforeach
        </select>
        <select name="jenis" class="inp-select shrink-0"><option value="">Semua Jenis</option>
            @foreach ($typeOptions as $val => $label)<option value="{{ $val }}" @selected(request('jenis') === $val)>{{ $label }}</option>@endforeach
        </select>
        <select name="status" class="inp-select inp-select--status shrink-0"><option value="">Semua Status</option>
            @foreach ($statusOptions as $val => $label)<option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>@endforeach
        </select>
        @if ($scopeAll)
            <select name="alp" class="inp-select inp-select--alp shrink-0"><option value="">Semua ALP</option>
                @foreach ($alps as $alp)<option value="{{ $alp->id }}" @selected(request('alp') == $alp->id)>{{ $alp->ref_code }}</option>@endforeach
            </select>
        @endif
        <button type="submit" class="btn-white shrink-0">Tapis</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No. Projek</th><th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Jenis</th>@if($scopeAll)<th class="px-4 py-3">ALP</th>@endif
                    <th class="px-4 py-3 text-right">Diluluskan</th><th class="px-4 py-3">Kemajuan</th>
                    <th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($projects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $project->project_number }}</td>
                        <td class="px-4 py-3 text-gray-900">{{ $project->project_name }}</td>
                        <td class="px-4 py-3"><x-status-badge :label="$project->project_type->label()" :classes="$project->project_type->badgeClasses()" /></td>
                        @if($scopeAll)<td class="px-4 py-3 text-gray-600">{{ $project->alp->ref_code }}</td>@endif
                        <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$project->approved_amount" /></td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->progress_percent }}%</td>
                        <td class="px-4 py-3"><x-status-badge :label="$project->status->label()" :classes="$project->status->badgeClasses()" /></td>
                        <td class="px-4 py-3 text-right">
                            <x-table-actions>
                                <x-table-action href="{{ route('projects.show', $project) }}" icon="eye" label="Lihat" variant="primary" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $scopeAll ? 8 : 7 }}" class="px-4 py-10 text-center text-gray-400">Tiada projek.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
@endsection
