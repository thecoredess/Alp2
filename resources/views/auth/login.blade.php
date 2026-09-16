@extends('layouts.guest')
@section('title', 'Log Masuk')

@section('content')
    <p class="guest-form-welcome">Selamat Datang</p>
    <h2 class="text-2xl font-semibold tracking-tight text-navy-800">Log Masuk</h2>
    <p class="mt-1.5 text-sm text-gray-500">Masukkan e-mel dan kata laluan anda.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-4" id="login-form">
        @csrf

        <x-field label="E-mel" name="email" :required="true">
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username" placeholder="nama@domain.com" class="inp">
        </x-field>

        <x-field label="Kata Laluan" name="password" :required="true">
            <x-password-input id="password" name="password" autocomplete="current-password" :required="true" placeholder="Masukkan kata laluan" />
        </x-field>

        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-royal-500 focus:ring-royal-500">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">
                Lupa kata laluan?
            </a>
        </div>

        <button type="submit" class="btn-navy w-full py-2.5">Log Masuk</button>
    </form>

    <div class="mt-6 border-t border-gray-100 pt-5 text-center text-sm leading-normal text-gray-600">
        <p class="font-semibold text-gray-800">Hubungi Kami :</p>
        <p class="mt-1.5 font-medium text-gray-800">Urus setia Lembaga Penasihat Bandaraya Kuala Lumpur</p>
        <p class="mt-1">Tingkat 4, Menara DBKL 1, Jalan Raja Laut,<br>50300, Kuala Lumpur</p>
        <p class="mt-1">No tel: 03-26179853/9852</p>
        <p class="mt-1">
            Emel:
            <a href="mailto:jptd@dbkl.gov.my" class="text-royal-700 hover:text-royal-800 hover:underline">jptd@dbkl.gov.my</a>
            /
            <a href="mailto:maklumbalasjp@dbkl.gov.my" class="text-royal-700 hover:text-royal-800 hover:underline">maklumbalasjp@dbkl.gov.my</a>
        </p>
    </div>

    @unless (app()->isProduction())
        @php
            $devAccounts = array_merge([
                ['label' => 'Super Admin', 'email' => 'superadmin@dbkl.test'],
                ['label' => 'Admin JP', 'email' => 'adminjp@dbkl.test'],
                ['label' => 'Pentadbir Sistem', 'email' => 'sysadmin@dbkl.test'],
            ], \App\Models\User::role(\App\Enums\RoleName::ALP->value)
                ->with('alp')
                ->orderBy('email')
                ->get()
                ->map(fn ($u) => [
                    'label' => 'ALP · '.($u->alp?->ref_code ?? $u->email),
                    'email' => $u->email,
                ])
                ->all(), [
                ['label' => 'Pegawai JP', 'email' => 'urussetia@dbkl.test'],
                ['label' => 'Kerani Kewangan JP', 'email' => 'kewangan@dbkl.test'],
                ['label' => 'JKEW (skop dihantar)', 'email' => 'jkew@dbkl.test'],
                ['label' => 'TP / Pengarah JP', 'email' => 'pelulus@dbkl.test'],
                ['label' => 'PEPU / Pengurusan', 'email' => 'pengurusan@dbkl.test'],
            ]);
        @endphp

        <div class="mt-8 border-t border-gray-100 pt-4">
            <label for="dev-account" class="mb-1.5 block text-xs text-gray-400">
                UAT · <span class="font-mono text-gray-500">password</span>
            </label>
            <select id="dev-account" class="inp text-sm">
                <option value="">— Pilih akaun —</option>
                @foreach ($devAccounts as $acc)
                    <option value="{{ $acc['email'] }}">{{ $acc['label'] }} · {{ $acc['email'] }}</option>
                @endforeach
            </select>
        </div>

        <script>
            document.getElementById('dev-account')?.addEventListener('change', function () {
                var email = this.value;
                if (!email) return;
                var e = document.getElementById('email');
                var p = document.getElementById('password');
                if (e) e.value = email;
                if (p) p.value = 'password';
            });
        </script>
    @endunless
@endsection
