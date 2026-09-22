@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', $activeYear ? 'Tahun Kewangan '.$activeYear->year.' (Aktif)' : 'Tiada tahun kewangan aktif')

@section('content')
    <div class="mb-8 rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 flex-1 items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-navy-600 to-royal-500 text-white shadow-sm">
                    <x-icon name="dashboard" class="h-6 w-6" />
                </span>
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-gray-900">Selamat datang, {{ auth()->user()->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Peranan: {{ auth()->user()->roles->first()?->name ? \App\Enums\RoleName::from(auth()->user()->roles->first()->name)->label() : '—' }}
                    </p>
                </div>
            </div>
            <x-welcome-datetime class="w-full sm:w-auto" />
        </div>
    </div>

    @if (! $activeYear)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Belum ada tahun kewangan yang ditetapkan sebagai <strong>Aktif</strong>.
            @can('financial_years.manage')
                Sila <a href="{{ route('financial-years.index') }}" class="font-medium underline">tetapkan tahun kewangan aktif</a>.
            @endcan
        </div>
    @endif

    {{-- PEPU: Tindakan Diperlukan --}}
    @if ($isPepuDashboard && $officerQueues)
        @include('dashboard.partials.pepu-overview-card')
    @else
        {{-- Kad statistik pentadbir --}}
        @if ($stats)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-stat-card label="Ahli Lembaga Aktif" icon="users-group" tone="navy">{{ $stats['alp_count'] }}</x-stat-card>
                <x-stat-card label="Pengguna Aktif" icon="user-cog" tone="royal">{{ $stats['user_count'] }}</x-stat-card>
                <x-stat-card label="Tahun Kewangan Aktif" icon="calendar" tone="teal">{{ $stats['financial_year'] ?? '—' }}</x-stat-card>
            </div>
        @endif

        {{-- Giliran pegawai — blok tindakan --}}
        @if ($officerQueues)
            <div class="mt-8">
                <h3 class="dashboard-section-title">Tindakan Diperlukan</h3>
                <div @class([
                    'grid gap-4',
                    'grid-cols-1' => count($officerQueues) === 1,
                    'grid-cols-1 sm:grid-cols-2' => count($officerQueues) === 2,
                    'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' => count($officerQueues) >= 3,
                ])>
                    @foreach ($officerQueues as $q)
                        <x-action-block
                            :label="$q['label']"
                            :description="$q['description'] ?? null"
                            :icon="$q['icon'] ?? 'inbox'"
                            :tone="$q['tone'] ?? 'royal'"
                            :count="$q['count']"
                            :href="route($q['route'])"
                        />
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    @if ($contributionKpi && ! auth()->user()->alp_id)
        @include('dashboard.partials.contribution-kpi-cards')
    @endif

    @if ($contributionAnalytics)
        @include('dashboard.partials.contribution-analytics')
    @endif

    {{-- Bajet tahunan ALP --}}
    @if (! $suppressLegacyDashboardCards && $scope === 'own' && $annualAllocation)
        <div class="mt-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="dashboard-section-title mb-0">Bajet Tahunan @if($activeYear)<span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>@endif</h3>
                <a href="{{ route('budget.mine') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Lihat butiran →</a>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat-card label="Peruntukan Tahunan Semasa" icon="wallet" tone="navy">
                    <x-money :value="$annualAllocation" />
                </x-stat-card>
                <x-stat-card label="Baki Tahunan Semasa" icon="sparkles" :tone="$annualAvailable->isNegative() ? 'red' : 'green'">
                    <x-money :value="$annualAvailable" />
                </x-stat-card>
            </div>
        </div>
    @endif

    {{-- Kuota tempoh — hanya jika polisi aktif & pengguna ALP --}}
    @if (! $suppressLegacyDashboardCards && $periodSummary)
        <div class="mt-8">
            <h3 class="dashboard-section-title">Kuota Tempoh · {{ $periodSummary['label'] }}@if($activeYear)<span class="font-normal text-gray-400"> · {{ $activeYear->year }}</span>@endif</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat-card label="Kuota tempoh" icon="banknotes" tone="navy">
                    <x-money :value="$periodSummary['quota']" />
                </x-stat-card>
                <x-stat-card label="Dalam Proses + Kelulusan" icon="arrow-path" tone="amber">
                    <x-money :value="$periodSummary['used']" />
                </x-stat-card>
                <x-stat-card
                    label="Baki tempoh"
                    icon="sparkles"
                    tone="green"
                    :hint="'Luput '.$periodSummary['ends_at']->format('d/m/Y').' · tiada bawa ke hadapan'"
                >
                    <x-money :value="$periodSummary['remaining']" />
                </x-stat-card>
            </div>
        </div>
    @endif

    {{-- Pemantauan KPI 14 hari — pegawai sahaja (bukan ALP, bukan PEPU) --}}
    @unless ($isPepuDashboard)
    @can('applications.view_all')
        @if (($kpiWatchlist ?? collect())->isNotEmpty())
            <div class="mt-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Pemantauan KPI {{ $kpiDays ?? 14 }} hari · hingga JKEW</h3>
                <div class="card overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-4 py-3">Permohonan</th>
                                <th class="px-4 py-3">ALP</th>
                                <th class="px-4 py-3">Hari</th>
                                <th class="px-4 py-3">Status KPI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($kpiWatchlist as $row)
                                @php $app = $row['application']; $kpi = $row['kpi']; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('applications.show', $app) }}" class="font-mono text-xs text-royal-700 hover:underline">{{ $app->application_number }}</a>
                                        <p class="line-clamp-1 text-xs text-gray-500">{{ $app->programLabelForReport(60) }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $app->alp?->ref_code }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $kpi['days'] ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                            'bg-green-100 text-green-800' => $kpi['within_kpi'] === true,
                                            'bg-red-100 text-red-800' => $kpi['within_kpi'] === false,
                                            'bg-gray-100 text-gray-600' => $kpi['within_kpi'] === null,
                                        ])>{{ $kpi['within_kpi'] === false ? 'Melebihi KPI' : ($kpi['within_kpi'] ? 'Dalam KPI' : '—') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endcan
    @endunless

    {{-- Permohonan tertunggak --}}
    @unless ($isPepuDashboard)
    @if ($overdueApplications->isNotEmpty())
        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">
                    Permohonan Tertunggak
                    <span class="font-normal text-gray-400">· &gt; {{ $overdueDays }} hari</span>
                </h3>
            </div>
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">No.</th>
                            <th class="px-4 py-3">Tajuk</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Dihantar</th>
                            <th class="px-4 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($overdueApplications as $app)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $app->application_number }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="line-clamp-1">{{ $app->programLabelForReport(60) }}</span>
                                    @if ($app->alp)
                                        <span class="block text-xs text-gray-400">{{ $app->alp->ref_code }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $app->status->badgeClasses() }}">
                                        {{ $app->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $app->submitted_at?->format('d/m/Y') }}
                                    <span class="block text-xs text-danger">{{ $app->submitted_at?->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-table-actions>
                                        <x-table-action href="{{ route('applications.show', $app) }}" icon="eye" label="Buka" variant="primary" />
                                    </x-table-actions>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    @endunless

    {{-- Kad permohonan --}}
    @if (! $suppressLegacyDashboardCards && $appStats && $scope === 'own')
        <div class="mt-8">
            <h3 class="dashboard-section-title">Ringkasan Permohonan</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <x-stat-card label="Draf" icon="document" tone="gray">{{ $appStats['draft'] }}</x-stat-card>
                <x-stat-card label="Dalam Proses" icon="arrow-path" tone="blue">{{ $appStats['in_process'] }}</x-stat-card>
                <x-stat-card label="Perlu Pembetulan" icon="pencil-square" tone="orange">{{ $appStats['revision'] }}</x-stat-card>
                <x-stat-card label="Diluluskan" icon="check" tone="green">{{ $appStats['approved'] }}</x-stat-card>
                <x-stat-card label="Ditolak" icon="x-circle" tone="red">{{ $appStats['rejected'] }}</x-stat-card>
            </div>
        </div>

        @include('dashboard.partials.alp-charts')
    @elseif ($appStats && $scope === 'all')
        <div class="mt-8">
            @if ($isPepuDashboard)
                @include('dashboard.partials.pepu-application-summary-card')
            @else
                <h3 class="dashboard-section-title">Ringkasan Permohonan</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    <x-stat-card label="Jumlah Permohonan" icon="clipboard" tone="navy">{{ $appStats['total'] }}</x-stat-card>
                    <x-stat-card label="Dalam Semakan" icon="search" tone="blue">{{ $appStats['under_review'] }}</x-stat-card>
                    <x-stat-card label="Menunggu Kelulusan" icon="scale" tone="amber">{{ $appStats['pending_approval'] }}</x-stat-card>
                    <x-stat-card label="Diluluskan" icon="check" tone="green">{{ $appStats['approved'] }}</x-stat-card>
                    <x-stat-card label="Ditolak" icon="x-circle" tone="red">{{ $appStats['rejected'] }}</x-stat-card>
                </div>
            @endif
        </div>
    @endif

    {{-- Modul projek diasingkan — kad projek tidak lagi dipaparkan --}}

    {{-- Ringkasan bajet — pegawai sahaja (ALP guna halaman Bajet Saya) --}}
    @if ($summary && $scope === 'all')
        <div class="mt-8">
            @if ($isPepuDashboard)
                @include('dashboard.partials.pepu-budget-card')
            @else
            <div class="mb-4 flex items-center justify-between">
                <h3 class="dashboard-section-title mb-0">Bajet Keseluruhan ALP @if($activeYear)<span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>@endif</h3>
                <a href="{{ route('allocations.index') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Lihat peruntukan →</a>
            </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-stat-card label="Peruntukan Tahunan" icon="banknotes" tone="navy">
                        <x-money :value="$summary->allocation" />
                    </x-stat-card>
                    <x-stat-card label="Diluluskan" icon="scale" tone="amber">
                        <x-money :value="$summary->committed" />
                    </x-stat-card>
                    <x-stat-card label="Baki Tersedia" icon="wallet" :tone="$summary->available()->isNegative() ? 'red' : 'green'">
                        <x-money :value="$summary->available()" />
                    </x-stat-card>
                </div>
                @if($summary->allocation->isPositive())
                    <div class="mt-4 card p-4">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1.5 font-medium"><x-icon name="chart" class="h-3.5 w-3.5" /> Penggunaan Bajet</span>
                            <span class="font-semibold text-navy-700">{{ $summary->utilisationPercent() }}%</span>
                        </div>
                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-royal-500 to-royal-400 transition-all" style="width: {{ min(100, $summary->utilisationPercent()) }}%"></div>
                        </div>
                    </div>
                @endif
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-stat-card label="Peruntukan Permohonan Dalam Proses (Belum diluluskan)" icon="clock" tone="orange">
                        <x-money :value="$pending" />
                    </x-stat-card>
                    <x-stat-card label="Baki Peruntukan Semasa" icon="sparkles" :tone="$projected->isNegative() ? 'red' : 'green'" hint="Peruntukan Diluluskan + Permohonan Dalam Proses">
                        <x-money :value="$projected" />
                    </x-stat-card>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/60 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-royal-50 text-royal-600">
                        <x-icon name="bell" class="h-4 w-4" />
                    </span>
                    <h3 class="text-sm font-semibold text-gray-900">Notifikasi Terkini</h3>
                </div>
                <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-royal-600 hover:text-royal-700">Semua →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($recentNotifications as $n)
                    <a href="{{ route('notifications.read', $n->id) }}"
                       class="block px-5 py-3 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-royal-50/50' }}">
                        <p class="text-sm font-medium text-gray-900">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                        <p class="mt-0.5 truncate text-xs text-gray-500">{{ \App\Support\NotificationMessage::display($n->data['message'] ?? null) }}</p>
                        <p class="mt-1 text-[11px] text-gray-400">{{ $n->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">Tiada notifikasi baharu.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-navy-50 text-navy-600">
                    <x-icon name="sparkles" class="h-4 w-4" />
                </span>
                <h3 class="text-sm font-semibold text-gray-900">Capaian Pantas</h3>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @can('create', \App\Models\Application::class)
                    <a href="{{ route('applications.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="h-4 w-4" /> Permohonan Baharu</a>
                @endcan
                @if (auth()->user()->can('applications.create') || auth()->user()->canCreateApplicationOnBehalf())
                    <a href="{{ route('association-guide.download') }}?v=3" class="btn-white text-sm"><x-icon name="download" class="h-4 w-4" /> Panduan Dokumen Persatuan (PDF)</a>
                @endif
                @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="btn-white text-sm"><x-icon name="chart" class="h-4 w-4" /> Laporan</a>
                @endcan
                <a href="{{ route('notifications.index') }}" class="btn-white text-sm"><x-icon name="bell" class="h-4 w-4" /> Notifikasi</a>
                @can('payments.view')
                    @unless ($isPepuDashboard)
                        <a href="{{ route('payments.index') }}" class="btn-white text-sm"><x-icon name="receipt" class="h-4 w-4" /> Pembayaran / Baucar</a>
                    @endunless
                @endcan
                @can('applications.review.secretariat')
                    <a href="{{ route('reviews.secretariat') }}" class="btn-white text-sm"><x-icon name="clipboard" class="h-4 w-4" /> Semakan JP</a>
                @endcan
                @can('applications.approve')
                    <a href="{{ route('approvals.queue') }}" class="btn-white text-sm"><x-icon name="shield-check" class="h-4 w-4" /> Kelulusan</a>
                @endcan
            </div>
        </div>
    </div>
@endsection
