@props([
    'user' => null,
    'size' => 'md', // sm | md | lg
])

@php
    $user = $user ?? auth()->user();
    $sizes = [
        'sm' => 'h-8 w-8 text-sm',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-20 w-20 text-2xl',
    ];
    $box = $sizes[$size] ?? $sizes['md'];
    $iconSize = $size === 'lg' ? 'h-9 w-9' : ($size === 'sm' ? 'h-4 w-4' : 'h-5 w-5');
    $url = $user?->avatarUrl();
    $icon = $user?->avatar_icon;
@endphp

@if ($url)
    <img src="{{ $url }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => "$box rounded-full object-cover ring-1 ring-black/5"]) }}>
@elseif ($icon && \App\Support\ProfileAvatarIcons::isValid($icon))
    <span {{ $attributes->merge(['class' => "grid $box place-items-center rounded-full bg-navy-100 text-navy-700 ring-1 ring-navy-200/60"]) }}>
        <x-profile-icon :name="$icon" class="{{ $iconSize }}" />
    </span>
@else
    <span {{ $attributes->merge(['class' => "grid $box place-items-center rounded-full bg-navy-100 font-semibold text-navy-700"]) }}>
        {{ $user?->avatarInitial() ?? '?' }}
    </span>
@endif
