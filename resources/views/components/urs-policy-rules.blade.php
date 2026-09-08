@props(['limits'])

@if ($limits)
    <x-page-card
        title="Peraturan Peruntukan Sumbangan ALP"
        icon="scale"
        {{ $attributes }}
    >
        <div class="flex flex-col gap-4">
            <div class="rounded-xl border border-royal-100 bg-gradient-to-br from-royal-50 to-navy-50 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-royal-600">1 · Had Tahunan</p>
                <p class="mt-1.5 text-sm text-gray-700">
                    Setiap ALP layak menerima peruntukan maksimum
                    <strong class="text-navy-700">RM {{ $limits['max_annual_policy']->format() }}</strong>
                    setahun.
                </p>
                @if (! $limits['max_annual_entitlement']->equals($limits['max_annual_policy']))
                    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        Kelayakan anda bagi tahun ini (BR-007):
                        <strong>RM {{ $limits['max_annual_entitlement']->format() }}</strong>
                        — {{ $limits['entitlement_periods'] }} tempoh × RM {{ $limits['period_quota']->format() }}
                        (kuota penuh setiap tempoh)
                        · lantikan {{ $limits['appointment_start'] ?? '—' }}.
                    </p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-100 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">2 · Had Setiap Permohonan</p>
                <p class="mt-1.5 text-sm text-gray-700">
                    Nilai maksimum kelulusan bagi setiap permohonan ialah
                    <strong class="text-navy-700">RM {{ $limits['max_per_application']->format() }}</strong>.
                </p>
            </div>

            <div class="rounded-xl border border-gray-100 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">3 · Pembahagian Tempoh</p>
                <p class="mt-1.5 text-sm text-gray-700">Peruntukan dibahagikan kepada tiga penggal setahun:</p>
                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                    @foreach ($limits['periods'] as $period)
                        <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 text-sm">
                            <p class="font-medium text-gray-900">{{ $period['label'] }}</p>
                            <p class="mt-0.5 font-semibold text-navy-700">RM {{ $limits['period_quota']->format() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">4 · Tempoh Luput</p>
                <p class="mt-1.5 text-sm text-gray-700">
                    Peruntukan yang tidak digunakan dalam tempoh ditetapkan akan <strong>luput</strong>
                    dan tidak boleh dibawa ke tempoh seterusnya.
                </p>
            </div>

            <div class="rounded-xl border border-gray-100 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">5 · Bilangan Permohonan</p>
                <p class="mt-1.5 text-sm text-gray-700">
                    Tiada had bilangan permohonan dalam satu tempoh selagi masih terdapat baki peruntukan tempoh tersebut.
                </p>
            </div>

            <div class="rounded-xl border border-gray-100 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">6 · Peruntukan Individu</p>
                <p class="mt-1.5 text-sm text-gray-700">
                    Peruntukan adalah individu setiap ALP dan <strong>tidak boleh</strong> dipindahkan, diserahkan,
                    atau dikongsi dengan ALP lain.
                </p>
            </div>

            <div class="rounded-xl border border-gray-100 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">7 · Baki Kelayakan</p>
                <p class="mt-1.5 text-sm text-gray-700">
                    Sebarang permohonan yang melebihi baki kelayakan semasa <strong>tidak akan dipertimbangkan</strong>.
                </p>
            </div>
        </div>
    </x-page-card>
@endif
