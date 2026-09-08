@props([
    'label',
    'icon',
    'tone' => 'navy',
    'href' => null,
    'hint' => null,
])

@php
    $tones = [
        'navy'   => ['icon' => 'bg-navy-50 text-navy-600',   'value' => 'text-navy-700',   'blob' => 'bg-navy-500',   'hover' => 'hover:border-navy-200 hover:shadow-navy-100/50'],
        'royal'  => ['icon' => 'bg-royal-50 text-royal-600', 'value' => 'text-royal-600',  'blob' => 'bg-royal-500',  'hover' => 'hover:border-royal-200 hover:shadow-royal-100/50'],
        'blue'   => ['icon' => 'bg-blue-50 text-blue-600',   'value' => 'text-blue-600',   'blob' => 'bg-blue-500',   'hover' => 'hover:border-blue-200 hover:shadow-blue-100/50'],
        'amber'  => ['icon' => 'bg-amber-50 text-amber-600', 'value' => 'text-amber-600',  'blob' => 'bg-amber-500',  'hover' => 'hover:border-amber-200 hover:shadow-amber-100/50'],
        'orange' => ['icon' => 'bg-orange-50 text-orange-600','value' => 'text-orange-600', 'blob' => 'bg-orange-500', 'hover' => 'hover:border-orange-200 hover:shadow-orange-100/50'],
        'green'  => ['icon' => 'bg-green-50 text-green-600', 'value' => 'text-green-600',  'blob' => 'bg-green-500',  'hover' => 'hover:border-green-200 hover:shadow-green-100/50'],
        'red'    => ['icon' => 'bg-red-50 text-red-600',     'value' => 'text-danger',     'blob' => 'bg-red-500',    'hover' => 'hover:border-red-200 hover:shadow-red-100/50'],
        'gray'   => ['icon' => 'bg-gray-100 text-gray-600',  'value' => 'text-gray-600',   'blob' => 'bg-gray-400',   'hover' => 'hover:border-gray-300 hover:shadow-gray-100/50'],
        'purple' => ['icon' => 'bg-purple-50 text-purple-600','value' => 'text-purple-600', 'blob' => 'bg-purple-500', 'hover' => 'hover:border-purple-200 hover:shadow-purple-100/50'],
        'teal'   => ['icon' => 'bg-teal-50 text-teal-600',   'value' => 'text-teal-600',   'blob' => 'bg-teal-500',   'hover' => 'hover:border-teal-200 hover:shadow-teal-100/50'],
    ];
    $t = $tones[$tone] ?? $tones['navy'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "stat-card group {$t['hover']}"]) }}
>
    <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full opacity-[0.06] {{ $t['blob'] }} transition group-hover:opacity-[0.1]"></div>

    <div class="relative flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <div @class(['mt-2 text-2xl font-bold tracking-tight sm:text-3xl', $t['value']])>
                {{ $slot }}
            </div>
            @if ($hint)
                <p class="mt-1.5 text-xs leading-relaxed text-gray-400">{{ $hint }}</p>
            @endif
        </div>
        <div @class(['flex h-11 w-11 shrink-0 items-center justify-center rounded-xl shadow-sm ring-1 ring-black/[0.04]', $t['icon']])>
            <x-icon :name="$icon" class="h-5 w-5" />
        </div>
    </div>

    @if ($href)
        <div class="relative mt-3 flex items-center gap-1 text-xs font-medium text-gray-400 opacity-0 transition group-hover:opacity-100">
            <span>Lihat senarai</span>
            <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </div>
    @endif
</{{ $tag }}>
