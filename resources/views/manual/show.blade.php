@extends('layouts.app')
@section('title', 'Manual Pengguna')
@section('heading', 'Manual Pengguna / Kit Tatacara')
@section('subheading', 'URS M11 — tatacara mengikut peranan anda')

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2 no-print">
        <button type="button" onclick="window.print()" class="btn-white">Cetak ringkasan</button>
        @if ($audience === 'alp')
            @can('applications.create')
                <a href="{{ route('association-guide.download') }}?v=3" class="btn-primary">Panduan Dokumen Persatuan (PDF)</a>
            @endcan
        @endif
        @if ($hasOfficialPdf)
            <a href="{{ route('manual.download') }}" class="btn-primary">Muat turun PDF rasmi</a>
        @endif
    </div>

    @if ($hasOfficialPdf)
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900 no-print">
            Manual PDF rasmi tersedia.
            <a href="{{ route('manual.download') }}" class="font-medium underline">Muat turun</a>
        </div>
    @else
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 no-print">
            Manual PDF rasmi belum dimuat naik. Ringkasan dalam sistem di bawah masih boleh digunakan / dicetak.
        </div>
    @endif

    @if ($canManageManual)
        <div class="mb-6 card space-y-3 p-5 no-print">
            <h3 class="text-sm font-semibold text-gray-900">Pentadbir — muat naik Manual PDF (M11)</h3>
            <form method="POST" action="{{ route('manual.upload') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Fail PDF (maks 10 MB)</label>
                    <input type="file" name="manual_pdf" accept="application/pdf" class="inp" required>
                    @error('manual_pdf')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-primary">Muat naik</button>
            </form>
            @if ($hasOfficialPdf)
                <form method="POST" action="{{ route('manual.destroy') }}" onsubmit="return confirm('Buang manual PDF rasmi?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-medium text-danger hover:underline">Buang PDF rasmi</button>
                </form>
            @endif
        </div>
    @endif

    <div class="mb-4 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
        Dokumen ini merumuskan aliran sistem untuk peranan anda.
        Kandungan penuh boleh disahkan/dikemas kini oleh pemilik proses.
        Peranan semasa: <strong>{{ $audienceLabel }}</strong>.
    </div>

    @if ($audience === 'alp')
        <div class="card p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Tatacara ALP / Persatuan</h3>
            <p class="text-sm text-gray-600">
                Kongsi <a href="{{ route('association-guide.download') }}?v=3" class="font-medium text-royal-600 underline hover:text-royal-700">Panduan Dokumen Persatuan (PDF)</a>
                kepada persatuan penerima — termasuk senarai semak 5 dokumen wajib, <strong>Format Laporan Program ALP</strong> (report card) dan <strong>Borang EFT rasmi DBKL 2026</strong>.
            </p>
            <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-700">
                <li>Log masuk → semak <strong>Bajet Saya</strong> &amp; kuota penggal.</li>
                <li>Cipta permohonan → lengkapkan borang penyaluran (penerima, ROS, akaun, alamat KL).</li>
                <li>Muat naik dokumen wajib senarai semak (ROS, EFT, bank, kertas kerja).</li>
                <li>Hantar → pantau status permohonan.</li>
                <li>Selepas baucar disedia → lengkapkan <strong>Format Laporan Program ALP</strong> dan muat naik dalam 1 bulan.</li>
                <li>Cetak <strong>Borang Penyaluran</strong> / surat kelulusan bila perlu.</li>
            </ol>
        </div>
    @elseif ($audience === 'jp')
        <div class="card p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900">Tatacara Pegawai Dalaman</h3>

            @can('applications.review.secretariat')
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ auth()->user()->canMakeFullJpReviewDecision() ? 'Admin JP' : 'Pegawai JP' }}
                    </p>
                    <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-gray-700">
                        @if (auth()->user()->canMakeFullJpReviewDecision())
                            <li>Semak permohonan di <strong>Semakan Admin JP</strong>.</li>
                            <li>Lengkapkan senarai semak UR-M04-001 → buat keputusan (Disyorkan / Tidak Disyorkan / Kembalikan).</li>
                        @else
                            <li>Semak permohonan di <strong>Semakan Pegawai JP</strong>.</li>
                            <li>Isikan <strong>ulasan</strong> dan <strong>syor kepada Pengarah JP</strong> (perakuan), atau kembalikan untuk pembetulan.</li>
                        @endif
                    </ol>
                </div>
            @endcan

            @can('applications.approve')
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Peraku / PEPU</p>
                    <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-gray-700">
                        <li><strong>Peraku</strong> (≤ RM3,000) / <strong>PEPU</strong> (&gt; RM3,000): lulus atau tolak permohonan.</li>
                        <li>Pastikan baki peruntukan ALP mencukupi sebelum kelulusan akhir.</li>
                    </ol>
                </div>
            @endcan

            @can('payments.view')
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Kerani Kewangan / JKEW</p>
                    <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-gray-700">
                        <li>Kemas kini baucar, tarikh hantar JKEW, semakan silang, dan status bayaran.</li>
                        <li>Pantau dashboard KPI <strong>14 hari</strong> hingga JKEW.</li>
                    </ol>
                </div>
            @endcan

            @if (auth()->user()->can('applications.view_all') || auth()->user()->can('applications.review.secretariat'))
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Laporan Aktiviti</p>
                    <p class="mt-2 text-sm text-gray-700">Semak senarai <strong>Laporan Aktiviti</strong> untuk report card tertunggak.</p>
                </div>
            @endif

            @if (! auth()->user()->can('applications.review.secretariat')
                && ! auth()->user()->can('applications.approve')
                && ! auth()->user()->can('payments.view')
                && ! auth()->user()->can('applications.view_all'))
                <p class="text-sm text-gray-600">Tiada tatacara khusus untuk peranan anda. Sila rujuk pentadbir sistem.</p>
            @endif
        </div>
    @else
        <div class="card p-6">
            <p class="text-sm text-gray-600">Manual khusus peranan tidak tersedia. Sila hubungi pentadbir sistem untuk bantuan.</p>
        </div>
    @endif

    <style>
        @media print {
            .no-print { display: none !important; }
            nav, aside, header { display: none !important; }
        }
    </style>
@endsection
