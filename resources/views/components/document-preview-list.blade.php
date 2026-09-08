@props([
    'application',
    'documents',
])

@php
    $previewDocs = $documents->map(function ($doc) use ($application) {
        $mime = strtolower((string) ($doc->mime_type ?? ''));
        $name = strtolower((string) $doc->original_filename);
        $kind = 'other';
        if (str_starts_with($mime, 'image/') || preg_match('/\.(jpe?g|png|gif|webp|bmp)$/', $name)) {
            $kind = 'image';
        } elseif ($mime === 'application/pdf' || str_ends_with($name, '.pdf')) {
            $kind = 'pdf';
        }

        return [
            'id' => $doc->id,
            'title' => $doc->document_type->simpleLabel(),
            'filename' => $doc->original_filename,
            'url' => route('applications.documents.view', [$application, $doc]),
            'kind' => $kind,
        ];
    })->values();
@endphp

<div
    x-data="{
        open: false,
        doc: null,
        pushed: false,
        items: {{ Js::from($previewDocs) }},
        show(id) {
            this.doc = this.items.find(d => d.id === id) || null;
            this.open = !! this.doc;
            document.body.classList.toggle('overflow-hidden', this.open);

            // Butang 'Back' pelayar menutup pratonton, bukan meninggalkan halaman.
            if (this.open && ! this.pushed) {
                history.pushState({ docPreview: true }, '');
                this.pushed = true;
            }
        },
        close(fromPopstate = false) {
            this.open = false;
            this.doc = null;
            document.body.classList.remove('overflow-hidden');

            if (this.pushed) {
                this.pushed = false;
                if (! fromPopstate) history.back();
            }
        }
    }"
    @keydown.escape.window="if (open) close()"
    @popstate.window="if (open) close(true)"
>
    @if ($documents->isEmpty())
        <p class="text-sm text-gray-400">Tiada lampiran.</p>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($documents as $doc)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/80 px-4 py-3 text-sm">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900">{{ $doc->document_type->simpleLabel() }}</p>
                        <p class="truncate text-xs text-gray-500" title="{{ $doc->original_filename }}">{{ $doc->original_filename }}</p>
                    </div>
                    {{-- Pautan sebenar sebagai sandaran jika JS gagal dimuatkan. --}}
                    <a
                        href="{{ route('applications.documents.view', [$application, $doc]) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click.prevent="show({{ $doc->id }})"
                        class="btn-white shrink-0 !px-3 !py-2 text-xs"
                    >
                        Lihat
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal dipindahkan ke <body> supaya tidak terikat pada kad induk. --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[9999] bg-neutral-900"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex h-full w-full flex-col bg-white">
                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-200 px-3 py-3 sm:px-5">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            @click="close()"
                            class="btn-navy shrink-0 !px-3 !py-2 text-sm"
                        >
                            <x-icon name="arrow-left" class="h-4 w-4" />
                            <span class="hidden sm:inline">Kembali ke semakan</span>
                            <span class="sm:hidden">Kembali</span>
                        </button>

                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-semibold text-gray-900" x-text="doc ? doc.title : ''"></h3>
                            <p class="truncate text-xs text-gray-500" x-text="doc ? doc.filename : ''"></p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <span class="hidden text-xs text-gray-400 lg:inline">Tekan <kbd class="rounded border border-gray-300 bg-gray-50 px-1.5 py-0.5 font-sans text-[11px] text-gray-600">Esc</kbd> untuk tutup</span>
                        <a
                            :href="doc ? doc.url : '#'"
                            download
                            class="btn-white !px-3 !py-1.5 text-xs"
                        >Muat turun</a>
                        <button
                            type="button"
                            @click="close()"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                            aria-label="Tutup pratonton"
                            title="Tutup"
                        >
                            <x-icon name="x-circle" class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                {{-- Body modal: PDF / imej dipaparkan terus di sini --}}
                <div class="min-h-0 flex-1 bg-neutral-200">
                    <template x-if="open && doc && doc.kind === 'pdf'">
                        <object :data="doc.url" type="application/pdf" class="h-full w-full">
                            <iframe :src="doc.url" class="h-full w-full border-0 bg-white" title="Pratonton PDF"></iframe>
                        </object>
                    </template>

                    <template x-if="open && doc && doc.kind === 'image'">
                        <div class="flex h-full w-full items-center justify-center overflow-auto p-4">
                            <img :src="doc.url" :alt="doc.filename" class="max-h-full max-w-full object-contain shadow">
                        </div>
                    </template>

                    <template x-if="open && doc && doc.kind === 'other'">
                        <div class="flex h-full w-full items-center justify-center px-6 text-center text-sm text-gray-600">
                            Fail ini tidak boleh dipratonton. Sila muat turun.
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
