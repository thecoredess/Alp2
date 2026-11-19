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
@endsection
