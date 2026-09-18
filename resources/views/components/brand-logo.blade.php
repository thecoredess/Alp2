@props([
    'variant' => 'full', // full | compact | login | sidebar
    'alt' => 'Sistem Sumbangan ALP',
])

@php
    $src = asset('images/logo-alp-dbkl.png').'?v='.(@filemtime(public_path('images/logo-alp-dbkl.png')) ?: time());
    $classes = match ($variant) {
        'login' => 'mx-auto h-14 w-auto max-w-full object-contain',
        'sidebar' => 'h-full w-full max-w-full object-contain object-center',
        'compact' => 'h-8 w-auto max-w-[13rem] object-contain object-left',
        default => 'mx-auto h-auto w-full max-w-[240px] object-contain',
    };
@endphp

<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => $classes]) }} decoding="async">
