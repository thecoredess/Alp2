@extends('layouts.app')
@section('title', 'Tetapan E-mel')
@section('heading', 'Tetapan E-mel SMTP')
@section('subheading', 'Super Admin — konfigurasi penghantaran e-mel notifikasi')

@section('content')
    <div class="page-shell max-w-3xl">
        <div class="mb-4">
            <a href="{{ route('settings.hub') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Kembali ke Tetapan Sistem</a>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="mb-4 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
            E-mel dihantar apabila status permohonan berubah atau notifikasi tindakan diperlukan.
            Pastikan <strong>Aktifkan penghantaran e-mel</strong> ditanda dan uji SMTP sebelum operasi.
        </div>

        <x-page-card title="Konfigurasi SMTP" icon="bell">
            <form method="POST" action="{{ route('settings.mail.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="mail_enabled" value="1" class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                           @checked(old('mail_enabled', $settings['enabled']))>
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Aktifkan penghantaran e-mel (override .env)</span>
                        <span class="block text-xs text-gray-500">Jika dimatikan, notifikasi hanya in-app (database).</span>
                    </span>
                </label>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field label="Mailer" name="mailer" :required="true">
                        <select name="mailer" class="inp">
                            @foreach (['smtp' => 'SMTP', 'log' => 'Log (dev)', 'sendmail' => 'Sendmail'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('mailer', $settings['mailer']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    <x-field label="Port" name="port" :required="true">
                        <input type="number" name="port" class="inp" value="{{ old('port', $settings['port']) }}" required>
                    </x-field>
                    <x-field label="Host SMTP" name="host" :required="true" class="sm:col-span-2">
                        <input type="text" name="host" class="inp" value="{{ old('host', $settings['host']) }}" required>
                    </x-field>
                    <x-field label="Encryption" name="encryption">
                        <select name="encryption" class="inp">
                            @foreach (['tls' => 'TLS (STARTTLS, port 587)', 'ssl' => 'SSL (port 465)', 'none' => 'Tiada'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('encryption', $settings['encryption'] ?: 'none') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    <div class="sm:col-span-2">
                        <label class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                            <input type="checkbox" name="verify_peer" value="1" class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                                   @checked(old('verify_peer', $settings['verify_peer'] ?? true))>
                            <span>
                                <span class="block text-sm font-medium text-gray-900">Sahkan sijil SSL server SMTP</span>
                                <span class="block text-xs text-gray-600">Nyahaktifkan jika ralat <em>certificate verify failed</em> pada server dalaman / ujian (XAMPP). Jangan matikan untuk SMTP produksi.</span>
                            </span>
                        </label>
                    </div>
                    <x-field label="Username" name="username">
                        <input type="text" name="username" class="inp" value="{{ old('username', $settings['username']) }}" autocomplete="off">
                    </x-field>
                    <x-field label="Password" name="password" class="sm:col-span-2" hint="{{ $settings['has_password'] ? 'Biarkan kosong untuk kekalkan kata laluan sedia ada.' : 'Kosong jika SMTP tanpa auth.' }}">
                        <input type="password" name="password" class="inp" autocomplete="new-password">
                    </x-field>
                    <x-field label="From Address" name="from_address" :required="true">
                        <input type="email" name="from_address" class="inp" value="{{ old('from_address', $settings['from_address']) }}" required>
                    </x-field>
                    <x-field label="From Name" name="from_name" :required="true">
                        <input type="text" name="from_name" class="inp" value="{{ old('from_name', $settings['from_name']) }}" required>
                    </x-field>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-5">
                    <button type="submit" class="btn-primary">Simpan Tetapan</button>
                </div>
            </form>

            <form method="POST" action="{{ route('settings.mail.test') }}" class="mt-4 space-y-4 border-t border-gray-100 pt-4">
                @csrf
                <x-field label="E-mel penerima ujian" name="test_email" :required="true" hint="Guna alamat domain DBKL (cth. @dbkl.gov.my). Ujian guna tetapan SMTP di atas walaupun penghantaran belum diaktifkan.">
                    <input type="email" name="test_email" class="inp" value="{{ old('test_email', auth()->user()->email) }}" required placeholder="contoh@dbkl.test">
                </x-field>
                <div class="flex justify-end">
                    <button type="submit" class="btn-white text-sm">Hantar e-mel ujian</button>
                </div>
            </form>
        </x-page-card>
    </div>
@endsection
