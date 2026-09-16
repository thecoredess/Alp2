@extends('layouts.app')
@section('title', 'Tahun Kewangan')
@section('heading', 'Tahun Kewangan')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-gray-500">Urus tahun kewangan — buka, tetapkan aktif, atau tutup.</p>
        @can('financial_years.create')
            <a href="{{ route('financial-years.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Tahun Baharu
            </a>
        @endcan
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Label</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aktif</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($years as $year)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $year->year }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $year->label ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :label="$year->status->label()" :classes="$year->status->badgeClasses()" />
                        </td>
                        <td class="px-4 py-3">
                            @if($year->is_active)
                                <span class="text-green-600 font-medium">&#10003; Ya</span>
                            @else
                                <span class="text-gray-400">Tidak</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-table-actions>
                                @can('financial_years.update')
                                    @if(! $year->isClosed())
                                        <x-table-action href="{{ route('financial-years.edit', $year) }}" icon="pencil-square" label="Kemaskini" variant="primary" />
                                    @endif
                                @endcan

                                @can('financial_years.manage')
                                    @if($year->status === \App\Enums\FinancialYearStatus::DRAFT)
                                        <form method="POST" action="{{ route('financial-years.open', $year) }}" class="inline">
                                            @csrf
                                            <x-table-action icon="check" label="Buka" variant="primary" type="submit" />
                                        </form>
                                    @endif

                                    @if(! $year->is_active && ! $year->isClosed())
                                        <form method="POST" action="{{ route('financial-years.activate', $year) }}" class="inline"
                                              onsubmit="return confirm('Tetapkan tahun {{ $year->year }} sebagai tahun aktif?')">
                                            @csrf
                                            <x-table-action icon="check" label="Set Aktif" variant="success" type="submit" />
                                        </form>
                                    @endif

                                    @if(! $year->isClosed())
                                        <form method="POST" action="{{ route('financial-years.close', $year) }}" class="inline"
                                              onsubmit="return confirm('Tutup tahun {{ $year->year }}? Data akan dilindungi selepas ini.')">
                                            @csrf
                                            <x-table-action icon="x-circle" label="Tutup" variant="danger" type="submit" />
                                        </form>
                                    @endif
                                @endcan
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada tahun kewangan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $years->links() }}</div>
@endsection
