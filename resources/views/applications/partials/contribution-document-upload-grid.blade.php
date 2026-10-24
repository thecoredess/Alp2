@php
    $requiredTypes = $requirements->map(fn ($r) => $r->document_type)->values();
    $docsByType = $application->documents
        ->groupBy(fn ($d) => $d->document_type->value)
        ->map(fn ($group) => $group->sortByDesc('id')->first());
    $doneCount = $requiredTypes->filter(fn ($t) => $docsByType->has($t->value))->count();
    $totalCount = $requiredTypes->count();
    $jpIncomplete = $jpIncomplete ?? [];
@endphp

<div class="rounded-lg border border-navy-100 bg-navy-50 px-4 py-3 text-sm text-navy-800">
    Tekan <strong>Pilih Fail</strong> pada setiap item. Format: PDF atau gambar (maksimum 10 MB).
    <strong>Kertas Kerja</strong> diisi dalam borang — tidak perlu muat naik fail kertas kerja melainkan JP minta salinan.
    <x-association-guide-links class="mt-2" />
</div>

<div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
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
                            <form method="POST" action="{{ $documentStoreUrl }}" enctype="multipart/form-data" class="inline">
                                @csrf
                                <input type="hidden" name="document_type" value="{{ $type->value }}">
                                <label class="btn-white cursor-pointer !px-3 !py-2 text-xs">
                                    Tukar Fail
                                    <input type="file" name="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                           onchange="if(this.files.length) this.form.requestSubmit()">
                                </label>
                            </form>
                            @if ($documentDestroyUrl)
                                <form method="POST" action="{{ $documentDestroyUrl($doc) }}"
                                      @if ($confirmDestroy ?? true)
                                          data-swal-confirm="Buang fail ini?"
                                          data-swal-title="Buang Lampiran"
                                          data-swal-icon="warning"
                                          data-swal-confirm-text="Ya, buang"
                                      @endif
                                >
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-white !px-3 !py-2 text-xs text-danger"
                                            @unless ($confirmDestroy ?? true) onclick="return confirm('Buang fail ini?')" @endunless>Buang</button>
                                </form>
                            @endif
                        </div>
                    @else
                        <form method="POST" action="{{ $documentStoreUrl }}" enctype="multipart/form-data" class="mt-3">
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

@if ($showProgress ?? false)
    @php
        $allDone = $doneCount === $totalCount && $totalCount > 0;
        $progress = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;
    @endphp
    <p class="mt-4 text-sm text-gray-600">
        Kemajuan: <strong>{{ $doneCount }}/{{ $totalCount }}</strong> lampiran
        @if ($allDone)
            <span class="text-green-700">— lengkap</span>
        @endif
    </p>
    <div class="mt-2 h-2 max-w-md overflow-hidden rounded-full bg-gray-100">
        <div class="h-full rounded-full bg-green-500 transition-all" style="width: {{ $progress }}%"></div>
    </div>
@endif
