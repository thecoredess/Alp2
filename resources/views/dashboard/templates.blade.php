@extends('layouts.app')
@section('title', 'Templat Dashboard Analisa')
@section('heading', 'Templat Dashboard Analisa Sumbangan')
@section('subheading', 'Data contoh — untuk semakan reka bentuk sahaja')

@php
    // ── Data contoh (bukan data sebenar sistem) ──────────────────────────
    $kpi = ['jumlah' => 128400, 'proses' => 18600, 'lulus' => 84200, 'tolak' => 25600];

    $penggal = [
        ['label' => 'Penggal 1', 'value' => 41200, 'color' => '#86efac'],
        ['label' => 'Penggal 2', 'value' => 26800, 'color' => '#4ade80'],
        ['label' => 'Penggal 3', 'value' => 16200, 'color' => '#15803d'],
    ];

    $status = [
        ['label' => 'Dalam Proses', 'value' => 18600, 'color' => '#c2410c'],
        ['label' => 'Lulus', 'value' => 84200, 'color' => '#ea8a5a'],
        ['label' => 'Ditolak', 'value' => 25600, 'color' => '#fbd5c0'],
    ];

    $bulanLabels = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogs','Sep','Okt','Nov','Dis'];
    $bulanValues = [2100, 2800, 1900, 3200, 2600, 3100, 2400, 2900, 3500, 2700, 2200, 1800];

    $jadual = [
        ['ALP-01', 4200, 12800, 1500, 18500, 36500, 'SUP-1021', 'BYR-8841', '12/08/2026'],
        ['ALP-02', 6100, 9400, 800, 11200, 27500, 'SUP-1104', 'BYR-8902', '28/07/2026'],
        ['ALP-03', 3800, 15600, 2200, 9800, 31400, 'SUP-0988', '—', '—'],
        ['ALP-04', 2500, 8200, 3100, 14400, 28200, 'SUP-1210', 'BYR-8760', '03/08/2026'],
        ['ALP-05', 2000, 38200, 18000, 22300, 80500, 'SUP-1315', 'BYR-9011', '01/09/2026'],
    ];

    // Helper: bina CSS conic-gradient untuk donut
    $donut = function (array $slices): string {
        $total = array_sum(array_column($slices, 'value')) ?: 1;
        $stops = [];
        $cursor = 0.0;
        foreach ($slices as $s) {
            $pct = ($s['value'] / $total) * 100;
            $stops[] = $s['color'].' '.round($cursor, 3).'% '.round($cursor + $pct, 3).'%';
            $cursor += $pct;
        }
        return 'conic-gradient('.implode(', ', $stops).')';
    };

    $maxBulan = max($bulanValues) ?: 1;

    // Titik untuk polyline carta garis
    $chartW = 640; $chartH = 150; $padL = 40; $padB = 24;
    $stepX = ($chartW - $padL - 10) / (count($bulanValues) - 1);
    $points = [];
    foreach ($bulanValues as $i => $v) {
        $x = $padL + ($i * $stepX);
        $y = ($chartH - $padB) - (($v / $maxBulan) * ($chartH - $padB - 10));
        $points[] = round($x, 1).','.round($y, 1);
    }
    $polyline = implode(' ', $points);
@endphp

