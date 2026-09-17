@extends('layouts.app')
@section('title', 'Ahli Lembaga Penasihat')
@section('heading', 'Ahli Lembaga Penasihat')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input type="text" name="cari" value="{{ request('cari') }}"
                       placeholder="Cari nama / kod / zon…" class="inp w-full pl-9">
            </div>
            <button type="submit" class="btn-primary shrink-0">Cari</button>
            <x-filter-reset />
        </form>
        @can('alps.create')
            <a href="{{ route('alps.create') }}" class="btn-primary shrink-0">
                <x-icon name="plus" class="h-4 w-4" /> ALP Baharu
            </a>
        @endcan
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Kod</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Portfolio / Zon</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($alps as $alp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $alp->ref_code }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            <a href="{{ route('alps.show', $alp) }}" class="text-royal-600 hover:text-royal-700 font-medium">{{ $alp->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $alp->portfolio_zone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :label="$alp->status->label()" :classes="$alp->status->badgeClasses()" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <x-table-actions>
                                <x-table-action href="{{ route('alps.show', $alp) }}" icon="eye" label="Lihat" variant="muted" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Tiada rekod ALP dijumpai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $alps->links() }}</div>
@endsection
