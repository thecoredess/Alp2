@if ($application->associationFilledAwaitingAlpReview() && auth()->user()?->can('update', $application))
    <div class="mb-6 rounded-xl border-2 border-emerald-400 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm ring-1 ring-emerald-100" role="status">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-emerald-950">Persatuan telah hantar borang — semakan ALP diperlukan</h3>
                <p class="mt-1 text-sm text-emerald-900/90">
                    Maklumat di bawah diisi oleh persatuan/NGO pada
                    <strong>{{ $application->association_invite_filled_at->format('d/m/Y H:i') }}</strong>.
                    Sila semak borang dan lampiran yang dimuat naik persatuan, betulkan jika perlu, tambah lampiran jika masih kurang, kemudian <strong>hantar kepada Admin JP</strong> di Langkah 3.
                    Persatuan tidak boleh menghantar terus ke JP.
                </p>
            </div>
            <a href="{{ route('applications.wizard.maklumat', $application) }}" class="btn-navy shrink-0 whitespace-nowrap">
                Semak borang (Langkah 1)
            </a>
        </div>
    </div>
@endif
