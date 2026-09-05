@extends('layouts.app')
@section('title', 'Ketetapan Sistem')
@section('heading', 'Ketetapan Sistem')
@section('subheading', 'Konfigurasi operasi Sistem ALP DBKL')

@section('content')
    <div class="mb-4">
        <a href="{{ route('settings.index') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Kembali ke Ketetapan</a>
    </div>

    <div class="mx-auto max-w-3xl grid gap-3 sm:grid-cols-2">
        @can('settings.manage')
            <a href="{{ route('settings.edit') }}" class="card group flex items-start gap-3 p-5 transition hover:border-royal-300 hover:shadow-sm">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                    <x-icon name="scale" class="h-5 w-5" />
                </span>
                <span>
                    <span class="block text-sm font-semibold text-gray-900">Polisi URS</span>
                    <span class="mt-1 block text-xs text-gray-500">Had BR-001 / BR-005, kuota tempoh, templat surat &amp; borang.</span>
                </span>
            </a>
        @endcan

        @can('users.view')
            <a href="{{ route('users.index') }}" class="card group flex items-start gap-3 p-5 transition hover:border-royal-300 hover:shadow-sm">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                    <x-icon name="users-group" class="h-5 w-5" />
                </span>
                <span>
                    <span class="block text-sm font-semibold text-gray-900">Pengguna</span>
                    <span class="mt-1 block text-xs text-gray-500">Cipta, sunting, nyahaktif dan reset kata laluan.</span>
                </span>
            </a>
        @endcan

        @can('financial_years.view')
            <a href="{{ route('financial-years.index') }}" class="card group flex items-start gap-3 p-5 transition hover:border-royal-300 hover:shadow-sm">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                    <x-icon name="calendar" class="h-5 w-5" />
                </span>
                <span>
                    <span class="block text-sm font-semibold text-gray-900">Tahun Kewangan</span>
                    <span class="mt-1 block text-xs text-gray-500">Urus tahun kewangan aktif untuk peruntukan &amp; permohonan.</span>
                </span>
            </a>
        @endcan

        @can('approval_matrix.view')
            <a href="{{ route('approval-matrix.index') }}" class="card group flex items-start gap-3 p-5 transition hover:border-royal-300 hover:shadow-sm">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-royal-50 text-royal-700">
                    <x-icon name="clipboard" class="h-5 w-5" />
                </span>
                <span>
                    <span class="block text-sm font-semibold text-gray-900">Matriks Kelulusan</span>
                    <span class="mt-1 block text-xs text-gray-500">Tetapkan aras Peraku / PEPU mengikut jumlah.</span>
                </span>
            </a>
        @endcan
    </div>
@endsection
