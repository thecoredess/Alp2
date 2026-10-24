@php
    use App\Services\Application\ApplicationAssociationInviteService;
    $inviteService = app(ApplicationAssociationInviteService::class);
    $inviteActive = $inviteService->isInviteActive($application);
    $inviteUrl = session('association_invite_url') ?: $inviteService->publicUrlFor($application);
@endphp

@if ($application->isDraft() && auth()->user()?->can('update', $application))
    <div id="pautan-persatuan" class="mb-6 scroll-mt-6 rounded-xl border-2 border-indigo-300 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-md ring-1 ring-indigo-100">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.121a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-indigo-950">Pautan untuk persatuan isi borang</h3>
                <p class="mt-1 text-sm text-indigo-900/90">
                    Jana pautan awam (sah <strong>{{ ApplicationAssociationInviteService::VALID_DAYS }} hari</strong>) untuk persatuan/NGO lengkapkan borang tanpa log masuk.
                    Persatuan boleh isi borang dan <strong>muat naik lampiran</strong> melalui pautan. Selepas itu ALP semak, lengkapkan jika perlu, kemudian <strong>hantar kepada Admin JP</strong> (Langkah 3).
                </p>

                @if ($inviteActive)
                    <p class="mt-3 text-xs font-medium text-indigo-800">
                        Pautan aktif sehingga {{ $application->association_invite_expires_at?->format('d/m/Y H:i') }}.
                        @if ($application->association_invite_filled_at)
                            Persatuan hantar borang pada {{ $application->association_invite_filled_at->format('d/m/Y H:i') }}.
                        @else
                            Belum diisi persatuan.
                        @endif
                    </p>
                @endif

                @if ($inviteUrl)
                    <div class="mt-4" x-data="{ copied: false }">
                        <label class="text-xs font-medium uppercase tracking-wide text-indigo-700">Salin pautan untuk persatuan</label>
                        <div class="mt-1 flex flex-col gap-2 sm:flex-row">
                            <input type="text" readonly value="{{ $inviteUrl }}" class="inp flex-1 bg-white font-mono text-xs" id="association-invite-url">
                            <button type="button" class="btn-white shrink-0"
                                    @click="navigator.clipboard.writeText(document.getElementById('association-invite-url').value); copied = true; setTimeout(() => copied = false, 2000)">
                                <span x-text="copied ? 'Disalin!' : 'Salin pautan'"></span>
                            </button>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('applications.association-invite.store', $application) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="btn-primary">
                        {{ $inviteActive ? 'Jana pautan baharu (7 hari)' : 'Jana pautan untuk persatuan' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
