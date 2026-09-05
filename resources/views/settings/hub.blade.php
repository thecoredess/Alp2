@extends('layouts.app')
@section('title', 'Ketetapan')
@section('heading', 'Ketetapan')
@section('subheading', 'Ketetapan pengguna dan ketetapan sistem')

@section('content')
    <div class="mx-auto max-w-3xl space-y-8">
        {{-- Ketetapan Pengguna --}}
        <section>
            <div class="mb-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-navy-700">Ketetapan Pengguna</h2>
                <p class="mt-0.5 text-sm text-gray-500">Maklumat akaun dan keselamatan log masuk anda.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('profile.edit') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-navy-50 text-navy-700">
                        <x-icon name="user-cog" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Profil Saya</span>
                        <span class="mt-0.5 block text-xs text-gray-500">Nama, e-mel log masuk, telefon &amp; alamat hubungan.</span>
                    </span>
                </a>
                <a href="{{ route('password.change') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-navy-50 text-navy-700">
                        <x-icon name="check" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Tukar Kata Laluan</span>
                        <span class="mt-0.5 block text-xs text-gray-500">Kemaskini kata laluan akaun anda.</span>
                    </span>
                </a>
            </div>
            <div class="mt-3">
                <a href="{{ route('settings.user') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">
                    Buka halaman Ketetapan Pengguna →
                </a>
            </div>
        </section>

        {{-- Ketetapan Sistem --}}
        <section>
            <div class="mb-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-navy-700">Ketetapan Sistem</h2>
                <p class="mt-0.5 text-sm text-gray-500">Konfigurasi operasi, polisi URS dan pentadbiran.</p>
            </div>

            @if ($canSystem)
                <div class="grid gap-3 sm:grid-cols-2">
                    @can('settings.manage')
                        <a href="{{ route('settings.edit') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                                <x-icon name="scale" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Polisi URS</span>
                                <span class="mt-0.5 block text-xs text-gray-500">Had sumbangan &amp; templat surat/borang.</span>
                            </span>
                        </a>
                    @endcan
                    @can('users.view')
                        <a href="{{ route('users.index') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                                <x-icon name="users-group" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Pengguna</span>
                                <span class="mt-0.5 block text-xs text-gray-500">Urus akaun, peranan dan status pengguna.</span>
                            </span>
                        </a>
                    @endcan
                    @can('financial_years.view')
                        <a href="{{ route('financial-years.index') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                                <x-icon name="calendar" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Tahun Kewangan</span>
                                <span class="mt-0.5 block text-xs text-gray-500">Buka / tutup tahun kewangan aktif.</span>
                            </span>
                        </a>
                    @endcan
                    @can('approval_matrix.view')
                        <a href="{{ route('approval-matrix.index') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                                <x-icon name="clipboard" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Matriks Kelulusan</span>
                                <span class="mt-0.5 block text-xs text-gray-500">Ambang Peraku / PEPU mengikut jumlah.</span>
                            </span>
                        </a>
                    @endcan
                </div>
                <div class="mt-3">
                    <a href="{{ route('settings.system') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">
                        Buka halaman Ketetapan Sistem →
                    </a>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">
                    Ketetapan sistem hanya untuk pentadbir. Hubungi Admin JP jika anda memerlukan akses.
                </div>
            @endif
        </section>
    </div>
@endsection
