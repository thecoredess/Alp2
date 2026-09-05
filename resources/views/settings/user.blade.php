@extends('layouts.app')
@section('title', 'Ketetapan Pengguna')
@section('heading', 'Ketetapan Pengguna')
@section('subheading', 'Profil dan keselamatan akaun anda')

@section('content')
    <div class="mb-4">
        <a href="{{ route('settings.index') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Kembali ke Ketetapan</a>
    </div>

    <div class="mx-auto max-w-2xl space-y-4">
        <div class="card p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-3">
                    <x-user-avatar :user="$user" size="md" />
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                        <p class="mt-0.5 text-sm text-gray-500">{{ $user->email }}</p>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $user->roles->first()?->name ? \App\Enums\RoleName::from($user->roles->first()->name)->label() : '—' }}
                            @if ($user->alp)
                                · {{ $user->alp->ref_code }}
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn-primary shrink-0">Edit Profil</a>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <a href="{{ route('profile.edit') }}" class="card flex items-center gap-3 p-4 hover:border-royal-300">
                <x-icon name="user-cog" class="h-5 w-5 text-navy-600" />
                <span>
                    <span class="block text-sm font-medium text-gray-900">Profil Saya</span>
                    <span class="block text-xs text-gray-500">Nama, e-mel, telefon, alamat</span>
                </span>
            </a>
            <a href="{{ route('password.change') }}" class="card flex items-center gap-3 p-4 hover:border-royal-300">
                <x-icon name="check" class="h-5 w-5 text-navy-600" />
                <span>
                    <span class="block text-sm font-medium text-gray-900">Tukar Kata Laluan</span>
                    <span class="block text-xs text-gray-500">Keselamatan akaun</span>
                </span>
            </a>
        </div>
    </div>
@endsection
