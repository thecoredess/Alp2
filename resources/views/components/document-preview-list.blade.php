{{-- Alias — kekalkan untuk halaman sedia ada. --}}
@props([
    'application',
    'documents',
    'viewOnly' => null,
])

<x-document-preview
    :application="$application"
    :documents="$documents"
    layout="grid"
    empty="Tiada lampiran."
    :view-only="$viewOnly"
/>
