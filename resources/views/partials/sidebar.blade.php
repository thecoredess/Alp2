@php
    $link = fn (bool $active) => $active
        ? 'flex items-center gap-3 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white'
        : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-navy-100 hover:bg-white/5 hover:text-white transition';
@endphp

<div class="flex h-full flex-col">
    <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
        <span class="grid h-9 w-9 place-items-center rounded-lg bg-white/10 text-sm font-bold text-white ring-1 ring-white/20">DBKL</span>
        <div class="leading-tight">
            <p class="text-sm font-semibold text-white">Sistem ALP</p>
            <p class="text-[11px] text-navy-200">Sumbangan · URS v1.2</p>
        </div>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="{{ $link(request()->routeIs('dashboard')) }}">
                <x-icon name="dashboard" class="h-5 w-5 shrink-0" />
                Dashboard
            </a>

            @can('reports.view')
                <a href="{{ route('reports.index') }}" class="{{ $link(request()->routeIs('reports.*')) }}">
                    <x-icon name="document" class="h-5 w-5 shrink-0" />
                    Laporan
                </a>
            @endcan

            @can('alps.view')
                <a href="{{ route('alps.index') }}" class="{{ $link(request()->routeIs('alps.*')) }}">
                    <x-icon name="users-group" class="h-5 w-5 shrink-0" />
                    Ahli Lembaga
                </a>
            @endcan

            @if(auth()->user()->alp_id)
                <a href="{{ route('budget.mine') }}" class="{{ $link(request()->routeIs('budget.mine')) }}">
                    <x-icon name="wallet" class="h-5 w-5 shrink-0" />
                    Bajet Saya
                </a>
            @endif

            @canany(['allocations.view', 'budget.view_all'])
                <a href="{{ route('allocations.index') }}" class="{{ $link(request()->routeIs('allocations.index') || request()->routeIs('allocations.show')) }}">
                    <x-icon name="wallet" class="h-5 w-5 shrink-0" />
                    Peruntukan (Ringkasan)
                </a>
            @endcanany

            @if(auth()->user()->alp_id)
                <a href="{{ route('applications.index') }}" class="{{ $link(request()->routeIs('applications.index') || request()->routeIs('applications.wizard.*') || request()->routeIs('applications.create')) }}">
                    <x-icon name="document" class="h-5 w-5 shrink-0" />
                    Permohonan Saya
                </a>
            @endif

            @can('applications.view_all')
                <a href="{{ route('applications.all') }}" class="{{ $link(request()->routeIs('applications.all')) }}">
                    <x-icon name="document" class="h-5 w-5 shrink-0" />
                    Semua Permohonan
                </a>
            @endcan
        </div>

        @canany(['applications.review.secretariat', 'applications.approve'])
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Semakan &amp; Kelulusan</p>
                <div class="space-y-1">
                    @can('applications.review.secretariat')
                        <a href="{{ route('reviews.secretariat') }}" class="{{ $link(request()->routeIs('reviews.secretariat')) }}">
                            <x-icon name="clipboard" class="h-5 w-5 shrink-0" /> Semakan Pegawai JP
                        </a>
                    @endcan
                    @can('applications.approve')
                        <a href="{{ route('approvals.queue') }}" class="{{ $link(request()->routeIs('approvals.*')) }}">
                            <x-icon name="check" class="h-5 w-5 shrink-0" /> Kelulusan
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        @can('payments.view')
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Pembayaran</p>
                <div class="space-y-1">
                    <a href="{{ route('payments.index') }}" class="{{ $link(request()->routeIs('payments.*')) }}">
                        <x-icon name="wallet" class="h-5 w-5 shrink-0" /> Pembayaran / Baucar
                    </a>
                </div>
            </div>
        @endcan

        @canany(['applications.view_all', 'applications.review.secretariat'])
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Program</p>
                <div class="space-y-1">
                    <a href="{{ route('report-cards.index') }}" class="{{ $link(request()->routeIs('report-cards.*')) }}">
                        <x-icon name="clipboard" class="h-5 w-5 shrink-0" /> Laporan Aktiviti
                    </a>
                    @can('applications.view_all')
                        <a href="{{ route('recipients.index') }}" class="{{ $link(request()->routeIs('recipients.*')) }}">
                            <x-icon name="users-group" class="h-5 w-5 shrink-0" /> Penerima / Persatuan
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        <div>
            <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-navy-300">Ketetapan</p>
            <div class="space-y-1">
                <a href="{{ route('settings.index') }}" class="{{ $link(request()->routeIs('settings.index')) }}">
                    <x-icon name="user-cog" class="h-5 w-5 shrink-0" />
                    Semua Ketetapan
                </a>
                <a href="{{ route('settings.user') }}" class="{{ $link(request()->routeIs('settings.user') || request()->routeIs('profile.*') || request()->routeIs('password.change*')) }}">
                    <x-icon name="user-cog" class="h-5 w-5 shrink-0" />
                    Ketetapan Pengguna
                </a>
                @canany(['users.view', 'financial_years.view', 'settings.manage', 'approval_matrix.view'])
                    <a href="{{ route('settings.system') }}" class="{{ $link(request()->routeIs('settings.system') || request()->routeIs('settings.edit') || request()->routeIs('settings.update') || request()->routeIs('users.*') || request()->routeIs('financial-years.*') || request()->routeIs('approval-matrix.*')) }}">
                        <x-icon name="scale" class="h-5 w-5 shrink-0" />
                        Ketetapan Sistem
                    </a>
                @endcanany
            </div>
        </div>

        <div class="space-y-1">
            <a href="{{ route('manual.show') }}" class="{{ $link(request()->routeIs('manual.*')) }}">
                <x-icon name="document" class="h-5 w-5 shrink-0" />
                Manual Pengguna
            </a>
            <a href="{{ route('notifications.index') }}" class="{{ $link(request()->routeIs('notifications.*')) }}">
                <x-icon name="document" class="h-5 w-5 shrink-0" />
                <span class="flex-1">Notifikasi</span>
                @php $unread = auth()->user()->unreadNotifications->count(); @endphp
                @if($unread > 0)
                    <span class="rounded-full bg-royal-500 px-2 py-0.5 text-[10px] font-semibold text-white">{{ $unread }}</span>
                @endif
            </a>
        </div>
    </nav>

    <div class="border-t border-white/10 px-5 py-3">
        <p class="text-[11px] text-navy-300">URS v1.2 &middot; Sistem Aktif</p>
    </div>
</div>
