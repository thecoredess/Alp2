{{-- Carta bar mendatar tulen-SVG: Belanja Bersih vs Peruntukan mengikut ALP.
     Param: $rows (koleksi ['alp'=>, 'breakdown'=>FinancialBreakdown]). Tiada pustaka luar. --}}
@php
    use App\Support\Money;
    $maxAlloc = 0.0;
    foreach ($rows as $r) {
        $maxAlloc = max($maxAlloc, (float) $r['breakdown']->allocation->value());
    }
    $maxAlloc = $maxAlloc > 0 ? $maxAlloc : 1;
    $barH = 22; $gap = 14; $labelW = 70; $trackW = 100; // trackW dalam peratus lebar
@endphp
<div class="space-y-3">
    @foreach ($rows as $r)
        @php
            $alloc = (float) $r['breakdown']->allocation->value();
            $net = (float) $r['breakdown']->netSpent()->value();
            $allocPct = $maxAlloc > 0 ? ($alloc / $maxAlloc) * 100 : 0;
            $netPct = $maxAlloc > 0 ? ($net / $maxAlloc) * 100 : 0;
            $util = $r['breakdown']->netUtilisationPercent();
        @endphp
        <div class="flex items-center gap-3 text-xs">
            <div class="w-16 shrink-0 font-mono text-gray-600">{{ $r['alp']->ref_code }}</div>
            <div class="relative h-5 flex-1 rounded bg-gray-100">
                {{-- Peruntukan (rangka) --}}
                <div class="absolute inset-y-0 left-0 rounded bg-navy-100" style="width: {{ number_format($allocPct, 2) }}%"></div>
                {{-- Belanja bersih (isian) --}}
                <div class="absolute inset-y-0 left-0 rounded bg-purple-500" style="width: {{ number_format($netPct, 2) }}%"></div>
            </div>
            <div class="w-28 shrink-0 text-right text-gray-500"><x-money :value="$r['breakdown']->netSpent()" /></div>
            <div class="w-12 shrink-0 text-right font-medium text-gray-700">{{ number_format($util, 0) }}%</div>
        </div>
    @endforeach
</div>
<div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
    <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-navy-100"></span> Peruntukan</span>
    <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-purple-500"></span> Belanja Bersih</span>
</div>
