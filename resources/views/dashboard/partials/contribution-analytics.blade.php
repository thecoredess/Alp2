@php
    $analytics = $contributionAnalytics;
    $periodColors = ['#86efac', '#4ade80', '#15803d'];
    $statusColors = [
        'in_process' => '#c2410c',
        'approved' => '#ea8a5a',
        'rejected' => '#fbd5c0',
    ];

    $periodSlices = collect($analytics['periods'])
        ->values()
        ->map(fn ($period, $index) => [
            'label' => $period['label'],
            'value' => $period['value'],
            'color' => $periodColors[$index],
        ])
        ->all();
    $statusSlices = [
        ['label' => 'Dalam Proses', 'value' => $analytics['status']['in_process'], 'color' => $statusColors['in_process']],
        ['label' => 'Diluluskan', 'value' => $analytics['status']['approved'], 'color' => $statusColors['approved']],
        ['label' => 'Ditolak', 'value' => $analytics['status']['rejected'], 'color' => $statusColors['rejected']],
    ];

    $monthLabels = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogs', 'Sep', 'Okt', 'Nov', 'Dis'];
    $monthValues = $analytics['monthly'];
    $maxMonth = max(1, ...$monthValues);
    $chartWidth = 640;
    $chartHeight = 170;
    $left = 52;
    $bottom = 28;
    $plotHeight = $chartHeight - $bottom - 14;
    $step = ($chartWidth - $left - 12) / 11;
    $points = [];
    foreach ($monthValues as $index => $value) {
        $x = $left + ($index * $step);
        $y = ($chartHeight - $bottom) - (($value / $maxMonth) * $plotHeight);
        $points[] = round($x, 1).','.round($y, 1);
    }
    $tickMax = (int) ceil($maxMonth / 1000) * 1000;
    $tickMax = max(1000, $tickMax);
    $ticks = [0, $tickMax / 2, $tickMax];
@endphp

