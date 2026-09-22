{{-- Alias — kekalkan untuk halaman sedia ada. --}}
@props([
    'application',
    'documents',
])

<x-document-preview :application="$application" :documents="$documents" layout="grid" empty="Tiada lampiran." />
