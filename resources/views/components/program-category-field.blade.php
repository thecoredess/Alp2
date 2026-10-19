@props([
    'selected' => '',
    'required' => true,
])

@php
    $entries = \App\Enums\ProgramCategory::guideEntries();
    $selectedValue = old('program_category', $selected);
@endphp

<div
    {{ $attributes->merge(['class' => '']) }}
    x-data="{ showGuide: false }"
>
    <div class="flex flex-wrap items-center gap-2">
        <label for="program_category" class="block text-sm font-medium text-gray-700">
            e) Jenis/Kategori Program
            @if ($required)<span class="text-danger">*</span>@endif
        </label>
        <button
            type="button"
            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-royal-100 text-royal-700 transition hover:bg-royal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-royal-500"
            :aria-expanded="showGuide"
            aria-controls="program-category-guide"
            @click="showGuide = !showGuide"
            title="Penjelasan kategori program"
        >
            <x-icon name="information-circle" class="h-4 w-4" />
            <span class="sr-only">Tunjuk penjelasan kategori program</span>
        </button>
    </div>

    <div
        id="program-category-guide"
        x-show="showGuide"
        x-cloak
        x-transition
        class="mt-2 rounded-xl border border-royal-200 bg-royal-50/80 px-4 py-3 text-sm text-navy-800"
    >
        <p class="mb-2 font-semibold text-navy-900">Pilih kategori yang paling hampir dengan program anda:</p>
        <ul class="space-y-2.5">
            @foreach ($entries as $entry)
                <li>
                    <span class="font-medium text-navy-900">{{ $entry['label'] }}</span>
                    <span class="text-gray-700"> — {{ $entry['description'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mt-2">
        <select id="program_category" name="program_category" @if($required) required @endif class="inp">
            <option value="">— Pilih kategori —</option>
            @foreach (\App\Enums\ProgramCategory::options() as $value => $label)
                <option value="{{ $value }}" @selected($selectedValue === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    @error('program_category')
        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
