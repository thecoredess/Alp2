@extends('layouts.app')
@section('title', 'Muat Naik Lampiran')
@section('heading', 'Permohonan — '.$application->application_number)
@section('subheading', 'Langkah 2 · Muat naik dokumen sokongan')

@php
    $requiredTypes = $requirements->map(fn ($r) => $r->document_type)->values();
    $docsByType = $application->documents
        ->groupBy(fn ($d) => $d->document_type->value)
        ->map(fn ($group) => $group->sortByDesc('id')->first());
    $doneCount = $requiredTypes->filter(fn ($t) => $docsByType->has($t->value))->count();
    $totalCount = $requiredTypes->count();
    $allDone = $doneCount === $totalCount && $totalCount > 0;
    $progress = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;
    $jpIncomplete = $jpIncomplete ?? [];
@endphp

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    <x-page-shell>
        @include('applications.partials.program-date-warning')
        @include('applications.partials.jp-incomplete-banner')
        @include('applications.partials.association-alp-review-banner')
        @include('applications.partials.association-invite-card')

        <x-page-card title="Kemajuan Muat Naik" icon="paper-clip">
            <p class="text-3xl font-bold text-navy-700">{{ $doneCount }}/{{ $totalCount }}</p>
            <p class="mt-1 text-sm text-gray-500">dokumen selesai</p>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-green-500 transition-all" style="width: {{ $progress }}%"></div>
            </div>
            @if ($allDone)
                <p class="mt-3 text-sm font-medium text-green-700">Semua lengkap — boleh ke langkah hantar.</p>
            @else
                <p class="mt-3 text-sm text-amber-700">{{ $totalCount - $doneCount }} dokumen lagi diperlukan.</p>
            @endif
        </x-page-card>

        <div class="mt-4 text-sm text-gray-600">
            Borang Penyaluran sudah diisi — tidak perlu muat naik semula.
        </div>

        @include('applications.partials.contribution-document-upload-grid', [
            'documentStoreUrl' => route('applications.documents.store', $application),
            'documentDestroyUrl' => fn ($doc) => route('applications.documents.destroy', [$application, $doc]),
            'confirmDestroy' => true,
            'showProgress' => false,
        ])

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-between">
            <a href="{{ route('applications.wizard.maklumat', $application) }}" class="btn-white">← Kembali ke Borang</a>
            @if ($allDone)
                <a href="{{ route('applications.wizard.semakan', $application) }}" class="btn-primary">Seterusnya: Semak &amp; Hantar →</a>
            @else
                <span class="btn-primary pointer-events-none opacity-50" title="Lengkapkan semua dokumen dahulu">
                    Seterusnya ({{ $totalCount - $doneCount }} lagi)
                </span>
            @endif
        </div>
    </x-page-shell>
@endsection
