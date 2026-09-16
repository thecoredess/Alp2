@props([
    'icon',
    'label',
    'href' => null,
    'variant' => 'default',
])

@php
    $classes = match ($variant) {
        'primary' => 'text-royal-600 hover:bg-royal-50 hover:text-royal-700',
        'success' => 'text-green-600 hover:bg-green-50 hover:text-green-700',
        'warning' => 'text-amber-600 hover:bg-amber-50 hover:text-amber-700',
        'danger' => 'text-danger hover:bg-red-50 hover:text-red-700',
        'muted' => 'text-gray-500 hover:bg-gray-100 hover:text-gray-700',
        default => 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
    };
    $base = "inline-flex h-8 w-8 items-center justify-center rounded-lg transition {$classes}";
@endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $base]) }}>
        <x-icon :name="$icon" class="h-4 w-4" />
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $base]) }}>
        <x-icon :name="$icon" class="h-4 w-4" />
    </button>
@endif
