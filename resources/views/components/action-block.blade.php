@props([
    'label',
    'icon',
    'tone' => 'royal',
    'href',
    'description' => null,
    'count' => 0,
])

@php
    $tones = [
        'navy'  => ['border' => 'border-l-navy-600',   'icon' => 'bg-navy-600 text-white',   'count' => 'text-navy-700',   'bg' => 'hover:border-navy-200 hover:bg-navy-50/30'],
        'royal' => ['border' => 'border-l-royal-500',  'icon' => 'bg-royal-500 text-white',  'count' => 'text-royal-600',  'bg' => 'hover:border-royal-200 hover:bg-royal-50/30'],
        'blue'  => ['border' => 'border-l-blue-500',   'icon' => 'bg-blue-500 text-white',   'count' => 'text-blue-600',   'bg' => 'hover:border-blue-200 hover:bg-blue-50/30'],
        'amber' => ['border' => 'border-l-amber-500',  'icon' => 'bg-amber-500 text-white',  'count' => 'text-amber-600',  'bg' => 'hover:border-amber-200 hover:bg-amber-50/30'],
        'green' => ['border' => 'border-l-green-500',  'icon' => 'bg-green-500 text-white',  'count' => 'text-green-600',  'bg' => 'hover:border-green-200 hover:bg-green-50/30'],
    ];
    $t = $tones[$tone] ?? $tones['royal'];
    $hasItems = (int) $count > 0;
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "action-block group {$t['border']} {$t['bg']}"]) }}>
    <div class="flex items-start gap-4">
        <div @class(['flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl shadow-sm', $t['icon']])>
            <x-icon :name="$icon" class="h-7 w-7" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ $label }}</p>
            @if ($description)
                <p class="mt-1 text-xs leading-relaxed text-gray-500">{{ $description }}</p>
            @endif
        </div>
    </div>

    <div class="mt-5 flex items-end justify-between gap-3 border-t border-gray-100 pt-4">
        <div>
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Menunggu tindakan</p>
            <p @class(['mt-0.5 text-4xl font-bold tabular-nums leading-none', $t['count'], 'opacity-40' => ! $hasItems])>{{ $count }}</p>
        </div>
        <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm ring-1 ring-gray-200 transition group-hover:bg-gray-50 group-hover:text-royal-700">
            Buka giliran
            <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </span>
    </div>
</a>
