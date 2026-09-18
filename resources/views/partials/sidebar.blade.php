@php
    $link = fn (bool $active) => $active
        ? 'flex items-center gap-3 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white'
        : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-navy-100 hover:bg-white/5 hover:text-white transition';

    $user = auth()->user();
    $hasAlp = (bool) $user->alp_id;
    $canAllApplications = $user->can('applications.view_all');
    $canReviewJp = $user->can('applications.review.secretariat');
    $canApprove = $user->can('applications.approve');
    $canPayments = $user->can('payments.view');
    $canReportCards = $canAllApplications || $canReviewJp;
    $showProcess = $hasAlp || $canAllApplications || $canReviewJp || $canApprove || $canPayments || $canReportCards;

    $canReports = $user->can('reports.view');
    $canAllocations = $user->can('allocations.view') || $user->can('budget.view_all');
    $showMonitoring = $hasAlp || $canReports || $canAllocations;

    $canRecipients = $canAllApplications;
    $canAlps = $user->can('alps.view');
    $hideReferenceMenu = $user->hasRole(\App\Enums\RoleName::PEGAWAI_KEWANGAN->value);
    $showReference = ! $hideReferenceMenu && ($canRecipients || $canAlps);

    $canSystem = $user->can('users.view')
        || $user->can('financial_years.view')
        || $user->can('settings.manage')
        || $user->can('approval_matrix.view')
        || $user->hasRole(\App\Enums\RoleName::SUPER_ADMIN->value);
    $systemRoutesActive = request()->routeIs('settings.*')
        || request()->routeIs('users.*')
        || request()->routeIs('financial-years.*')
        || request()->routeIs('approval-matrix.*');
@endphp

