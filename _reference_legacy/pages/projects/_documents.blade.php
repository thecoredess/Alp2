{{-- Panel dokumen bukti (boleh guna semula untuk perbelanjaan / penutupan / refund).
     Param: $documents, $uploadRoute, $documentTypes (map value=>label), $canManage (bool),
            $title, $emptyText --}}
<div class="card p-5" x-data="{ open: false }">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">{{ $title ?? 'Dokumen Bukti' }}</h3>
        @if ($canManage)
            <button type="button" @click="open = !open" class="text-sm text-navy-600 hover:text-navy-800" x-text="open ? 'Tutup' : '+ Muat Naik'"></button>
        @endif
    </div>

    @if ($canManage)
        <form x-show="open" x-cloak method="POST" action="{{ $uploadRoute }}" enctype="multipart/form-data" class="mb-4 space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3">
            @csrf
            <div>
                <label class="block text-xs text-gray-500">Jenis Dokumen</label>
                <select name="document_type" required class="inp">
                    @foreach ($documentTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Fail (PDF/imej/Word/Excel, maks 10 MB)</label>
                <input type="file" name="file" required class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-50 file:px-3 file:py-1.5 file:text-navy-700" />
            </div>
            <button class="btn-navy w-full">Muat Naik</button>
        </form>
    @endif

    @forelse ($documents as $doc)
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2 last:border-0 text-sm">
            <div class="min-w-0">
                <p class="truncate text-gray-900">{{ $doc->original_filename }}</p>
                <p class="text-xs text-gray-500">
                    {{ $doc->document_type->label() }} · {{ number_format($doc->file_size / 1024, 0) }} KB
                    @isset($doc->uploader)· {{ $doc->uploader->name }}@endisset
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('project-documents.download', $doc) }}" class="text-navy-600 hover:text-navy-800">Muat Turun</a>
                @if ($canManage)
                    <form method="POST" action="{{ route('project-documents.destroy', $doc) }}" onsubmit="return confirm('Buang dokumen ini?')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700">Buang</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="py-2 text-sm text-gray-500">{{ $emptyText ?? 'Tiada dokumen lagi.' }}</p>
    @endforelse
</div>
