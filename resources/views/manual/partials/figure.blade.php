{{-- @var array<string, array{url: string, caption: string}> $figures --}}
@if (! empty($figures[$file] ?? null))
    @php($shot = $figures[$file])
    <figure class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-gray-50/80">
        <a href="{{ $shot['url'] }}" target="_blank" rel="noopener" class="block p-2 focus:outline-none focus:ring-2 focus:ring-royal-400">
            <img
                src="{{ $shot['url'] }}"
                alt="{{ $shot['caption'] }}"
                loading="lazy"
                decoding="async"
                class="mx-auto max-h-52 w-full max-w-md object-contain object-center"
            >
        </a>
        <figcaption class="border-t border-gray-100 px-3 py-2 text-center text-xs italic text-gray-500">
            {{ $shot['caption'] }}
        </figcaption>
    </figure>
@endif
