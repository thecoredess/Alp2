@props([
    'application' => null,
])

@php
    $fields = \App\Support\KertasKerjaGuide::formFields();
@endphp

<div {{ $attributes->merge(['class' => 'space-y-4 rounded-xl border border-navy-200 bg-navy-50/40 p-4 sm:p-5']) }}>
    <div>
        <h3 class="text-sm font-semibold text-navy-900">g) Kertas Kerja — Maklumat Program</h3>
        <p class="mt-1 text-sm text-navy-800/90">{{ \App\Support\KertasKerjaGuide::intro() }}</p>
    </div>

    <div class="flex flex-col gap-5">
        @foreach ($fields as $field)
            @php
                $key = $field['key'];
                $oldValue = old($key, $application?->{$key});
            @endphp
            <x-field :label="$field['label']" :name="$key" :required="true" :hint="$field['hint']">
                <textarea
                    id="{{ $key }}"
                    name="{{ $key }}"
                    rows="{{ $field['rows'] }}"
                    required
                    class="inp"
                    placeholder="{{ $field['placeholder'] }}"
                >{{ $oldValue }}</textarea>
            </x-field>
        @endforeach
    </div>
</div>
