{{-- Visualisasi dashboard ALP — data sedia ada (status / bajet / kuota). --}}
@php
    $statusLabels = [
        'draft' => 'Draf',
        'in_process' => 'Dalam Proses',
        'revision' => 'Perlu Pembetulan',
        'approved' => 'Diluluskan',
        'rejected' => 'Ditolak',
    ];
    $statusColors = [
        'draft' => '#9ca3af',
        'in_process' => '#3b82f6',
        'revision' => '#f97316',
        'approved' => '#22c55e',
        'rejected' => '#ef4444',
    ];
    $statusRows = [];
    $statusTotal = 0;
    foreach ($statusLabels as $key => $label) {
        $n = (int) ($appStats[$key] ?? 0);
        $statusTotal += $n;
        $statusRows[] = ['key' => $key, 'label' => $label, 'value' => $n, 'color' => $statusColors[$key]];
    }
    $statusMax = max(1, ...array_column($statusRows, 'value'));

    $alloc = (float) ($annualAllocation?->value() ?? 0);
    $avail = (float) ($annualAvailable?->value() ?? 0);
    $usedBudget = max(0, $alloc - $avail);
    $budgetMax = max(1, $alloc, $avail, $usedBudget);

    $hasPeriod = ! empty($periodSummary);
    $periodQuota = $hasPeriod ? (float) $periodSummary['quota']->value() : 0;
    $periodUsed = $hasPeriod ? (float) $periodSummary['used']->value() : 0;
    $periodRem = $hasPeriod ? (float) $periodSummary['remaining']->value() : 0;
    $periodMax = max(1, $periodQuota, $periodUsed, $periodRem);

    $defaultChart = $statusTotal > 0 ? 'status' : ($alloc > 0 ? 'bajet' : ($hasPeriod ? 'kuota' : 'status'));

    // Conic gradient for donut
    $donutStops = [];
    $cursor = 0;
    if ($statusTotal > 0) {
        foreach ($statusRows as $row) {
            if ($row['value'] <= 0) {
                continue;
            }
            $pct = ($row['value'] / $statusTotal) * 100;
            $donutStops[] = $row['color'].' '.$cursor.'% '.($cursor + $pct).'%';
            $cursor += $pct;
        }
    }
    $donutCss = $donutStops !== []
        ? 'conic-gradient('.implode(', ', $donutStops).')'
        : 'conic-gradient(#e5e7eb 0% 100%)';
@endphp

<div
    class="mt-8"
    x-data="{ chart: '{{ $defaultChart }}' }"
>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="dashboard-section-title mb-0">Graf Ringkasan</h3>
        <div class="flex flex-wrap gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1 text-sm">
            <button type="button" @click="chart='status'"
                    :class="chart==='status' ? 'bg-navy-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                    class="rounded-lg px-3 py-1.5 font-medium transition">Status</button>
            <button type="button" @click="chart='bajet'"
                    :class="chart==='bajet' ? 'bg-navy-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                    class="rounded-lg px-3 py-1.5 font-medium transition">Bajet</button>
            @if ($hasPeriod)
                <button type="button" @click="chart='kuota'"
                        :class="chart==='kuota' ? 'bg-navy-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="rounded-lg px-3 py-1.5 font-medium transition">Kuota Tempoh</button>
            @endif
        </div>
    </div>

    <div class="card p-5 sm:p-6">
        {{-- Status permohonan --}}
        <div x-show="chart==='status'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <p class="mb-4 text-sm font-semibold text-gray-900">Status Permohonan @if($activeYear)<span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>@endif</p>
                <div class="space-y-3">
                    @foreach ($statusRows as $row)
                        @php $w = ($row['value'] / $statusMax) * 100; @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-700">{{ $row['label'] }}</span>
                                <span class="font-semibold text-gray-900">{{ $row['value'] }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full transition-all" style="width: {{ number_format($w, 1) }}%; background: {{ $row['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($statusTotal === 0)
                    <p class="mt-4 text-sm text-gray-400">Tiada permohonan untuk dipaparkan.</p>
                @endif
            </div>
            <div class="flex flex-col items-center justify-center">
                <div class="relative h-44 w-44 rounded-full" style="background: {{ $donutCss }}">
                    <div class="absolute inset-6 flex flex-col items-center justify-center rounded-full bg-white">
                        <span class="text-2xl font-bold text-navy-700">{{ $statusTotal }}</span>
                        <span class="text-xs text-gray-500">Jumlah</span>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs text-gray-600">
                    @foreach ($statusRows as $row)
                        @if ($row['value'] > 0)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-sm" style="background: {{ $row['color'] }}"></span>
                                {{ $row['label'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Bajet tahunan --}}
        <div x-show="chart==='bajet'" x-cloak>
            <p class="mb-4 text-sm font-semibold text-gray-900">Bajet Tahunan @if($activeYear)<span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>@endif</p>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Peruntukan', 'value' => $alloc, 'color' => '#1e3a5f', 'money' => $annualAllocation],
                    ['label' => 'Digunakan', 'value' => $usedBudget, 'color' => '#f59e0b', 'money' => null],
                    ['label' => 'Baki', 'value' => max(0, $avail), 'color' => '#16a34a', 'money' => $annualAvailable],
                ] as $bar)
                    @php $h = max(8, ($bar['value'] / $budgetMax) * 140); @endphp
                    <div class="flex flex-col items-center">
                        <div class="flex h-40 w-full items-end justify-center rounded-xl bg-gray-50 px-6 pb-2">
                            <div class="w-16 rounded-t-lg transition-all" style="height: {{ number_format($h, 1) }}px; background: {{ $bar['color'] }}"
                                 title="RM {{ number_format($bar['value'], 2) }}"></div>
                        </div>
                        <p class="mt-3 text-xs font-medium text-gray-500">{{ $bar['label'] }}</p>
                        <p class="text-sm font-semibold text-gray-900">
                            @if ($bar['money'])
                                <x-money :value="$bar['money']" />
                            @else
                                RM {{ number_format($bar['value'], 2) }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
            @if ($alloc <= 0)
                <p class="mt-4 text-center text-sm text-gray-400">Tiada peruntukan tahunan ditetapkan.</p>
            @endif
        </div>

        {{-- Kuota tempoh --}}
        @if ($hasPeriod)
            <div x-show="chart==='kuota'" x-cloak>
                <p class="mb-4 text-sm font-semibold text-gray-900">
                    Kuota Tempoh · {{ $periodSummary['label'] }}
                    @if($activeYear)<span class="font-normal text-gray-400"> · {{ $activeYear->year }}</span>@endif
                </p>
                <div class="space-y-4">
                    @foreach ([
                        ['label' => 'Kuota tempoh', 'value' => $periodQuota, 'color' => '#1e3a5f', 'money' => $periodSummary['quota']],
                        ['label' => 'Digunakan', 'value' => $periodUsed, 'color' => '#f59e0b', 'money' => $periodSummary['used']],
                        ['label' => 'Baki tempoh', 'value' => $periodRem, 'color' => '#16a34a', 'money' => $periodSummary['remaining']],
                    ] as $row)
                        @php $w = ($row['value'] / $periodMax) * 100; @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-700">{{ $row['label'] }}</span>
                                <span class="font-semibold text-gray-900"><x-money :value="$row['money']" /></span>
                            </div>
                            <div class="h-3.5 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full" style="width: {{ number_format($w, 1) }}%; background: {{ $row['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-gray-400">Luput {{ $periodSummary['ends_at']->format('d/m/Y') }} · tiada bawa ke hadapan</p>
            </div>
        @endif
    </div>
</div>
