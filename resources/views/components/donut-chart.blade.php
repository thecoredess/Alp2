@props([
    /** @var list<array{label: string, value: float|int, color: string}> */
    'slices' => [],
    'size' => 148,
    'thickness' => 30,
    'centerLabel' => null,
    'centerValue' => null,
])

@php
    $size = (int) $size;
    $thickness = (int) $thickness;
    $radius = ($size - $thickness) / 2;
    $center = $size / 2;
    $circumference = 2 * M_PI * $radius;

    $clean = collect($slices)
        ->map(fn ($s) => [
            'label' => $s['label'],
            'value' => max(0, (float) $s['value']),
            'color' => $s['color'],
        ])
        ->all();

    $total = array_sum(array_column($clean, 'value'));

    // Segmen dilukis guna stroke-dasharray supaya tidak bergantung pada kelas CSS.
    $segments = [];
    $offset = 0.0;
    foreach ($clean as $slice) {
        if ($total <= 0 || $slice['value'] <= 0) {
            continue;
        }
        $length = ($slice['value'] / $total) * $circumference;
        $segments[] = [
            'color' => $slice['color'],
            'dash' => round($length, 3).' '.round($circumference - $length, 3),
            'offset' => round(-$offset, 3),
            'percent' => ($slice['value'] / $total) * 100,
            'label' => $slice['label'],
            'value' => $slice['value'],
        ];
        $offset += $length;
    }
@endphp

<div class="flex flex-wrap items-center justify-center gap-5">
    <svg
        width="{{ $size }}"
        height="{{ $size }}"
        viewBox="0 0 {{ $size }} {{ $size }}"
        role="img"
        aria-label="{{ $centerLabel ?? 'Carta pai' }}"
        style="flex: none"
    >
        <g transform="rotate(-90 {{ $center }} {{ $center }})">
            <circle
                cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}"
                fill="none" stroke="#e5e7eb" stroke-width="{{ $thickness }}"
            />
            @foreach ($segments as $segment)
                <circle
                    cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}"
                    fill="none"
                    stroke="{{ $segment['color'] }}"
                    stroke-width="{{ $thickness }}"
                    stroke-dasharray="{{ $segment['dash'] }}"
                    stroke-dashoffset="{{ $segment['offset'] }}"
                >
                    <title>{{ $segment['label'] }}: RM {{ number_format($segment['value'], 2) }} ({{ number_format($segment['percent'], 1) }}%)</title>
                </circle>
            @endforeach
        </g>

        @if ($centerValue !== null)
            <text x="{{ $center }}" y="{{ $center - 2 }}" text-anchor="middle" font-size="13" font-weight="700" fill="#111827">
                {{ $centerValue }}
            </text>
            <text x="{{ $center }}" y="{{ $center + 14 }}" text-anchor="middle" font-size="9" fill="#6b7280">
                {{ $centerLabel ?? 'Jumlah' }}
            </text>
        @elseif ($centerLabel)
            <text x="{{ $center }}" y="{{ $center + 4 }}" text-anchor="middle" font-size="12" font-weight="700" fill="#374151">
                {{ $centerLabel }}
            </text>
        @endif
    </svg>

    <ul class="space-y-2 text-xs text-gray-600">
        @foreach ($clean as $slice)
            @php $percent = $total > 0 ? ($slice['value'] / $total) * 100 : 0; @endphp
            <li>
                <span class="flex items-center gap-2">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="flex: none">
                        <rect width="10" height="10" rx="2" fill="{{ $slice['color'] }}" />
                    </svg>
                    <span class="text-gray-600">{{ $slice['label'] }}</span>
                </span>
                <span class="ml-4 block font-semibold text-gray-900">
                    RM {{ number_format($slice['value'], 2) }}
                    <span class="font-normal text-gray-400">· {{ number_format($percent, 1) }}%</span>
                </span>
            </li>
        @endforeach
    </ul>
</div>
