@props(['summary'])
{{-- Memerlukan $summary (App\Services\Budget\BudgetSummary) --}}

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card p-5">
        <p class="text-sm text-gray-500">Peruntukan Tahunan</p>
        <p class="mt-2 text-2xl font-semibold text-navy-700"><x-money :value="$summary->allocation" /></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-gray-500">Committed</p>
        <p class="mt-2 text-2xl font-semibold text-amber-600"><x-money :value="$summary->committed" /></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-gray-500">Perbelanjaan Sebenar</p>
        <p class="mt-2 text-2xl font-semibold text-purple-600"><x-money :value="$summary->spent" /></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-gray-500">Baki Tersedia</p>
        <p class="mt-2 text-2xl font-semibold {{ $summary->available()->isNegative() ? 'text-danger' : 'text-green-600' }}">
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
