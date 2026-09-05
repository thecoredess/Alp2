@extends('layouts.guest')
@section('title', 'Set Semula Kata Laluan')

@section('content')
    <h2 class="text-2xl font-semibold tracking-tight text-navy-800">Set Semula Kata Laluan</h2>
    <p class="mt-1.5 text-sm text-gray-500">Masukkan kata laluan baharu anda.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-7 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-field label="E-mel" name="email" :required="true">
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}"
                   required autocomplete="username" class="inp">
        </x-field>

        <x-field label="Kata Laluan Baharu" name="password" :required="true" hint="Minimum 8 aksara, huruf dan nombor.">
            <x-password-input id="password" name="password" autocomplete="new-password" :required="true" />
        </x-field>

        <x-field label="Sahkan Kata Laluan" name="password_confirmation" :required="true">
            <x-password-input id="password_confirmation" name="password_confirmation" autocomplete="new-password" :required="true" />
        </x-field>

        <button type="submit" class="btn-navy w-full py-2.5">Simpan Kata Laluan</button>
    </form>
@endsection
