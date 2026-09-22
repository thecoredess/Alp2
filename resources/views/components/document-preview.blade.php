@props([
    'application',
    'documents',
    'layout' => 'grid',
    'empty' => 'Tiada lampiran.',
    'highlightIncomplete' => false,
])

@php
    use App\Support\DocumentPreview;

    $previewDocs = DocumentPreview::itemsFor($application, $documents);
    $docCollection = collect($documents);
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
            if (this.open && ! this.pushed) {
                history.pushState({ docPreview: true }, '');
                this.pushed = true;
            }
        },
        close(fromPopstate = false) {
            this.open = false;
            this.doc = null;
            if (this.pushed) {
                this.pushed = false;
                if (! fromPopstate) history.back();
            }
        }
    }"
    @keydown.escape.window="if (open) close()"
    @popstate.window="if (open) close(true)"
    {{ $attributes }}
>
    @if ($docCollection->isEmpty())
        <p class="text-sm text-gray-500">{{ $empty }}</p>
    @elseif ($layout === 'cards')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($docCollection as $doc)
                <div @class([
                    'group card flex items-center gap-4 p-5 transition',
                    'border-2 border-red-400 bg-red-50 ring-1 ring-red-200' => $highlightIncomplete,
                    'hover:border-royal-200 hover:shadow-md' => ! $highlightIncomplete,
                ])>
                    <span @class([
                        'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition',
                        'bg-red-100 text-red-600' => $highlightIncomplete,
                        'bg-royal-50 text-royal-600 group-hover:bg-royal-100' => ! $highlightIncomplete,
                    ])>
                        <x-icon name="document" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900">{{ $doc->document_type->simpleLabel() }}</p>
                        <p class="truncate text-sm text-gray-500" title="{{ $doc->original_filename }}">{{ $doc->original_filename }}</p>
                    </div>
                    <button
                        type="button"
                        @click="show({{ $doc->id }})"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-royal-600 transition hover:bg-royal-50 hover:text-royal-700"
                        title="Lihat Lampiran"
                        aria-label="Lihat lampiran {{ $doc->document_type->simpleLabel() }}"
                    >
                        <x-icon name="eye" class="h-4 w-4" />
                    </button>
                </div>
            @endforeach
        </div>
    @elseif ($layout === 'rows')
        <div class="space-y-3">
            @foreach ($docCollection as $doc)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900">{{ $doc->document_type->label() }}</p>
                        <p class="truncate text-xs text-gray-500" title="{{ $doc->original_filename }}">
                            {{ $doc->original_filename }}
                            @if ($doc->created_at)
                                · {{ $doc->created_at->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="show({{ $doc->id }})"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-royal-600 transition hover:bg-royal-50 hover:text-royal-700"
                        title="Lihat Lampiran"
                        aria-label="Lihat lampiran {{ $doc->document_type->label() }}"
                    >
                        <x-icon name="eye" class="h-4 w-4" />
                    </button>
                </div>
            @endforeach
        </div>
    @elseif ($layout === 'actions')
        @foreach ($docCollection as $doc)
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="show({{ $doc->id }})" class="btn-white inline-flex items-center gap-2 text-xs">
                    <x-icon name="eye" class="h-4 w-4" />
                    Lihat
                </button>
                <a href="{{ route('applications.documents.download', [$application, $doc]) }}" class="btn-white text-xs">
                    Muat Turun
                </a>
            </div>
        @endforeach
    @elseif ($layout === 'button')
        @foreach ($docCollection as $doc)
            <button type="button" @click="show({{ $doc->id }})" class="btn-white inline-flex items-center gap-2 text-xs !px-3 !py-1.5">
                <x-icon name="eye" class="h-4 w-4" />
                Lihat
            </button>
        @endforeach
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($docCollection as $doc)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/80 px-4 py-3 text-sm">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900">{{ $doc->document_type->simpleLabel() }}</p>
                        <p class="truncate text-xs text-gray-500" title="{{ $doc->original_filename }}">{{ $doc->original_filename }}</p>
                    </div>
                    <button
                        type="button"
                        @click="show({{ $doc->id }})"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-royal-600 transition hover:bg-royal-50 hover:text-royal-700"
                        title="Lihat Lampiran"
                        aria-label="Lihat lampiran {{ $doc->document_type->simpleLabel() }}"
                    >
                        <x-icon name="eye" class="h-4 w-4" />
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <x-document-viewer-modal />
</div>