<section @class(['mt-8' => ($analytics['scope_label'] ?? '') === 'ALP sendiri', 'mt-5' => ($analytics['scope_label'] ?? '') !== 'ALP sendiri']) aria-labelledby="contribution-analysis-title">
    @if (($analytics['scope_label'] ?? '') === 'ALP sendiri')
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="contribution-analysis-title" class="text-lg font-semibold text-gray-900">Analisa Sumbangan</h2>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $analytics['scope_label'] }} ·
                    {{ $analytics['from']->format('d/m/Y') }} hingga {{ $analytics['to']->format('d/m/Y') }}
                </p>
            </div>
            <a href="{{ route('budget.mine') }}" class="text-xs font-medium text-royal-600 hover:text-royal-700">
                Bajet saya →
            </a>
        </div>
    @else
        <h2 id="contribution-analysis-title" class="sr-only">Analisa Sumbangan — graf & jadual</h2>
    @endif

    {{-- Kuota tempoh — ALP sahaja --}}
    @if ($periodSummary ?? null)
        <div class="mb-5">
            <h3 class="dashboard-section-title mb-4">
                Kuota Tempoh · {{ $periodSummary['label'] }}
                @if ($activeYear ?? null)
                    <span class="font-normal text-gray-400"> · {{ $activeYear->year }}</span>
                @endif
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat-card label="Kuota tempoh" icon="banknotes" tone="navy">
                    <x-money :value="$periodSummary['quota']" />
                </x-stat-card>
                <x-stat-card label="Dalam Proses + Kelulusan" icon="arrow-path" tone="amber">
                    <x-money :value="$periodSummary['used']" />
                </x-stat-card>
                <x-stat-card
                    label="Baki tempoh"
                    icon="sparkles"
                    tone="green"
                    :hint="'Luput '.$periodSummary['ends_at']->format('d/m/Y').' · tiada bawa ke hadapan'"
                >
                    <x-money :value="$periodSummary['remaining']" />
                </x-stat-card>
            </div>
        </div>
    @endif

    {{-- Graf --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <article class="overflow-hidden rounded-2xl border-2 border-gray-300 bg-white">
            <h3 class="px-4 py-2.5 text-center text-sm font-bold uppercase text-white" style="background:#15803d">Baki Sumbangan</h3>
            <div class="p-5">
                <x-donut-chart
                    :slices="$periodSlices"
                    center-label="Baki"
                    :center-value="'RM '.number_format(array_sum(array_column($periodSlices, 'value')), 0)"
                />
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border-2 border-gray-300 bg-white">
            <h3 class="px-4 py-2.5 text-center text-sm font-bold uppercase text-gray-900" style="background:#facc15">Status Kelulusan</h3>
            <div class="p-5">
                <x-donut-chart
                    :slices="$statusSlices"
                    center-label="Jumlah"
                    :center-value="'RM '.number_format(array_sum(array_column($statusSlices, 'value')), 0)"
                />
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border-2 border-gray-300 bg-white">
            <h3 class="bg-blue-500 px-4 py-2.5 text-center text-sm font-bold uppercase leading-tight text-white">
                Jumlah Sumbangan Mengikut Bulan
            </h3>
            <div class="p-3">
                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="w-full" style="height: 170px" role="img" aria-label="Jumlah sumbangan bulanan dalam Ringgit Malaysia">
                    @foreach ($ticks as $tick)
                        @php $tickY = ($chartHeight - $bottom) - (($tick / $tickMax) * $plotHeight); @endphp
                        <line x1="{{ $left }}" y1="{{ round($tickY, 1) }}" x2="{{ $chartWidth - 12 }}" y2="{{ round($tickY, 1) }}" stroke="#e5e7eb" stroke-width="1" />
                        <text x="{{ $left - 5 }}" y="{{ round($tickY + 3, 1) }}" font-size="9" fill="#6b7280" text-anchor="end">{{ number_format($tick) }}</text>
                    @endforeach
                    <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#2563eb" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />
                    @foreach ($monthValues as $index => $value)
                        @php
                            [$pointX, $pointY] = explode(',', $points[$index]);
                        @endphp
                        <circle cx="{{ $pointX }}" cy="{{ $pointY }}" r="3.5" fill="#2563eb">
                            <title>{{ $monthLabels[$index] }}: RM {{ number_format($value, 2) }}</title>
                        </circle>
                        <text x="{{ $pointX }}" y="{{ $chartHeight - 7 }}" font-size="8" fill="#6b7280" text-anchor="middle">{{ $monthLabels[$index] }}</text>
                    @endforeach
                    <text x="10" y="12" font-size="9" fill="#6b7280">RM</text>
                </svg>
                <p class="text-center text-xs text-gray-500">Berdasarkan tarikh permohonan dihantar</p>
            </div>
        </article>
    </div>

    {{-- Penapis dan jadual --}}
    <div class="mt-5 overflow-hidden rounded-2xl border-2 border-gray-300 bg-white">
        <div class="flex flex-col gap-3 bg-fuchsia-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <h3 class="text-base font-bold uppercase text-gray-900">Ringkasan Sumbangan</h3>
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-2 rounded-xl bg-white/75 px-3 py-2">
                <label class="text-[11px] font-bold uppercase text-gray-600">
                    Dari
                    <input name="dari" type="date" value="{{ $analytics['from']->format('Y-m-d') }}" class="mt-1 block rounded-lg border-gray-300 px-2 py-1.5 text-xs">
                </label>
                <span class="pb-1.5 text-xs font-medium text-gray-600">hingga</span>
                <label class="text-[11px] font-bold uppercase text-gray-600">
                    Hingga
                    <input name="hingga" type="date" value="{{ $analytics['to']->format('Y-m-d') }}" class="mt-1 block rounded-lg border-gray-300 px-2 py-1.5 text-xs">
                </label>
                <button type="submit" class="btn-primary !px-3 !py-1.5 text-xs">Tapis</button>
                <x-filter-reset :href="route('dashboard')" class="btn-primary !px-3 !py-1.5 text-xs shrink-0" />
            </form>
        </div>

        @php
            $isAlpTable = ($analytics['scope_label'] ?? '') === 'ALP sendiri';
            $tableColspan = $isAlpTable ? 5 : 9;
        @endphp
        <div class="overflow-x-auto">
            <table @class(['w-full text-sm', 'min-w-[640px]' => $isAlpTable, 'min-w-[1100px]' => ! $isAlpTable])>
                <thead class="bg-purple-200 text-xs font-bold uppercase text-gray-800">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2.5 text-left">ALP</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-right">Dalam Proses (RM)</th>
                        @unless ($isAlpTable)
                            <th class="border border-gray-300 px-3 py-2.5 text-right">Diluluskan (RM)</th>
                        @endunless
                        <th class="border border-gray-300 px-3 py-2.5 text-right">Ditolak (RM)</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-right">Baki Sumbangan (RM)</th>
                        <th class="border border-gray-300 px-3 py-2.5 text-right">Jumlah Sumbangan (RM)</th>
                        @unless ($isAlpTable)
                            <th class="border border-gray-300 px-3 py-2.5 text-left">Nombor Pembekal</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-left">Nombor Bayaran</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-left">Tarikh Bayaran</th>
                        @endunless
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analytics['rows'] as $row)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-purple-50">
                            <td class="border border-gray-200 px-3 py-2">
                                <span class="block font-mono text-xs font-semibold text-gray-900">{{ $row['alp']?->ref_code ?? '—' }}</span>
                                <span class="block max-w-44 truncate text-xs text-gray-500" title="{{ $row['alp']?->name }}">{{ $row['alp']?->name }}</span>
                            </td>
                            @if ($isAlpTable)
                                @foreach (['in_process', 'rejected', 'remaining'] as $key)
                                    <td class="border border-gray-200 px-3 py-2 text-right tabular-nums text-gray-800">{{ number_format($row[$key], 2) }}</td>
                                @endforeach
                                <td class="border border-gray-200 px-3 py-2 text-right tabular-nums text-gray-800">{{ number_format($row['approved'], 2) }}</td>
                            @else
                                @foreach (['in_process', 'approved', 'rejected', 'remaining', 'total'] as $key)
                                    <td class="border border-gray-200 px-3 py-2 text-right tabular-nums text-gray-800">{{ number_format($row[$key], 2) }}</td>
                                @endforeach
                                <td class="border border-gray-200 px-3 py-2 font-mono text-xs text-gray-600">{{ $row['supplier_no'] ?: '—' }}</td>
                                <td class="border border-gray-200 px-3 py-2 font-mono text-xs text-gray-600">{{ $row['payment_no'] ?: '—' }}</td>
                                <td class="border border-gray-200 px-3 py-2 text-xs text-gray-600">{{ $row['payment_date']?->format('d/m/Y') ?? '—' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tableColspan }}" class="px-5 py-10 text-center text-sm text-gray-400">
                                Tiada data sumbangan bagi tempoh dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
