@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', $activeYear ? 'Tahun Kewangan '.$activeYear->year.' (Aktif)' : 'Tiada tahun kewangan aktif')

@section('content')
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Selamat datang, {{ auth()->user()->name }}</h2>
        <p class="text-sm text-gray-500">
            Peranan: {{ auth()->user()->roles->first()?->name ? \App\Enums\RoleName::from(auth()->user()->roles->first()->name)->label() : '—' }}
        </p>
    </div>

    @if (! $activeYear)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Belum ada tahun kewangan yang ditetapkan sebagai <strong>Aktif</strong>.
            @can('financial_years.manage')
                Sila <a href="{{ route('financial-years.index') }}" class="font-medium underline">tetapkan tahun kewangan aktif</a>.
            @endcan
        </div>
    @endif

    {{-- Kad statistik pentadbir --}}
    @if ($stats)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="card p-5">
                <p class="text-sm text-gray-500">Ahli Lembaga Aktif</p>
                <p class="mt-2 text-3xl font-semibold text-navy-700">{{ $stats['alp_count'] }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-gray-500">Pengguna Aktif</p>
                <p class="mt-2 text-3xl font-semibold text-navy-700">{{ $stats['user_count'] }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-gray-500">Tahun Kewangan Aktif</p>
                <p class="mt-2 text-3xl font-semibold text-navy-700">{{ $stats['financial_year'] ?? '—' }}</p>
            </div>
        </div>
    @endif

    {{-- Giliran pegawai --}}
    @if ($officerQueues)
        <div class="mt-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Tindakan Diperlukan</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($officerQueues as $q)
                    <a href="{{ route($q['route']) }}" class="card p-5 hover:bg-gray-50">
                        <p class="text-sm text-gray-500">{{ $q['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold text-royal-600">{{ $q['count'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Kuota tempoh URS (BR-003/004) — hanya jika polisi aktif & pengguna ALP --}}
    @if ($periodSummary)
        <div class="mt-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Kuota Tempoh URS · {{ $periodSummary['label'] }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="card p-5">
                    <p class="text-sm text-gray-500">Kuota tempoh</p>
                    <p class="mt-2 text-2xl font-semibold text-navy-700"><x-money :value="$periodSummary['quota']" /></p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-gray-500">Digunakan (pending + diluluskan)</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-600"><x-money :value="$periodSummary['used']" /></p>
                </div>
                <div class="card p-5">
                    <p class="text-sm text-gray-500">Baki tempoh</p>
                    <p class="mt-2 text-2xl font-semibold text-green-600"><x-money :value="$periodSummary['remaining']" /></p>
                    <p class="mt-1 text-xs text-gray-400">Luput {{ $periodSummary['ends_at']->format('d/m/Y') }} · tiada bawa ke hadapan</p>
                </div>
            </div>
        </div>
    @endif

    {{-- KPI 14 hari hingga JKEW (UR-M02-005) --}}
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
                                    <p class="line-clamp-1 text-xs text-gray-500">{{ $app->project_title }}</p>
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

    {{-- Permohonan tertunggak --}}
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
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($overdueApplications as $app)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $app->application_number }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="line-clamp-1">{{ $app->project_title }}</span>
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
                                    <a href="{{ route('applications.show', $app) }}" class="text-xs font-medium text-royal-600 hover:text-royal-700">Buka →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Kad permohonan --}}
    @if ($appStats && $scope === 'own')
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="card p-5"><p class="text-sm text-gray-500">Draf</p><p class="mt-2 text-3xl font-semibold text-gray-600">{{ $appStats['draft'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Dalam Proses</p><p class="mt-2 text-3xl font-semibold text-blue-600">{{ $appStats['in_process'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Perlu Pembetulan</p><p class="mt-2 text-3xl font-semibold text-orange-600">{{ $appStats['revision'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Diluluskan</p><p class="mt-2 text-3xl font-semibold text-green-600">{{ $appStats['approved'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Ditolak</p><p class="mt-2 text-3xl font-semibold text-danger">{{ $appStats['rejected'] }}</p></div>
        </div>
    @elseif ($appStats && $scope === 'all')
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="card p-5"><p class="text-sm text-gray-500">Jumlah Permohonan</p><p class="mt-2 text-3xl font-semibold text-navy-700">{{ $appStats['total'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Dalam Semakan</p><p class="mt-2 text-3xl font-semibold text-blue-600">{{ $appStats['under_review'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Menunggu Kelulusan</p><p class="mt-2 text-3xl font-semibold text-amber-600">{{ $appStats['pending_approval'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Diluluskan</p><p class="mt-2 text-3xl font-semibold text-green-600">{{ $appStats['approved'] }}</p></div>
            <div class="card p-5"><p class="text-sm text-gray-500">Ditolak</p><p class="mt-2 text-3xl font-semibold text-danger">{{ $appStats['rejected'] }}</p></div>
        </div>
    @endif

    {{-- Modul projek diasingkan (URS v1.2) — kad projek tidak lagi dipaparkan --}}

    {{-- Ringkasan bajet --}}
    @if ($summary)
        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">
                    {{ $scope === 'own' ? 'Bajet Saya' : 'Bajet Keseluruhan ALP' }}
                    @if($activeYear) <span class="text-gray-400 font-normal">· {{ $activeYear->year }}</span> @endif
                </h3>
                @if($scope === 'own')
                    <a href="{{ route('budget.mine') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Lihat butiran →</a>
                @elseif($scope === 'all')
                    <a href="{{ route('allocations.index') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Lihat peruntukan →</a>
                @endif
            </div>
            <x-budget-cards :summary="$summary" />
            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="card p-4"><p class="text-xs text-gray-500">Pending Request (belum diluluskan — bukan Committed)</p><p class="mt-1 text-lg font-semibold text-orange-600"><x-money :value="$pending" /></p></div>
                <div class="card p-4"><p class="text-xs text-gray-500">Projected Available (Ledger Available − Pending)</p><p class="mt-1 text-lg font-semibold {{ $projected->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$projected" /></p></div>
            </div>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-gray-900">Notifikasi Terkini</h3>
                <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-royal-600 hover:text-royal-700">Semua →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($recentNotifications as $n)
                    <a href="{{ route('notifications.read', $n->id) }}"
                       class="block px-5 py-3 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-royal-50/50' }}">
                        <p class="text-sm font-medium text-gray-900">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                        <p class="mt-0.5 truncate text-xs text-gray-500">{{ $n->data['message'] ?? '' }}</p>
                        <p class="mt-1 text-[11px] text-gray-400">{{ $n->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">Tiada notifikasi baharu.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-900">Capaian Pantas</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="btn-white text-sm">Laporan</a>
                @endcan
                <a href="{{ route('notifications.index') }}" class="btn-white text-sm">Notifikasi</a>
                @can('payments.view')
                    <a href="{{ route('payments.index') }}" class="btn-white text-sm">Pembayaran / Baucar</a>
                @endcan
                @can('applications.review.secretariat')
                    <a href="{{ route('reviews.secretariat') }}" class="btn-white text-sm">Semakan JP</a>
                @endcan
                @can('applications.approve')
                    <a href="{{ route('approvals.queue') }}" class="btn-white text-sm">Kelulusan</a>
                @endcan
            </div>
        </div>
    </div>
@endsection
