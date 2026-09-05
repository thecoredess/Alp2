@props(['label', 'classes' => 'bg-gray-100 text-gray-700'])

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $label }}
</span>
