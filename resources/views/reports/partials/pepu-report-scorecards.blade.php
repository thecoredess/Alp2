@php
    $cards = $pepuReportCards ?? [];
    $total = (int) ($cards['total'] ?? 0);
    $completedTotal = (int) ($cards['completed_total'] ?? 0);
    $completedCategories = $cards['completed_by_category'] ?? [];
    $byAlp = $cards['by_alp'] ?? [];
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
                    <span class="font-normal text-gray-400">· {{ $activeYear->year }} · Keseluruhan ALP</span>
                @endif
            </h3>
            @if ($budgetLink)
                <a href="{{ $budgetLink }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Butiran peruntukan →</a>
            @endif
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Peruntukan Bajet Setahun" icon="wallet" tone="navy" :href="$budgetLink">
                <x-money :value="$annualAllocation" />
            </x-stat-card>

            @if ($period)
                <x-stat-card
                    label="Peruntukan Bajet Mengikut Penggal"
                    icon="banknotes"
                    tone="royal"
                    :hint="$period['label'].' · Agregat semua ALP · Luput '.$period['ends_at']->format('d/m/Y')"
                    :href="$budgetLink"
                >
                    <x-money :value="$period['quota']" />
                </x-stat-card>

                <x-stat-card
                    label="Bajet Digunakan (Penggal Semasa)"
                    icon="arrow-path"
                    tone="amber"
                    hint="Dalam proses + diluluskan dalam tempoh penggal (semua ALP)"
                    :href="$budgetLink"
                >
                    <x-money :value="$period['used']" />
                </x-stat-card>

                <x-stat-card
                    label="Baki Peruntukan (Penggal Semasa)"
                    icon="sparkles"
                    :tone="$period['remaining']->isZero() ? 'gray' : 'green'"
                    :hint="'Baki '.$period['label'].' · agregat'"
                    :href="$budgetLink"
                >
                    <x-money :value="$period['remaining']" />
                </x-stat-card>
            @else
                <x-stat-card label="Baki Tersedia" icon="sparkles" tone="green" :href="$budgetLink">
                    <x-money :value="$budget['available'] ?? \App\Support\Money::zero()" />
                </x-stat-card>
            @endif
        </div>
        @include('reports.partials.hub-financial-links')
    </div>
@endif

<div class="mb-8">
    <h3 class="dashboard-section-title">
        Ringkasan Permohonan
        @if ($activeYear ?? null)
            <span class="font-normal text-gray-400">· {{ $activeYear->year }} · Keseluruhan</span>
        @endif
    </h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:max-w-3xl">
        <x-stat-card
            label="Jumlah Permohonan Dimohon"
            icon="clipboard"
            tone="navy"
            :href="$totalLink"
            hint="Permohonan sumbangan dihantar (semua ALP)"
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
        <p class="mb-4 text-sm text-gray-500">
            Permohonan diluluskan dengan laporan aktiviti disahkan JP — ringkasan keseluruhan dan pecahan setiap ALP.
        </p>

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

        @if ($byAlp !== [])
            <div class="card mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3">ALP</th>
                            @foreach ($completedCategories as $cat)
                                <th class="px-4 py-3 text-right">{{ $cat['label'] }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right">Jumlah selesai</th>
                            <th class="px-4 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($byAlp as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs font-semibold text-navy-700">{{ $row['alp']->ref_code }}</span>
                                    <span class="mt-0.5 block max-w-xs truncate text-xs text-gray-500" title="{{ $row['alp']->name }}">{{ $row['alp']->name }}</span>
                                </td>
                                @foreach ($row['completed_by_category'] as $cat)
                                    <td class="px-4 py-3 text-right tabular-nums text-gray-800">{{ $cat['count'] }}</td>
                                @endforeach
                                <td class="px-4 py-3 text-right font-semibold tabular-nums text-gray-900">{{ (int) ($row['completed_total'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <x-table-actions>
                                        <x-table-action
                                            href="{{ $row['detail_link'] ?? '#' }}"
                                            icon="eye"
                                            label="Lihat aktiviti selesai"
                                            variant="primary"
                                        />
                                    </x-table-actions>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-navy-50/60 font-semibold">
                            <td class="px-4 py-3 text-gray-800">Jumlah (semua ALP)</td>
                            @foreach ($completedCategories as $cat)
                                <td class="px-4 py-3 text-right tabular-nums text-navy-800">{{ $cat['count'] }}</td>
                            @endforeach
                            <td class="px-4 py-3 text-right tabular-nums text-navy-800">{{ $completedTotal }}</td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
