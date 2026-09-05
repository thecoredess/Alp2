@props(['label', 'name', 'required' => false, 'hint' => null])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    <div class="mt-1">
        {{ $slot }}
    </div>
    @if($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
