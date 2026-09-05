@extends('layouts.app')
@section('title', 'Tetapan Polisi URS')
@section('heading', 'Tetapan Polisi URS')
@section('subheading', 'Had sumbangan URS v1.2 — wajib diaktifkan untuk operasi rasmi')

@section('content')
    <div class="mb-4">
        <a href="{{ route('settings.system') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Ketetapan Sistem</a>
    </div>

    <div class="mb-4 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-800">
        Polisi ini menguatkuasakan peraturan wang URS v1.2:
        <strong>BR-001</strong> (maks RM30,000/tahun),
        <strong>BR-005</strong> (maks RM3,000/permohonan),
        <strong>BR-002</strong> (3 tempoh × kuota), dan
        <strong>BR-003</strong> (baki tempoh luput).
        Lalai: <strong>ON</strong>.
    </div>

    <form method="POST" action="{{ route('settings.update') }}" class="card max-w-xl space-y-5 p-6">
        @csrf
        @method('PUT')

        <label class="flex items-start gap-3">
            <input type="checkbox" name="urs_policy_enabled" value="1" class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                   @checked(old('urs_policy_enabled', $enabled))>
            <span>
                <span class="block text-sm font-medium text-gray-900">Aktifkan polisi sumbangan URS</span>
                <span class="block text-xs text-amber-700">Jangan matikan dalam operasi rasmi — hanya untuk ujian luar skop.</span>
            </span>
        </label>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Had peruntukan tahunan ALP (BR-001)</label>
            <input type="number" step="0.01" min="0.01" name="urs_max_annual_allocation" class="inp"
                   value="{{ old('urs_max_annual_allocation', $maxAnnual) }}" required>
            @error('urs_max_annual_allocation')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Had setiap permohonan (BR-005)</label>
            <input type="number" step="0.01" min="0.01" name="urs_max_per_application" class="inp"
                   value="{{ old('urs_max_per_application', $maxPerApp) }}" required>
            @error('urs_max_per_application')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Kuota setiap tempoh 4-bulan (BR-002)</label>
            <input type="number" step="0.01" min="0.01" name="urs_period_quota" class="inp"
                   value="{{ old('urs_period_quota', $periodQuota) }}" required>
            <p class="mt-1 text-xs text-gray-500">Lalai RM10,000 × 3 tempoh (Jan–Apr, Mei–Ogos, Sep–Dis). Baki tempoh lepas tidak dibawa (BR-003).</p>
            @error('urs_period_quota')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Ambang tertunggak dashboard (hari)</label>
            <input type="number" min="1" max="365" name="urs_overdue_days" class="inp"
                   value="{{ old('urs_overdue_days', $overdueDays) }}" required>
            <p class="mt-1 text-xs text-gray-500">Lalai 14 hari (KPI hingga hantar dokumen ke JKEW — UR-M02-005).</p>
            @error('urs_overdue_days')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <div class="border-t border-gray-100 pt-5">
            <h3 class="mb-1 text-sm font-semibold text-gray-900">Templat dokumen (M10 / UR-M10-002)</h3>
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

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
@endsection
