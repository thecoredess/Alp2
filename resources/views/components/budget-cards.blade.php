@props(['summary'])
{{-- Memerlukan $summary (App\Services\Budget\BudgetSummary) --}}

<div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
    <div class="card min-w-0 p-4 sm:p-5">
        <p class="text-xs text-gray-500 sm:text-sm">Peruntukan Tahunan</p>
        <p class="mt-2 text-base font-semibold tabular-nums leading-tight text-navy-700 sm:text-lg xl:text-2xl">
            <x-money :value="$summary->allocation" />
        </p>
    </div>
    <div class="card min-w-0 p-4 sm:p-5">
        <p class="text-xs text-gray-500 sm:text-sm">Committed</p>
        <p class="mt-2 text-base font-semibold tabular-nums leading-tight text-amber-600 sm:text-lg xl:text-2xl">
            <x-money :value="$summary->committed" />
        </p>
    </div>
    <div class="card min-w-0 p-4 sm:p-5">
        <p class="text-xs text-gray-500 sm:text-sm">Perbelanjaan Sebenar</p>
        <p class="mt-2 text-base font-semibold tabular-nums leading-tight text-purple-600 sm:text-lg xl:text-2xl">
            <x-money :value="$summary->spent" />
        </p>
    </div>
    <div class="card min-w-0 p-4 sm:p-5">
        <p class="text-xs text-gray-500 sm:text-sm">Baki Tersedia</p>
        <p @class([
            'mt-2 text-base font-semibold tabular-nums leading-tight sm:text-lg xl:text-2xl',
            'text-danger' => $summary->available()->isNegative(),
            'text-green-600' => ! $summary->available()->isNegative(),
        ])>
            <x-money :value="$summary->available()" />
        </p>
    </div>
</div>

@if($summary->allocation->isPositive())
    <div class="mt-4">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Penggunaan Bajet</span>
            <span>{{ $summary->utilisationPercent() }}%</span>
        </div>
        <div class="mt-1 h-2.5 w-full overflow-hidden rounded-full bg-gray-200">
            <div class="h-full rounded-full bg-royal-500" style="width: {{ min(100, $summary->utilisationPercent()) }}%"></div>
        </div>
    </div>
@endif
