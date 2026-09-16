@extends('layouts.app')
@section('title', 'Templat Notifikasi E-mel')
@section('heading', 'Templat Notifikasi E-mel')
@section('subheading', 'Kandungan e-mel mengikut peranan & peristiwa aliran kerja')

@section('content')
    <div class="page-shell">
        <div class="mb-4">
            <a href="{{ route('settings.hub') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Kembali ke Tetapan Sistem</a>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <div class="mb-4 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
            <p class="font-semibold">Aliran e-mel tindakan (contoh)</p>
            <ol class="mt-2 list-decimal space-y-1 pl-5 text-xs text-royal-800">
                <li><strong>ALP hantar</strong> → ALP (<em>Pengesahan hantar permohonan</em>) + Admin JP (<em>Permohonan dihantar</em>)</li>
                <li><strong>Admin JP semak &amp; hantar</strong> → Pegawai JP (<em>Menunggu pengesyoran Pegawai JP</em>)</li>
                <li><strong>Pegawai JP syor</strong> → Peraku (<em>Menunggu perakuan</em>)</li>
                <li><strong>Peraku peraku</strong> → PEPU (<em>Menunggu kelulusan PEPU</em>)</li>
                <li><strong>Diluluskan</strong> → Kewangan JP (<em>Sedia proses bayaran</em>)</li>
            </ol>
            <p class="mt-2 text-xs text-royal-700">
                Templat <strong>tindakan diperlukan</strong> berada di tab peranan pegawai (Admin JP, Pegawai JP, Peraku, dll.).
                Tab ALP pula untuk makluman status &amp; tindakan ALP (pembetulan, laporan aktiviti).
            </p>
        </div>

        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-600">
            Placeholder: <code class="rounded bg-white px-1">{user_name}</code>,
            <code class="rounded bg-white px-1">{application_number}</code>,
            <code class="rounded bg-white px-1">{project_title}</code>,
            <code class="rounded bg-white px-1">{recipient_name}</code>,
            <code class="rounded bg-white px-1">{amount}</code>,
            <code class="rounded bg-white px-1">{message}</code>,
            <code class="rounded bg-white px-1">{action_url}</code>,
            <code class="rounded bg-white px-1">{action_label}</code>
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($roles as $roleKey => $roleLabel)
                <a href="{{ route('settings.notification-templates.edit', ['peranan' => $roleKey]) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition',
                       'bg-navy-700 text-white ring-navy-700' => $selectedRole === $roleKey,
                       'bg-white text-gray-700 ring-gray-200 hover:ring-royal-300' => $selectedRole !== $roleKey,
                   ])>
                    {{ $roleLabel }}
                </a>
            @endforeach
        </div>

        <x-page-card :title="'Peranan: '.($roles[$selectedRole] ?? $selectedRole)" icon="bell">
            <form method="POST" action="{{ route('settings.notification-templates.update') }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="role" value="{{ $selectedRole }}">

                <div class="space-y-3 rounded-lg border border-gray-100 bg-gray-50/80 p-4">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="notification_mail_globally_enabled" value="1"
                               class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                               @checked(old('notification_mail_globally_enabled', $mailGloballyEnabled))>
                        <span class="text-sm text-gray-800">Aktifkan e-mel notifikasi (seluruh sistem)</span>
                    </label>
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="role_mail_enabled" value="1"
                               class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                               @checked(old('role_mail_enabled', $roleMailEnabled))>
                        <span class="text-sm text-gray-800">Hantar e-mel untuk peranan ini</span>
                    </label>
                </div>

                @forelse ($roleEvents as $event)
                    @php $tpl = $templates[$event] ?? ['subject' => '', 'body' => '', 'mail_enabled' => true]; @endphp
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ $events[$event] ?? $event }}
                                @if (\App\Support\NotificationTemplates::isActionEvent($event))
                                    <span class="ml-2 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-800">Tindakan diperlukan</span>
                                @endif
                            </h3>
                            <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                <input type="checkbox" name="templates[{{ $event }}][mail_enabled]" value="1"
                                       class="rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                                       @checked(old('templates.'.$event.'.mail_enabled', $tpl['mail_enabled']))>
                                E-mel aktif
                            </label>
                        </div>
                        <div class="space-y-3">
                            <x-field label="Subjek e-mel" :name="'templates.'.$event.'.subject'" :required="true">
                                <input type="text" name="templates[{{ $event }}][subject]" class="inp"
                                       value="{{ old('templates.'.$event.'.subject', $tpl['subject']) }}" required>
                            </x-field>
                            <x-field label="Kandungan e-mel" :name="'templates.'.$event.'.body'" :required="true">
                                <textarea name="templates[{{ $event }}][body]" rows="3" class="inp" required>{{ old('templates.'.$event.'.body', $tpl['body']) }}</textarea>
                            </x-field>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tiada templat peristiwa untuk peranan ini.</p>
                @endforelse

                <div class="flex justify-end border-t border-gray-100 pt-5">
                    <button type="submit" class="btn-primary">Simpan Templat</button>
                </div>
            </form>
        </x-page-card>
    </div>
@endsection
