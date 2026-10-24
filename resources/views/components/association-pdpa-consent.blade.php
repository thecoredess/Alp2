@props([
    'formId' => 'association-borang-form',
])

@php
    use App\Support\DbklPersonalDataProtectionNotice as PdpaNotice;
@endphp

<div
    {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-4']) }}
    x-data="{
        openPdpaNotice() {
            const d = this.$refs.pdpaDialog;
            if (d && ! d.open) {
                d.showModal();
            }
        },
        closePdpaNotice() {
            const d = this.$refs.pdpaDialog;
            if (d && d.open) {
                d.close();
            }
        },
    }"
>
    <label class="flex cursor-pointer items-start gap-3">
        <input
            type="checkbox"
            name="pdpa_consent"
            value="1"
            form="{{ $formId }}"
            required
            @checked(old('pdpa_consent'))
            class="mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
        >
        <span class="text-sm text-gray-800">
            Saya telah membaca dan bersetuju dengan
            <button type="button" class="font-medium text-royal-700 underline hover:text-royal-900" @click="openPdpaNotice()">
                Notis Perlindungan Data Peribadi
            </button>
            Dewan Bandaraya Kuala Lumpur selaras Akta Perlindungan Data Peribadi 2010 (Akta 709).
            <span class="text-danger">*</span>
        </span>
    </label>
    @error('pdpa_consent')
        <p class="mt-2 text-sm text-danger">{{ $message }}</p>
    @enderror

    <template x-teleport="body">
        <dialog
            x-ref="pdpaDialog"
            class="pdpa-notice-dialog"
            aria-labelledby="pdpa-notice-title"
            @cancel.prevent="closePdpaNotice()"
        >
            <div class="pdpa-notice-dialog__inner">
                <div class="pdpa-notice-dialog__head flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h2 id="pdpa-notice-title" class="text-lg font-semibold text-gray-900">Notis Perlindungan Data Peribadi</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Dewan Bandaraya Kuala Lumpur · Terakhir dikemaskini: {{ PdpaNotice::LAST_UPDATED }}</p>
                    </div>
                    <button type="button" class="btn-white !px-2 !py-1 text-sm" @click="closePdpaNotice()">Tutup</button>
                </div>

                <div class="pdpa-notice-dialog__body px-5 py-4 text-sm leading-relaxed text-gray-700">
                    @foreach (PdpaNotice::sections() as $section)
                        <section class="mb-5 last:mb-0">
                            <h3 class="mb-2 font-semibold text-gray-900">{{ $section['title'] }}</h3>
                            @foreach ($section['body'] ?? [] as $paragraph)
                                <p class="mb-2">{{ $paragraph }}</p>
                            @endforeach
                            @if (! empty($section['items']))
                                <ul class="list-disc space-y-1 pl-5">
                                    @foreach ($section['items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach
                </div>

                <div class="pdpa-notice-dialog__foot border-t border-gray-100 bg-gray-50 px-5 py-3 text-xs text-gray-600">
                    Versi penuh di portal rasmi DBKL:
                    <a href="{{ PdpaNotice::OFFICIAL_URL }}" target="_blank" rel="noopener noreferrer" class="font-medium text-royal-700 underline hover:text-royal-900">
                        {{ PdpaNotice::OFFICIAL_URL }}
                    </a>
                </div>
            </div>
        </dialog>
    </template>
</div>
