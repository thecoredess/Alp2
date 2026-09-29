@php
    $cards = $alpReportCards ?? [];
    $total = (int) ($cards['total'] ?? 0);
    $completedTotal = (int) ($cards['completed_total'] ?? 0);
    $completedCategories = $cards['completed_by_category'] ?? [];
    $totalLink = $cards['links']['total'] ?? null;
    $completedLink = $cards['links']['completed'] ?? null;
    $budgetLink = $cards['links']['budget'] ?? null;
    $budget = $cards['budget'] ?? [];
    $annualAllocation = $budget['annual_allocation'] ?? null;
    $period = $budget['period'] ?? null;
@endphp

@if ($annualAllocation)
    <div class="mb-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="dashboard-section-title mb-0">
                Bajet &amp; Peruntukan
                @if ($activeYear ?? null)
                    <span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>
                @endif
            </h3>
            @if ($budgetLink)
                <a href="{{ $budgetLink }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Butiran bajet →</a>
            @endif
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card
                label="Peruntukan Bajet Setahun"
                icon="wallet"
                tone="navy"
                :href="$budgetLink"
            >
                <x-money :value="$annualAllocation" />
            </x-stat-card>

            @if ($period)
                <x-stat-card
                    label="Peruntukan Bajet Mengikut Penggal"
                    icon="banknotes"
                    tone="royal"
                    :hint="$period['label'].' · Luput '.$period['ends_at']->format('d/m/Y')"
                    :href="$budgetLink"
                >
                    <x-money :value="$period['quota']" />
                </x-stat-card>

                <x-stat-card
                    label="Bajet Digunakan (Penggal Semasa)"
                    icon="arrow-path"
                    tone="amber"
                    hint="Dalam proses + diluluskan dalam tempoh penggal"
                    :href="$budgetLink"
                >
                    <x-money :value="$period['used']" />
                </x-stat-card>

                <x-stat-card
                    label="Baki Peruntukan (Penggal Semasa)"
                    icon="sparkles"
                    :tone="$period['remaining']->isZero() ? 'gray' : 'green'"
                    :hint="'Baki '.$period['label']"
                    :href="$budgetLink"
                >
                    <x-money :value="$period['remaining']" />
                </x-stat-card>
            @endif
        </div>
    </div>
@endif

<div class="mb-8">
    <h3 class="dashboard-section-title">
        Ringkasan Permohonan
        @if ($activeYear ?? null)
            <span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>
        @endif
    </h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:max-w-3xl">
        <x-stat-card
            label="Jumlah Permohonan Dimohon"
            icon="clipboard"
            tone="navy"
            :href="$totalLink"
            hint="Permohonan sumbangan yang telah dihantar"
        >{{ $total }}</x-stat-card>

        <x-stat-card
            label="Jumlah Selesai Keseluruhan Proses"
            icon="check"
            tone="green"
            :href="$completedLink"
            hint="Diluluskan & laporan aktiviti disahkan JP"
        >{{ $completedTotal }}</x-stat-card>
    </div>
</div>

@if ($completedCategories !== [])
    <div class="mb-8">
        <h3 class="dashboard-section-title">
            Pecahan Selesai Mengikut Kategori Aktiviti
            @if ($activeYear ?? null)
                <span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>
            @endif
        </h3>
        <p class="mb-4 text-sm text-gray-500">Permohonan diluluskan dengan laporan aktiviti disahkan JP, mengikut jenis program.</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($completedCategories as $row)
                <x-stat-card
                    :label="$row['label']"
                    :icon="$row['icon']"
                    :tone="$row['tone']"
                    :href="$completedLink"
                >{{ $row['count'] }}</x-stat-card>
            @endforeach
        </div>
    </div>
@endif
