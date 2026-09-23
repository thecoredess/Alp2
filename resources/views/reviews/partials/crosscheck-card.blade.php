@props([
    'application',
    'crosscheckDocument' => null,
    'uploadAction' => null,
    'uploadRequiredForRecommend' => false,
])

@php
    $canDownloadMemo = auth()->user()->can('downloadCrosscheckMemo', $application);
    $canUpload = auth()->user()->can('uploadCrosscheckMemo', $application);
    $canViewReference = auth()->user()->can('viewCrosscheckReference', $application);
@endphp

@if ($canDownloadMemo || $canUpload || ($canViewReference && $crosscheckDocument))
    <x-page-card
        title="Semakan Silang Permohonan Persatuan"
        icon="document"
        :description="$canDownloadMemo || $canUpload ? 'Memo ulasan JKEW ke JP — semakan silang persatuan (JPKKB)' : null"
    >
        <div class="space-y-4">
            @if ($canDownloadMemo)
                <div class="rounded-xl border border-royal-100 bg-gradient-to-br from-royal-50/80 to-white p-4">
                    <p class="text-sm text-gray-700">
                        Muat turun borang memo JKEW dengan maklumat permohonan telah diisi. Hantar kepada JKEW untuk ulasan semakan silang.
                    </p>
                    <a href="{{ route('applications.crosscheck.memo.doc', $application) }}"
                       class="btn-navy mt-3 inline-flex items-center gap-2 text-sm">
                        <x-icon name="download" class="h-4 w-4" />
                        Muat Turun Borang (Word)
                    </a>
                    <p class="mt-2 text-xs text-gray-500">Tarikh memo (Masihi &amp; Hijrah) diisi automatik mengikut tarikh semasa.</p>
                </div>
            @endif

            @if ($crosscheckDocument && $canViewReference)
                <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-4 text-sm">
                    <p class="font-medium text-gray-900">Borang Ulasan JKEW</p>
                    <p class="mt-1 text-xs text-gray-500">
                        Dimuat naik {{ $crosscheckDocument->created_at?->format('d/m/Y H:i') }}
                        @if ($crosscheckDocument->uploader)
                            · {{ $crosscheckDocument->uploader->name }}
                        @endif
                    </p>
                    <p class="mt-1 truncate text-xs text-gray-500" title="{{ $crosscheckDocument->original_filename }}">
                        {{ $crosscheckDocument->original_filename }}
                    </p>
                    <div class="mt-3">
                        <x-document-preview
                            :application="$application"
                            :documents="collect([$crosscheckDocument])"
                            layout="actions"
                        />
                    </div>
                </div>
            @elseif ($canViewReference)
                <p @class(['text-sm', 'text-danger font-medium' => $uploadRequiredForRecommend && ! $crosscheckDocument, 'text-gray-500' => ! ($uploadRequiredForRecommend && ! $crosscheckDocument)])>
                    Belum ada borang ulasan JKEW dimuat naik.@if ($uploadRequiredForRecommend) Wajib sebelum hantar keputusan Disyorkan.@endif
                </p>
            @endif

            @error('crosscheck')
                <p class="text-sm text-danger">{{ $message }}</p>
            @enderror

            @if ($canUpload && $uploadAction)
                <form method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data" class="space-y-3 border-t border-gray-100 pt-4">
                    @csrf
                    <x-field label="Muat Naik Borang Ulasan JKEW" name="file" :required="$uploadRequiredForRecommend && ! $crosscheckDocument" hint="{{ $uploadRequiredForRecommend ? 'Wajib sebelum hantar keputusan Disyorkan. ' : '' }}PDF atau Word (.doc/.docx) selepas JKEW mengisi ulasan. Gantikan fail sedia ada jika dimuat naik semula.">
                        <input type="file" name="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required class="inp file:mr-3 file:rounded-lg file:border-0 file:bg-royal-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-royal-700">
                    </x-field>
                    @error('file')
                        <p class="text-xs text-danger">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="btn-primary text-sm">Muat Naik Borang</button>
                </form>
            @endif
        </div>
    </x-page-card>
@endif
