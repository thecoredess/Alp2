@extends('layouts.guest')
@section('title', 'Lupa Kata Laluan')

@section('content')
    <h2 class="text-2xl font-semibold tracking-tight text-navy-800">Lupa Kata Laluan</h2>
    <p class="mt-1.5 text-sm text-gray-500">
        Masukkan e-mel untuk menerima pautan set semula.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-4">
        @csrf
        <x-field label="E-mel" name="email" :required="true">
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autofocus class="inp" placeholder="nama@domain.com">
        </x-field>

        <button type="submit" class="btn-navy w-full py-2.5">Hantar Pautan</button>

        <a href="{{ route('login') }}" class="block text-center text-sm font-medium text-royal-600 hover:text-royal-700">
            ← Kembali ke Log Masuk
        </a>
    </form>
@endsection
