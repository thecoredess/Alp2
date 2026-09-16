@extends('layouts.app')
@section('title', 'Matriks Kelulusan')
@section('heading', 'Matriks Kelulusan')

@section('content')
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <strong>DEVELOPMENT CONFIGURATION</strong> — ambang di bawah adalah contoh pembangunan, <strong>bukan polisi rasmi DBKL</strong>.
    </div>

    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-gray-500">Kelulusan berperingkat mengikut jumlah. Aras tidak dipadam jika telah dirujuk — nyahaktif sebaliknya.</p>
        @can('approval_matrix.manage')
            <a href="{{ route('approval-matrix.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Aras Baharu</a>
        @endcan
    </div>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Urutan</th><th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3 text-right">Min</th><th class="px-4 py-3 text-right">Maks</th>
                    <th class="px-4 py-3">Peranan</th><th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($levels as $level)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-900">{{ $level->sequence }}</td>
                        <td class="px-4 py-3 text-gray-900">{{ $level->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-600"><x-money :value="$level->min_amount" /></td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $level->max_amount === null ? '∞' : '' }}@if($level->max_amount !== null)<x-money :value="$level->max_amount" />@endif</td>
                        <td class="px-4 py-3 text-gray-600">{{ \App\Enums\RoleName::from($level->required_role)->label() }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $level->financial_year_id ? $level->financial_year_id : 'Semua' }}</td>
                        <td class="px-4 py-3">
                            @if($level->active)<span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">Aktif</span>
                            @else<span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">Tidak Aktif</span>@endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('approval_matrix.manage')
                                <x-table-actions>
                                    <x-table-action href="{{ route('approval-matrix.edit', $level) }}" icon="pencil-square" label="Kemaskini" variant="primary" />
                                    <form method="POST" action="{{ route('approval-matrix.toggle', $level) }}" class="inline">
                                        @csrf
                                        <x-table-action
                                            :icon="$level->active ? 'x-circle' : 'check'"
                                            :label="$level->active ? 'Nyahaktif' : 'Aktifkan'"
                                            :variant="$level->active ? 'muted' : 'success'"
                                            type="submit"
                                        />
                                    </form>
                                </x-table-actions>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Tiada aras kelulusan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
