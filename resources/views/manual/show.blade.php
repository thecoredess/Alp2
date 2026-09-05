@extends('layouts.app')
@section('title', 'Manual Pengguna')
@section('heading', 'Manual Pengguna / Kit Tatacara')
@section('subheading', 'URS M11 — capaian tatacara mengikut peranan')

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2 no-print">
        <button type="button" onclick="window.print()" class="btn-white">Cetak ringkasan</button>
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
        Dokumen ini merumuskan aliran URS v1.2. Kandungan penuh boleh disahkan/dikemas kini oleh pemilik proses.
        Audiens semasa: <strong>{{ $audience === 'alp' ? 'ALP / Persatuan' : ($audience === 'jp' ? 'Pengguna JP / dalaman' : 'Umum') }}</strong>.
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Untuk ALP / Persatuan</h3>
            <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-700">
                <li>Log masuk → semak <strong>Bajet Saya</strong> &amp; kuota tempoh.</li>
                <li>Cipta permohonan → lengkapkan TBL-10 (penerima, ROS, akaun, alamat KL).</li>
                <li>Muat naik dokumen wajib TBL-9 (ROS, EFT, bank, kertas kerja).</li>
                <li>Hantar → pantau status &amp; notifikasi.</li>
                <li>Selepas lulus &amp; program selesai → muat naik <strong>report card</strong> dalam 1 bulan (BR-018).</li>
                <li>Cetak <strong>Borang Penyaluran</strong> / surat kelulusan bila perlu (BR-020).</li>
            </ol>
        </div>

        <div class="card p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Untuk JP / PEPU / Kewangan</h3>
            <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-700">
                <li><strong>Pegawai JP</strong>: senarai semak UR-M04-001 → syorkan / kembalikan.</li>
                <li><strong>Peraku</strong> (≤ RM3,000) / <strong>PEPU</strong> (> RM3,000): lulus atau tolak.</li>
                <li><strong>Kerani Kewangan / JKEW</strong>: baucar, tarikh hantar JKEW, semakan silang, status bayaran.</li>
                <li>Pantau dashboard KPI <strong>14 hari</strong> hingga JKEW.</li>
                <li>Semak senarai <strong>Laporan Aktiviti</strong> untuk report card tertunggak.</li>
            </ol>
        </div>
    </div>

    <div class="mt-6 card p-6">
        <h3 class="mb-2 text-sm font-semibold text-gray-900">Rujukan pantas peraturan wang</h3>
        <ul class="list-disc space-y-1 pl-5 text-sm text-gray-700">
            <li>BR-001: maks RM30,000 / ALP / tahun (boleh diprorata BR-007 mengikut lantikan)</li>
            <li>BR-002 / BR-003: 3 tempoh × kuota; baki tempoh luput</li>
            <li>BR-005: maks RM3,000 setiap permohonan</li>
            <li>BR-009 / BR-023: satu persatuan (ROS) sekali setahun</li>
        </ul>
        <p class="mt-4 text-xs text-gray-500">
            Fail panduan projek tambahan: <code class="font-mono">docs/PANDUAN_LOGIN.md</code>,
            <code class="font-mono">docs/PANDUAN_ANALISIS_URS_v1.2.md</code>.
        </p>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            nav, aside, header { display: none !important; }
        }
    </style>
@endsection
