@props(['class' => ''])

@php
    $guide = app(\App\Services\Documents\AssociationDocumentGuideService::class);
@endphp

<a href="{{ $guide->downloadUrl() }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 font-medium text-royal-700 underline hover:text-royal-800 '.$class]) }}>
    <x-icon name="download" class="h-4 w-4" />
    Muat turun Panduan Dokumen Persatuan (PDF + Laporan Program + Borang EFT)
</a>
