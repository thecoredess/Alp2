@extends('layouts.app')
@section('title', 'Peruntukan & Bajet')
@section('heading', 'Peruntukan & Bajet')
@section('subheading', $year ? 'Tahun Kewangan '.$year->year : 'Tiada tahun kewangan')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-500">Tahun Kewangan</label>
            <select name="tahun" onchange="this.form.submit()" class="inp w-auto">
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected($year && $year->id === $y->id)>
                        {{ $y->year }} @if($y->is_active) (Aktif) @endif
                    </option>
                @endforeach
            </select>
        </form>
        @can('allocations.manage')
            <a href="{{ route('allocations.create', ['tahun' => $year?->id]) }}" class="btn-primary shrink-0">
                <x-icon name="plus" class="h-4 w-4" /> Set Peruntukan
            </a>
        @endcan
    </div>

    <x-budget-cards :summary="$totals" />

    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="card p-4">
            <p class="text-xs text-gray-500">Pending Request (permohonan belum diluluskan — <strong>bukan</strong> Committed)</p>
            <p class="mt-1 text-lg font-semibold text-orange-600"><x-money :value="$totalPending" /></p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Projected Available (Ledger Available − Pending Request)</p>
            <p class="mt-1 text-lg font-semibold {{ $projectedTotal->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$projectedTotal" /></p>
        </div>
    </div>

    <div class="card mt-6 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">ALP</th>
                    <th class="px-4 py-3 text-right">Peruntukan</th>
                    <th class="px-4 py-3 text-right">Committed</th>
                    <th class="px-4 py-3 text-right">Spent</th>
                    <th class="px-4 py-3 text-right">Pending</th>
                    <th class="px-4 py-3 text-right">Baki</th>
                    <th class="px-4 py-3 text-right">Guna</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($alps as $alp)
                    @php
                        $s = $summaries->get($alp->id) ?? new \App\Services\Budget\BudgetSummary();
                        $alloc = $allocations->get($alp->id);
                        $alpPending = $pendingByAlp->get($alp->id) ?? \App\Support\Money::zero();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-900">{{ $alp->ref_code }}</span>
                            <span class="text-gray-500"> — {{ $alp->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$s->allocation" /></td>
                        <td class="px-4 py-3 text-right text-amber-600"><x-money :value="$s->committed" /></td>
                        <td class="px-4 py-3 text-right text-purple-600"><x-money :value="$s->spent" /></td>
                        <td class="px-4 py-3 text-right text-orange-600"><x-money :value="$alpPending" /></td>
                        <td class="px-4 py-3 text-right font-medium {{ $s->available()->isNegative() ? 'text-danger' : 'text-green-600' }}">
                            <x-money :value="$s->available()" />
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $s->utilisationPercent() }}%</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if ($alloc)
                                <x-table-actions>
                                    <x-table-action href="{{ route('allocations.show', $alloc) }}" icon="eye" label="Lihat" variant="primary" />
                                    @can('allocations.manage')
                                        @unless($year?->isClosed())
                                            <x-table-action href="{{ route('allocations.adjust', $alloc) }}" icon="pencil-square" label="Laras" variant="muted" />
                                        @endunless
                                    @endcan
                                </x-table-actions>
                            @else
                                @can('allocations.manage')
                                    <x-table-actions>
                                        <x-table-action href="{{ route('allocations.create', ['tahun' => $year?->id, 'alp' => $alp->id]) }}" icon="plus" label="Set Peruntukan" variant="primary" />
                                    </x-table-actions>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Tiada ALP.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
