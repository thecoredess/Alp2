@php
    $budget = $alpScorecards['budget'] ?? [];
    $allocation = $budget['allocation'] ?? null;
    $available = $budget['available'] ?? null;
    $util = (float) ($budget['utilisation_percent'] ?? 0);
    $period = $budget['period'] ?? null;
    $budgetLink = $alpScorecards['links']['budget'] ?? null;
@endphp

<div class="mb-8">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h3 class="dashboard-section-title mb-0">
            Bajet &amp; Kuota
            @if ($activeYear ?? null)
                <span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>
            @endif
        </h3>
        @if ($budgetLink)
            <a href="{{ $budgetLink }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Butiran bajet →</a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @if ($allocation)
            <div class="stat-card group hover:border-navy-200 hover:shadow-navy-100/50">
                <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-navy-500 opacity-[0.06]"></div>
                <div class="relative">
                    <p class="text-sm font-medium text-gray-500">Peruntukan Tahunan</p>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-navy-700 sm:text-3xl">
                        <x-money :value="$allocation" />
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-2 rounded-full bg-navy-500 transition-all"
                            style="width: {{ min(100, max(0, $util)) }}%"
                        ></div>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">{{ number_format($util, 1) }}% telah digunakan</p>
                </div>
            </div>
        @endif

        @if ($available)
            <x-stat-card
                label="Baki Tahunan"
                icon="sparkles"
                :tone="$available->isNegative() ? 'red' : 'green'"
            >
                <x-money :value="$available" />
            </x-stat-card>
        @endif

        @if ($period)
            <x-stat-card
                label="Kuota {{ $period['label'] }}"
                icon="banknotes"
                tone="navy"
                :hint="'Luput '.$period['ends_at']->format('d/m/Y')"
            >
                <x-money :value="$period['quota']" />
            </x-stat-card>

            <x-stat-card
                label="Baki {{ $period['label'] }}"
                icon="sparkles"
                tone="teal"
                hint="Dalam proses + kelulusan dikira dalam penggunaan"
            >
                <x-money :value="$period['remaining']" />
            </x-stat-card>
        @endif
    </div>
</div>
