@props([
    'compact' => false,
])

@php
    $themes = \App\Support\KertasKerjaGuide::themes();
    $minimum = \App\Support\KertasKerjaGuide::minimumSections();
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-navy-200 bg-navy-50/80 px-4 py-3.5 text-sm text-navy-900']) }}>
    @unless ($compact)
        <p class="font-semibold text-navy-800">Kertas Kerja — kandungan yang perlu dijelaskan</p>
        <p class="mt-1.5 text-navy-800/90">{{ \App\Support\KertasKerjaGuide::intro() }}</p>
        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-navy-600">Aspek program</p>
        <ul class="mt-1.5 grid gap-1 sm:grid-cols-2">
            @foreach ($themes as $theme)
                <li class="flex gap-2">
                    <span class="text-royal-600" aria-hidden="true">•</span>
                    <span>{{ $theme }}</span>
                </li>
            @endforeach
        </ul>
    @endunless

    <p @class([
        'text-xs font-semibold uppercase tracking-wide text-navy-600',
        'mt-3' => ! $compact,
        'mt-0' => $compact,
    ])>Isi minimum kertas kerja</p>
    <ul class="mt-1.5 list-inside list-disc space-y-1 text-navy-800/95">
        @foreach ($minimum as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
</div>
