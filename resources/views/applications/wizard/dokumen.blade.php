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

        <div class="rounded-lg border border-navy-100 bg-navy-50 px-4 py-3 text-sm text-navy-800">
            Tekan <strong>Pilih Fail</strong> pada setiap item. Format: PDF atau gambar (maksimum 10 MB).
            Borang Penyaluran sudah diisi — tidak perlu muat naik semula.
            <x-association-guide-links class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @foreach ($requiredTypes as $index => $type)
                @php
                    $doc = $docsByType->get($type->value);
                    $done = $doc !== null;
                @endphp
                <div @class([
                    'card overflow-hidden border-2 p-5',
                    'border-red-400 bg-red-50 ring-1 ring-red-200' => ($jpIncomplete['dokumen'] ?? null) !== null,
                    'border-green-200 bg-green-50/40' => ($jpIncomplete['dokumen'] ?? null) === null && $done,
                    'border-gray-200' => ($jpIncomplete['dokumen'] ?? null) === null && ! $done,
                ])>
                    <div class="flex items-start gap-4">
                        <span @class([
                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-lg font-bold',
                            'bg-green-600 text-white' => $done,
                            'bg-gray-200 text-gray-600' => ! $done,
                        ])>{{ $done ? '✓' : ($index + 1) }}</span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-gray-900">{{ $type->simpleLabel() }}</h3>
                                @if ($done)
                                    <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Selesai</span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Belum</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-600">{{ $type->simpleHint() }}</p>

                            @if ($done)
                                <p class="mt-2 truncate text-sm font-medium text-gray-800" title="{{ $doc->original_filename }}">{{ $doc->original_filename }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('applications.documents.store', $application) }}" enctype="multipart/form-data" class="inline">
                                        @csrf
                                        <input type="hidden" name="document_type" value="{{ $type->value }}">
                                        <label class="btn-white cursor-pointer !px-3 !py-2 text-xs">
                                            Tukar Fail
                                            <input type="file" name="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                                   onchange="if(this.files.length) this.form.requestSubmit()">
                                        </label>
                                    </form>
                                    <form method="POST" action="{{ route('applications.documents.destroy', [$application, $doc]) }}"
                                          data-swal-confirm="Buang fail ini?"
                                          data-swal-title="Buang Lampiran"
                                          data-swal-icon="warning"
                                          data-swal-confirm-text="Ya, buang">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-white !px-3 !py-2 text-xs text-danger">Buang</button>
                                    </form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('applications.documents.store', $application) }}" enctype="multipart/form-data" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $type->value }}">
                                    <label class="btn-primary inline-flex cursor-pointer !px-4 !py-2 text-sm">
                                        Pilih Fail
                                        <input type="file" name="file" class="sr-only" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                               onchange="if(this.files.length) this.form.requestSubmit()">
                                    </label>
                                </form>
                                @error('file')
                                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
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
