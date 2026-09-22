@extends('layouts.app')
@section('title', 'Profil ALP')
@section('heading', $alp->name)
@section('subheading', $alp->ref_code)

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('alps.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke senarai
        </a>
        <div class="flex items-center gap-2">
            @can('alps.update')
                <a href="{{ route('alps.edit', $alp) }}" class="btn-white">Kemaskini</a>
            @endcan
            @can('alps.deactivate')
                @if($alp->isActive())
                    <form method="POST" action="{{ route('alps.deactivate', $alp) }}"
                          onsubmit="return confirm('Nyahaktifkan ALP ini?')">
                        @csrf
                        <button class="btn-danger">Nyahaktif</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('alps.activate', $alp) }}">
                        @csrf
                        <button class="btn-white">Aktifkan Semula</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    {{-- Kad bajet (tahun kewangan aktif) --}}
    <div class="mb-2 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">
            Bajet {{ $activeYear ? $activeYear->year : '' }}
        </h3>
        @if($allocation)
            <a href="{{ route('allocations.show', $allocation) }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Lihat Peruntukkan →</a>
        @elseif($activeYear)
            <span class="text-xs text-gray-400">Peruntukan: urus melalui lejar/pentadbir (cadangan bajet maker-checker telah dinyahaktif)</span>
        @endif
    </div>
    @if($activeYear)
        <x-budget-cards :summary="$summary" />
        <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="card p-4">
                <p class="text-xs text-gray-500">Peruntukan Permohonan Dalam Proses (Belum diluluskan)</p>
                <p class="mt-1 text-lg font-semibold text-orange-600"><x-money :value="$pending" /></p>
            </div>
            <div class="card p-4">
                <p class="text-xs text-gray-500">Baki Peruntukan Semasa (Peruntukan Diluluskan + Permohonan Dalam Proses)</p>
                <p class="mt-1 text-lg font-semibold {{ $projected->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$projected" /></p>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Tiada tahun kewangan aktif.</div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Maklumat profil --}}
        <div class="card p-6 lg:col-span-2">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Maklumat Profil</h3>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-gray-500">Status</dt>
                    <dd class="mt-0.5"><x-status-badge :label="$alp->status->label()" :classes="$alp->status->badgeClasses()" /></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Portfolio / Zon</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $alp->portfolio_zone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Tarikh Mula Lantikan</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $alp->appointment_start?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Tarikh Tamat Lantikan</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $alp->appointment_end?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Telefon</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $alp->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">E-mel</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $alp->email ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Alamat</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $alp->address ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Catatan</dt>
                    <dd class="mt-0.5 text-gray-900 whitespace-pre-line">{{ $alp->remarks ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Ringkasan --}}
        <div class="card p-6">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Ringkasan</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Akaun pengguna dikaitkan</dt>
                    <dd class="font-medium text-gray-900">{{ $alp->users_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Permohonan</dt>
                    <dd class="font-medium text-gray-900">{{ $alp->applications_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Projek aktif</dt>
                    <dd class="font-medium text-gray-900">{{ $alp->active_projects_count }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
