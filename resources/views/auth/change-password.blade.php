@extends('layouts.app')
@section('title', 'Tukar Kata Laluan')
@section('heading', 'Tukar Kata Laluan')

@section('content')
    <div class="max-w-xl">
        @if(auth()->user()->must_change_password)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Demi keselamatan, anda perlu menetapkan kata laluan baharu sebelum meneruskan.
            </div>
        @endif

        <div class="card p-6">
            <form method="POST" action="{{ route('password.change.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-field label="Kata Laluan Semasa" name="current_password" :required="true">
                    <x-password-input id="current_password" name="current_password" autocomplete="current-password" :required="true" />
                </x-field>

                <x-field label="Kata Laluan Baharu" name="password" :required="true" hint="Minimum 8 aksara, mengandungi huruf dan nombor.">
                    <x-password-input id="password" name="password" autocomplete="new-password" :required="true" />
                </x-field>

                <x-field label="Sahkan Kata Laluan Baharu" name="password_confirmation" :required="true">
                    <x-password-input id="password_confirmation" name="password_confirmation" autocomplete="new-password" :required="true" />
                </x-field>

                <div class="flex justify-end gap-3 pt-2">
                    @unless(auth()->user()->must_change_password)
                        <a href="{{ route('dashboard') }}" class="btn-white">Batal</a>
                    @endunless
                    <button type="submit" class="btn-primary">Kemas Kini Kata Laluan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
