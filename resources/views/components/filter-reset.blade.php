@props([
    'href' => null,
    'label' => 'Reset',
])

@php
    $resetUrl = $href ?? url()->current();
@endphp

<a href="{{ $resetUrl }}" {{ $attributes->merge(['class' => 'btn-primary shrink-0']) }}>{{ $label }}</a>
