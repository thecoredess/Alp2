{{-- Modal pratonton dokumen — tengah skrin, tinggi hampir penuh (Alpine: open, doc, close()). --}}
<style>
    .doc-viewer-shell {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: grid;
        place-items: center;
        padding: clamp(0.75rem, 2vw, 1rem);
    }

    .doc-viewer-panel {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        width: min(80vw, 75rem);
        height: calc(100dvh - 2rem);
        max-width: calc(100vw - 1.5rem);
        max-height: calc(100dvh - 1.5rem);
        border-radius: 0.75rem;
        background: #fff;
        border: 1px solid rgb(0 0 0 / 0.1);
        box-shadow: 0 24px 60px -16px rgb(30 42 90 / 0.4);
        pointer-events: auto;
    }

    .doc-viewer-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        background: #f5f5f5;
    }

    .doc-viewer-body iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
        background: #fff;
    }

    @media (max-width: 639px) {
        .doc-viewer-panel {
            width: min(94vw, 75rem);
            height: calc(100dvh - 1.25rem);
            max-height: calc(100dvh - 1.25rem);
        }
    }
</style>

<template x-teleport="body">
    <template x-if="open">
        <div
            class="doc-viewer-shell"
            role="dialog"
            aria-modal="true"
            aria-labelledby="document-viewer-title"
        >
            <button
                type="button"
                class="absolute inset-0 bg-navy-900/35"
                style="pointer-events:auto;"
                @click="close()"
                aria-label="Tutup pratonton"
            ></button>

            <div class="doc-viewer-panel" @click.stop>
                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
                    <div class="min-w-0">
                        <h3 id="document-viewer-title" class="text-lg font-semibold text-gray-900">Dokumen</h3>
                        <p class="truncate text-xs text-gray-500" x-show="doc && doc.filename" x-text="doc ? doc.filename : ''"></p>
                    </div>
                    <button
                        type="button"
                        @click="close()"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                        aria-label="Tutup"
                    >
                        <x-icon name="x-circle" class="h-5 w-5" />
                    </button>
                </div>

                <div class="doc-viewer-body">
                    <template x-if="doc && doc.kind === 'pdf'">
                        <iframe :src="doc.url" title="Pratonton dokumen"></iframe>
                    </template>

                    <template x-if="doc && doc.kind === 'image'">
                        <div class="flex h-full w-full items-center justify-center overflow-auto p-4">
                            <img :src="doc.url" :alt="doc.filename" class="max-h-full max-w-full object-contain shadow-md">
                        </div>
                    </template>

                    <template x-if="doc && doc.kind === 'other'">
                        <div class="flex h-full w-full items-center justify-center px-6 text-center text-sm text-gray-600">
                            Fail ini tidak boleh dipratonton. Sila muat turun.
                        </div>
                    </template>
                </div>

                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-200 px-5 py-4">
                    <button type="button" @click="close()" class="btn-white inline-flex items-center gap-2">
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Tutup
                    </button>
                <a
                    :href="doc ? (doc.downloadUrl || doc.url) : '#'"
                    download
                    class="btn-primary inline-flex items-center gap-2"
                >
                        <x-icon name="download" class="h-4 w-4" />
                        Muat Turun
                    </a>
                </div>
            </div>
        </div>
    </template>
</template>
