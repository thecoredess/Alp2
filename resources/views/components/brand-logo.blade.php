@props([
    'variant' => 'full', // full | compact | login
    'alt' => 'Logo DBKL — Sistem Pengurusan Sumbangan ALP',
])

@php
    $src = asset('images/logo-alp-dbkl.png');
    $classes = match ($variant) {
        'login' => 'mx-auto h-14 w-auto max-w-full object-contain',
        'compact' => 'h-8 w-auto max-w-[13rem] object-contain object-left',
        default => 'mx-auto h-auto w-full max-w-[240px] object-contain',
    };
@endphp

<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => $classes]) }} decoding="async">
