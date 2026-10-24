@php
    $fields = \App\Support\KertasKerjaGuide::formFields();
@endphp

<div class="space-y-4">
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">g) Kertas Kerja — Maklumat Program</p>
    @foreach ($fields as $field)
        @php $value = $application->{$field['key']} ?? null; @endphp
        <div class="rounded-xl border border-gray-100 p-4">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $field['label'] }}</dt>
            <dd class="mt-1.5 whitespace-pre-line text-sm text-gray-900">{{ filled($value) ? $value : '—' }}</dd>
        </div>
    @endforeach
</div>
