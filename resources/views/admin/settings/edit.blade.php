@extends('layouts.app')
@section('title', 'Tetapan Polisi Sumbangan')
@section('heading', 'Tetapan Polisi Sumbangan')
@section('subheading', 'Had sumbangan — wajib diaktifkan untuk operasi rasmi')

@section('content')
    <div class="page-shell">
        <div class="mb-4">
            <a href="{{ route('settings.hub') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Kembali ke Tetapan Sistem</a>
        </div>

        <div class="mb-4 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
            Polisi ini menguatkuasakan had peruntukan tahunan, had setiap permohonan, kuota tiga tempoh,
            dan peraturan baki tempoh luput. Lalai: <strong>ON</strong>.
        </div>

        <x-page-card title="Polisi Sumbangan" icon="scale" class="max-w-3xl">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <label class="flex items-start gap-3">
            <input type="checkbox" name="urs_policy_enabled" value="1" class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                   @checked(old('urs_policy_enabled', $enabled))>
            <span>
                <span class="block text-sm font-medium text-gray-900">Aktifkan polisi sumbangan</span>
                <span class="block text-xs text-amber-700">Jangan matikan dalam operasi rasmi — hanya untuk ujian luar skop.</span>
            </span>
        </label>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Had peruntukan tahunan ALP</label>
            <input type="number" step="0.01" min="0.01" name="urs_max_annual_allocation" class="inp"
                   value="{{ old('urs_max_annual_allocation', $maxAnnual) }}" required>
            @error('urs_max_annual_allocation')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Had setiap permohonan</label>
            <input type="number" step="0.01" min="0.01" name="urs_max_per_application" class="inp"
                   value="{{ old('urs_max_per_application', $maxPerApp) }}" required>
            @error('urs_max_per_application')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Kuota setiap tempoh 4-bulan</label>
            <input type="number" step="0.01" min="0.01" name="urs_period_quota" class="inp"
                   value="{{ old('urs_period_quota', $periodQuota) }}" required>
            <p class="mt-1 text-xs text-gray-500">Lalai RM10,000 × 3 tempoh (Jan–Apr, Mei–Ogos, Sep–Dis). Baki tempoh lepas tidak dibawa ke hadapan.</p>
            @error('urs_period_quota')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Ambang tertunggak dashboard (hari)</label>
            <input type="number" min="1" max="365" name="urs_overdue_days" class="inp"
                   value="{{ old('urs_overdue_days', $overdueDays) }}" required>
            <p class="mt-1 text-xs text-gray-500">Lalai 14 hari (KPI hingga hantar dokumen ke JKEW).</p>
            @error('urs_overdue_days')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div class="border-t border-gray-100 pt-5">
            <h3 class="mb-1 text-sm font-semibold text-gray-900">Templat dokumen</h3>
            <p class="mb-4 text-xs text-gray-500">Teks kepala/badan/kaki untuk surat kelulusan &amp; borang penyaluran. Tiada HTML — teks biasa.</p>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Surat — kepala</label>
                    <input type="text" name="template_letter_header" class="inp"
                           value="{{ old('template_letter_header', $templates[\App\Support\UrsDocumentTemplates::KEY_LETTER_HEADER]) }}" required>
                    @error('template_letter_header')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Surat — badan keputusan</label>
                    <textarea name="template_letter_body" rows="4" class="inp" required>{{ old('template_letter_body', $templates[\App\Support\UrsDocumentTemplates::KEY_LETTER_BODY]) }}</textarea>
                    @error('template_letter_body')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Surat — kaki</label>
                    <textarea name="template_letter_footer" rows="2" class="inp" required>{{ old('template_letter_footer', $templates[\App\Support\UrsDocumentTemplates::KEY_LETTER_FOOTER]) }}</textarea>
                    @error('template_letter_footer')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Borang — kepala</label>
                    <input type="text" name="template_borang_header" class="inp"
                           value="{{ old('template_borang_header', $templates[\App\Support\UrsDocumentTemplates::KEY_BORANG_HEADER]) }}" required>
                    @error('template_borang_header')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Borang — kaki</label>
                    <textarea name="template_borang_footer" rows="2" class="inp" required>{{ old('template_borang_footer', $templates[\App\Support\UrsDocumentTemplates::KEY_BORANG_FOOTER]) }}</textarea>
                    @error('template_borang_footer')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="flex gap-2 border-t border-gray-100 pt-5">
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
        </form>
        </x-page-card>
    </div>
@endsection
