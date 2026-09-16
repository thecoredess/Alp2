@extends('layouts.app')
@section('title', 'Tetapan Sistem')
@section('heading', 'Tetapan Sistem')
@section('subheading', 'Konfigurasi operasi, polisi sumbangan dan pentadbiran')

@section('content')
    <div class="page-shell">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @can('settings.manage')
                <a href="{{ route('settings.edit') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                        <x-icon name="scale" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Polisi Sumbangan</span>
                        <span class="mt-0.5 block text-xs text-gray-500">Had sumbangan &amp; templat surat/borang.</span>
                    </span>
                </a>
                <a href="{{ route('settings.notification-templates.edit') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                        <x-icon name="bell" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Templat E-mel Notifikasi</span>
                        <span class="mt-0.5 block text-xs text-gray-500">Kandungan e-mel mengikut peranan &amp; peristiwa.</span>
                    </span>
                </a>
            @endcan
            @if (auth()->user()->hasRole(\App\Enums\RoleName::SUPER_ADMIN->value))
                <a href="{{ route('settings.audit-trail') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-navy-50 text-navy-700">
                        <x-icon name="document" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Jejak Audit</span>
                        <span class="mt-0.5 block text-xs text-gray-500">Rekod tindakan pentadbir &amp; perubahan sistem.</span>
                    </span>
                </a>
                <a href="{{ route('settings.mail.edit') }}" class="card group flex items-start gap-3 p-4 transition hover:border-royal-300 hover:shadow-sm">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-navy-50 text-navy-700">
                        <x-icon name="receipt" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900 group-hover:text-navy-800">Tetapan E-mel SMTP</span>
                        <span class="mt-0.5 block text-xs text-gray-500">Server, port &amp; penghantaran e-mel sistem.</span>
                    </span>
                </a>
            @endif
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
    </div>
@endsection