@section('content')
    <x-page-shell>
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <strong>Pratonton reka bentuk.</strong> Semua angka di halaman ini adalah data contoh.
            Pilih templat yang sesuai, kemudian ia akan disambung kepada data sebenar.
        </div>

        {{-- ══════════════ TEMPLAT A ══════════════ --}}
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Templat A — Analisa Penuh</h2>
            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">Paling sepadan mockup</span>
        </div>

        {{-- KPI pastel seperti mockup --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
                ['label' => 'JUMLAH SUMBANGAN', 'value' => $kpi['jumlah'], 'bg' => 'bg-green-50', 'border' => 'border-green-300', 'icon' => 'wallet', 'ring' => 'bg-cyan-100 text-cyan-700'],
                ['label' => 'JUMLAH SUMBANGAN DALAM PROSES', 'value' => $kpi['proses'], 'bg' => 'bg-orange-50', 'border' => 'border-orange-300', 'icon' => 'arrow-path', 'ring' => 'bg-orange-100 text-orange-700'],
                ['label' => 'JUMLAH SUMBANGAN DILULUSKAN', 'value' => $kpi['lulus'], 'bg' => 'bg-blue-50', 'border' => 'border-blue-300', 'icon' => 'banknotes', 'ring' => 'bg-blue-100 text-blue-700'],
            ] as $card)
                <div class="flex items-center gap-4 rounded-2xl border-2 {{ $card['border'] }} {{ $card['bg'] }} p-5">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $card['ring'] }}">
                        <x-icon :name="$card['icon']" class="h-7 w-7" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase leading-tight tracking-wide text-gray-700">{{ $card['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">RM {{ number_format($card['value']) }}</p>
                        <p class="text-xs italic text-gray-500">(Keseluruhan)</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tiga graf --}}
        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Donut baki penggal --}}
            <div class="overflow-hidden rounded-2xl border-2 border-gray-300">
                <div class="bg-green-700 px-4 py-2.5 text-center text-sm font-bold uppercase text-white">Baki Sumbangan</div>
                <div class="flex items-center justify-center gap-5 p-5">
                    <div class="relative h-32 w-32 shrink-0 rounded-full" style="background: {{ $donut($penggal) }}">
                        <div class="absolute inset-8 flex items-center justify-center rounded-full bg-white text-xs font-bold text-gray-700">RM</div>
                    </div>
                    <ul class="space-y-1.5 text-xs text-gray-600">
                        @foreach ($penggal as $p)
                            <li class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-sm" style="background: {{ $p['color'] }}"></span>
                                {{ $p['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Donut status --}}
            <div class="overflow-hidden rounded-2xl border-2 border-gray-300">
                <div class="bg-yellow-400 px-4 py-2.5 text-center text-sm font-bold uppercase text-gray-900">Status Kelulusan</div>
                <div class="flex items-center justify-center gap-5 p-5">
                    <div class="relative h-32 w-32 shrink-0 rounded-full" style="background: {{ $donut($status) }}">
                        <div class="absolute inset-8 flex items-center justify-center rounded-full bg-white text-xs font-bold text-gray-700">RM</div>
                    </div>
                    <ul class="space-y-1.5 text-xs text-gray-600">
                        @foreach ($status as $s)
                            <li class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-sm" style="background: {{ $s['color'] }}"></span>
                                {{ $s['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Garis bulanan --}}
            <div class="overflow-hidden rounded-2xl border-2 border-gray-300">
                <div class="bg-blue-500 px-4 py-2.5 text-center text-sm font-bold uppercase leading-tight text-white">Jumlah Sumbangan Mengikut Bulan</div>
                <div class="p-3">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full" style="height: 150px">
                        @foreach ([0, 2000, 4000] as $tick)
                            @php $ty = ($chartH - $padB) - (($tick / $maxBulan) * ($chartH - $padB - 10)); @endphp
                            <line x1="{{ $padL }}" y1="{{ round($ty, 1) }}" x2="{{ $chartW - 10 }}" y2="{{ round($ty, 1) }}" stroke="#e5e7eb" stroke-width="1" />
                            <text x="4" y="{{ round($ty + 3, 1) }}" font-size="9" fill="#9ca3af">{{ number_format($tick) }}</text>
                        @endforeach
                        <polyline points="{{ $polyline }}" fill="none" stroke="#2563eb" stroke-width="2.5" />
                        @foreach ($bulanLabels as $i => $label)
                            <text x="{{ round($padL + ($i * $stepX), 1) }}" y="{{ $chartH - 6 }}" font-size="8" fill="#9ca3af" text-anchor="middle">{{ $label }}</text>
                        @endforeach
                    </svg>
                    <p class="text-center text-xs text-gray-500">RM mengikut bulan · 2026</p>
                </div>
            </div>
        </div>

        {{-- Penapis tempoh + jadual --}}
        <div class="mt-5 overflow-hidden rounded-2xl border-2 border-gray-300">
            <div class="flex flex-col gap-3 bg-fuchsia-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-base font-bold uppercase text-gray-900">Ringkasan Sumbangan</h3>
                <div class="flex flex-wrap items-center gap-2 rounded-xl bg-white/70 px-3 py-2">
                    <span class="text-[11px] font-bold uppercase text-gray-600">Tempoh Laporan</span>
                    <input type="date" class="rounded-lg border-gray-300 px-2 py-1 text-xs" value="2026-01-01">
                    <span class="text-xs font-medium text-gray-600">hingga</span>
                    <input type="date" class="rounded-lg border-gray-300 px-2 py-1 text-xs" value="2026-09-08">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-purple-200 text-xs font-bold uppercase text-gray-800">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2.5 text-left">ALP</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-right">Dalam Proses (RM)</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-right">Diluluskan (RM)</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-right">Ditolak (RM)</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-right">Baki Sumbangan (RM)</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-right">Jumlah Sumbangan (RM)</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-left">Nombor Pembekal</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-left">Nombor Bayaran</th>
                            <th class="border border-gray-300 px-3 py-2.5 text-left">Tarikh Bayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadual as $row)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-200 px-3 py-2 font-mono text-gray-700">{{ $row[0] }}</td>
                                @foreach ([1, 2, 3, 4, 5] as $i)
                                    <td class="border border-gray-200 px-3 py-2 text-right text-gray-800">{{ number_format($row[$i]) }}</td>
                                @endforeach
                                <td class="border border-gray-200 px-3 py-2 font-mono text-xs text-gray-600">{{ $row[6] }}</td>
                                <td class="border border-gray-200 px-3 py-2 font-mono text-xs text-gray-600">{{ $row[7] }}</td>
                                <td class="border border-gray-200 px-3 py-2 text-xs text-gray-600">{{ $row[8] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ══════════════ TEMPLAT B ══════════════ --}}
        <div class="mb-4 mt-12 flex flex-wrap items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Templat B — Gaya Sistem Sedia Ada</h2>
            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">Selaras UI semasa</span>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-stat-card label="Jumlah Dimohon" icon="banknotes" tone="navy">RM {{ number_format($kpi['jumlah']) }}</x-stat-card>
            <x-stat-card label="Dalam Proses" icon="arrow-path" tone="amber">RM {{ number_format($kpi['proses']) }}</x-stat-card>
            <x-stat-card label="Diluluskan" icon="check" tone="green">RM {{ number_format($kpi['lulus']) }}</x-stat-card>
            <x-stat-card label="Ditolak" icon="x-circle" tone="red">RM {{ number_format($kpi['tolak']) }}</x-stat-card>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-page-card title="Trend Bulanan (RM)" icon="chart" class="lg:col-span-2">
                <div class="flex items-end gap-2" style="height: 170px">
                    @foreach ($bulanValues as $i => $v)
                        @php $h = max(4, ($v / $maxBulan) * 150); @endphp
                        <div class="flex flex-1 flex-col items-center justify-end gap-1">
                            <div class="w-full rounded-t bg-royal-500/80" style="height: {{ round($h, 1) }}px" title="RM {{ number_format($v) }}"></div>
                            <span class="text-[10px] text-gray-400">{{ $bulanLabels[$i] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-400">Jumlah RM dimohon mengikut bulan · 2026</p>
            </x-page-card>

            <x-page-card title="Status Permohonan (kes)" icon="chart">
                @php
                    $kes = [
                        ['label' => 'Draf', 'value' => 4, 'color' => '#9ca3af'],
                        ['label' => 'Dalam Proses', 'value' => 7, 'color' => '#3b82f6'],
                        ['label' => 'Pembetulan', 'value' => 2, 'color' => '#f97316'],
                        ['label' => 'Lulus', 'value' => 11, 'color' => '#22c55e'],
                        ['label' => 'Tolak', 'value' => 3, 'color' => '#ef4444'],
                    ];
                @endphp
                <div class="flex items-center gap-5">
                    <div class="relative h-28 w-28 shrink-0 rounded-full" style="background: {{ $donut($kes) }}">
                        <div class="absolute inset-7 flex items-center justify-center rounded-full bg-white text-sm font-bold text-navy-700">27</div>
                    </div>
                    <ul class="space-y-1 text-xs text-gray-600">
                        @foreach ($kes as $k)
                            <li class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-sm" style="background: {{ $k['color'] }}"></span>
                                {{ $k['label'] }} <span class="font-semibold text-gray-900">{{ $k['value'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-page-card>
        </div>

        {{-- ══════════════ TEMPLAT C ══════════════ --}}
        <div class="mb-4 mt-12 flex flex-wrap items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Templat C — Padat (Mudah Alih)</h2>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">Skop kecil</span>
        </div>

        <div class="max-w-md space-y-4 rounded-2xl border-2 border-dashed border-gray-300 p-4">
            <div class="grid grid-cols-3 gap-2">
                @foreach ([['Jumlah', '128k', 'bg-green-50 text-green-800'], ['Proses', '19k', 'bg-orange-50 text-orange-800'], ['Lulus', '84k', 'bg-blue-50 text-blue-800']] as $c)
                    <div class="rounded-xl {{ $c[2] }} px-3 py-3 text-center">
                        <p class="text-[10px] font-bold uppercase">{{ $c[0] }}</p>
                        <p class="text-lg font-bold">RM {{ $c[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <p class="mb-3 text-sm font-semibold text-gray-900">Status Kelulusan</p>
                <div class="flex items-center gap-4">
                    <div class="relative h-24 w-24 shrink-0 rounded-full" style="background: {{ $donut($status) }}">
                        <div class="absolute inset-6 rounded-full bg-white"></div>
                    </div>
                    <ul class="space-y-1 text-xs text-gray-600">
                        @foreach ($status as $s)
                            <li class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-sm" style="background: {{ $s['color'] }}"></span>{{ $s['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <p class="mb-3 text-sm font-semibold text-gray-900">Trend 12 Bulan</p>
                <div class="flex items-end gap-1" style="height: 90px">
                    @foreach ($bulanValues as $v)
                        <div class="flex-1 rounded-t bg-royal-400" style="height: {{ round(max(4, ($v / $maxBulan) * 80), 1) }}px"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-10 rounded-xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-600">
            <p class="font-semibold text-gray-900">Sumber data bila disambung nanti</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                <li>KPI RM — <code>requested_amount</code> mengikut status permohonan</li>
                <li>Donut baki penggal — kuota tempoh URS (jika dasar diaktifkan)</li>
                <li>Donut status — kiraan / jumlah RM mengikut status</li>
                <li>Garis bulanan — <code>submitted_at</code> atau tarikh kelulusan</li>
                <li>Jadual — permohonan tahun aktif + no. pembekal, no. baucar, tarikh bayar (borang JKEW)</li>
                <li>Penapis tarikh — perlu dibina baharu</li>
            </ul>
        </div>
    </x-page-shell>
@endsection
