{{-- Trend belanja bersih bulanan — bar menegak SVG ringan. $monthly: [['month'=>1,'net'=>Money],...] --}}
@php
    $labels = ['','Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogo','Sep','Okt','Nov','Dis'];
    $max = 0.0;
    foreach ($monthly as $m) { $max = max($max, (float) $m['net']->value()); }
    $max = $max > 0 ? $max : 1;
@endphp
<div class="flex items-end gap-2" style="height: 160px">
    @foreach ($monthly as $m)
        @php $v = (float) $m['net']->value(); $h = $max > 0 ? max(2, ($v / $max) * 140) : 2; @endphp
        <div class="flex flex-1 flex-col items-center justify-end gap-1">
            <div class="w-full rounded-t bg-purple-500/80" style="height: {{ number_format($h, 1) }}px" title="{{ $m['net']->format() }}"></div>
            <span class="text-[10px] text-gray-400">{{ $labels[$m['month']] ?? $m['month'] }}</span>
        </div>
    @endforeach
</div>
<p class="mt-2 text-xs text-gray-400">Belanja Bersih = Perbelanjaan − Refund, mengikut bulan diposkan ke lejar.</p>