<div class="flex h-full flex-col">
    <div class="flex h-16 items-center gap-2.5 border-b border-white/10 px-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white p-1 ring-1 ring-white/20">
            <x-brand-logo variant="sidebar" />
        </div>
        <p class="min-w-0 flex-1 text-[11px] font-semibold uppercase leading-snug tracking-[0.06em] text-white">
            Sistem Sumbangan ALP
        </p>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="{{ $link(request()->routeIs('dashboard')) }}">
                <x-icon name="dashboard" class="h-5 w-5 shrink-0" />
                Dashboard
            </a>
        </div>

        @if($showProcess)
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Aliran proses</p>
                <div class="space-y-1">
                    @if($hasAlp)
                        <a href="{{ route('applications.index') }}" class="{{ $link(request()->routeIs('applications.index') || request()->routeIs('applications.wizard.*') || request()->routeIs('applications.create')) }}">
                            <span class="w-5 shrink-0 text-center text-[10px] font-semibold {{ request()->routeIs('applications.index') || request()->routeIs('applications.wizard.*') || request()->routeIs('applications.create') ? 'text-white' : 'text-navy-300' }}">1</span>
                            Permohonan Saya
                        </a>
                    @endif
                    @if($canAllApplications)
                        <a href="{{ route('applications.all') }}" class="{{ $link(request()->routeIs('applications.all')) }}">
                            <span class="w-5 shrink-0 text-center text-[10px] font-semibold {{ request()->routeIs('applications.all') ? 'text-white' : 'text-navy-300' }}">1</span>
                            Semua Permohonan
                        </a>
                    @endif
                    @if($canReviewJp)
                        <a href="{{ route('reviews.secretariat') }}" class="{{ $link(request()->routeIs('reviews.secretariat')) }}">
                            <span class="w-5 shrink-0 text-center text-[10px] font-semibold {{ request()->routeIs('reviews.secretariat') ? 'text-white' : 'text-navy-300' }}">2</span>
                            {{ $user->canMakeFullJpReviewDecision() ? 'Semakan Admin JP' : 'Semakan Urusetia JP' }}
                        </a>
                    @endif
                    @if($canApprove)
                        <a href="{{ route('approvals.queue') }}" class="{{ $link(request()->routeIs('approvals.*')) }}">
                            <span class="w-5 shrink-0 text-center text-[10px] font-semibold {{ request()->routeIs('approvals.*') ? 'text-white' : 'text-navy-300' }}">3</span>
                            Kelulusan
                        </a>
                    @endif
                    @if($canPayments)
                        <a href="{{ route('payments.index') }}" class="{{ $link(request()->routeIs('payments.*')) }}">
                            <span class="w-5 shrink-0 text-center text-[10px] font-semibold {{ request()->routeIs('payments.*') ? 'text-white' : 'text-navy-300' }}">4</span>
                            Pembayaran / Baucar
                        </a>
                    @endif
                    @if($canReviewJp)
                        <a href="{{ route('report-cards.review.index') }}" class="{{ $link(request()->routeIs('report-cards.review.*')) }}">
                            <span class="w-5 shrink-0 text-center text-[10px] font-semibold {{ request()->routeIs('report-cards.review.*') ? 'text-white' : 'text-navy-300' }}">5b</span>
                            Semak Laporan
                        </a>
                    @endif
                    @if($canReportCards)
                        <a href="{{ route('report-cards.index') }}" class="{{ $link(request()->routeIs('report-cards.*')) }}">
                            <span class="w-5 shrink-0 text-center text-[10px] font-semibold {{ request()->routeIs('report-cards.*') ? 'text-white' : 'text-navy-300' }}">5</span>
                            Laporan Aktiviti
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($showMonitoring)
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Pemantauan</p>
                <div class="space-y-1">
                    @if($hasAlp)
                        <a href="{{ route('budget.mine') }}" class="{{ $link(request()->routeIs('budget.mine')) }}">
                            <x-icon name="wallet" class="h-5 w-5 shrink-0" />
                            Bajet Saya
                        </a>
                    @endif
                    @if($canAllocations)
                        <a href="{{ route('allocations.index') }}" class="{{ $link(request()->routeIs('allocations.index') || request()->routeIs('allocations.show')) }}">
                            <x-icon name="wallet" class="h-5 w-5 shrink-0" />
                            Peruntukan (Ringkasan)
                        </a>
                    @endif
                    @if($canReports)
                        <a href="{{ route('reports.index') }}" class="{{ $link(request()->routeIs('reports.*')) }}">
                            <x-icon name="document" class="h-5 w-5 shrink-0" />
                            Laporan
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($showReference)
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Rujukan</p>
                <div class="space-y-1">
                    @if($canRecipients)
                        <a href="{{ route('recipients.index') }}" class="{{ $link(request()->routeIs('recipients.*')) }}">
                            <x-icon name="users-group" class="h-5 w-5 shrink-0" />
                            Penerima / Persatuan
                        </a>
                    @endif
                    @if($canAlps)
                        <a href="{{ route('alps.index') }}" class="{{ $link(request()->routeIs('alps.*')) }}">
                            <x-icon name="users-group" class="h-5 w-5 shrink-0" />
                            Ahli Lembaga
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <div>
            <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Tetapan</p>
            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="{{ $link(request()->routeIs('profile.*') || request()->routeIs('password.change*')) }}">
                    <x-icon name="user" class="h-5 w-5 shrink-0" />
                    Profil
                </a>
                @if($canSystem)
                    <a href="{{ route('settings.hub') }}" class="{{ $link($systemRoutesActive) }}">
                        <x-icon name="user-cog" class="h-5 w-5 shrink-0" />
                        Tetapan Sistem
                    </a>
                @endif
                <a href="{{ route('manual.show') }}" class="{{ $link(request()->routeIs('manual.*')) }}">
                    <x-icon name="document" class="h-5 w-5 shrink-0" />
                    Manual Pengguna
                </a>
            </div>
        </div>
    </nav>

    <div class="border-t border-white/10 px-5 py-3">
        <p class="text-[11px] text-navy-300">Sistem Aktif</p>
    </div>
</div>
