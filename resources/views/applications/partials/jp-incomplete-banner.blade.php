@php
    /** @var array<string, string> $jpIncomplete */
    $jpIncomplete = $jpIncomplete ?? [];
@endphp

@if ($jpIncomplete !== [])
    <div class="mb-6 rounded-xl border-2 border-red-300 bg-red-50 p-4 shadow-sm">
        <p class="text-sm font-semibold text-red-900">Item ditanda tidak lengkap oleh Admin JP</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-800">
            @foreach ($jpIncomplete as $label)
                <li>{{ $label }}</li>
            @endforeach
        </ul>
        <p class="mt-2 text-xs text-red-700">Card berkaitan dipaparkan merah. Sila betulkan sebelum hantar semula.</p>
    </div>
@endif
