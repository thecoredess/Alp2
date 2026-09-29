@php
    $budget = $pepuScorecards['budget'] ?? [];
    $allocation = $budget['allocation'] ?? null;
    $available = $budget['available'] ?? null;
    $projected = $budget['projected'] ?? null;
    $util = (float) ($budget['utilisation_percent'] ?? 0);
    $alpCount = (int) ($budget['alp_count'] ?? 0);
    $budgetLink = $pepuScorecards['links']['budget'] ?? null;
@endphp

<div class="mb-8">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h3 class="dashboard-section-title mb-0">
            Bajet &amp; Peruntukan Keseluruhan
            @if ($activeYear ?? null)
                <span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>
            @endif
        </h3>
        @if ($budgetLink)
            <a href="{{ $budgetLink }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Lihat peruntukan →</a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Ahli Lembaga Aktif" icon="users-group" tone="navy">
            {{ $alpCount }}
        </x-stat-card>

        @if ($allocation)
            <div class="stat-card group hover:border-navy-200 hover:shadow-navy-100/50">
                <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-navy-500 opacity-[0.06]"></div>
                <div class="relative">
                    <p class="text-sm font-medium text-gray-500">Peruntukan Tahunan</p>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-navy-700 sm:text-3xl">
                        <x-money :value="$allocation" />
                    </div>
                    @if ($util > 0)
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                            <div
                                class="h-2 rounded-full bg-royal-500 transition-all"
                                style="width: {{ min(100, max(0, $util)) }}%"
                            ></div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-400">{{ number_format($util, 1) }}% komited / dibelanjakan</p>
                    @endif
                </div>
            </div>
        @endif

        @if ($available)
            <x-stat-card
                label="Baki Tersedia"
                icon="wallet"
                :tone="$available->isNegative() ? 'red' : 'green'"
            >
                <x-money :value="$available" />
            </x-stat-card>
        @endif

        @if ($projected)
            <x-stat-card
                label="Baki Peruntukan Semasa"
                icon="sparkles"
                :tone="$projected->isNegative() ? 'red' : 'teal'"
                hint="Selepas tolak permohonan dalam proses"
            >
                <x-money :value="$projected" />
            </x-stat-card>
        @endif
    </div>
</div>
