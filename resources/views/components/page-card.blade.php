@props(['title' => null, 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    @if ($title)
        <div class="card-header">
            <div class="flex items-center gap-2">
                @if ($icon)
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-royal-50 text-royal-600">
                        <x-icon :name="$icon" class="h-4 w-4" />
                    </span>
                @endif
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">{{ $title }}</h2>
                    @if ($description)
                        <p class="mt-1.5 text-xs text-gray-500">{{ $description }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
