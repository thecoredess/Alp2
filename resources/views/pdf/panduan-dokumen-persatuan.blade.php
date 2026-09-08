<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Panduan Dokumen Persatuan — ALP DBKL</title>
    <style>
        @page { margin: 22mm 18mm 20mm 18mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5pt;
            color: #1e293b;
            line-height: 1.45;
        }
        .cover {
            border: 2px solid #1e2a5a;
            border-radius: 8px;
            padding: 28px 24px;
            margin-bottom: 24px;
            background: #f4f6fb;
        }
        .cover-badge {
            display: inline-block;
            background: #1e2a5a;
            color: #fff;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 4px;
            margin-bottom: 14px;
        }
        h1 {
            font-size: 18pt;
            color: #1e2a5a;
            margin: 0 0 8px;
            line-height: 1.25;
        }
        h2 {
            font-size: 12pt;
            color: #1e2a5a;
            margin: 22px 0 8px;
            border-bottom: 1px solid #d5dcef;
            padding-bottom: 4px;
        }
        h3 {
            font-size: 10.5pt;
            color: #263d7a;
            margin: 14px 0 6px;
        }
        p { margin: 0 0 8px; }
        .muted { color: #64748b; font-size: 9.5pt; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 16px;
            font-size: 9.5pt;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }
        th {
            background: #eef1f8;
            color: #1e2a5a;
            text-align: left;
            font-weight: bold;
        }
        .item-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .item-no {
            display: inline-block;
            width: 22px;
            height: 22px;
            line-height: 22px;
            text-align: center;
            background: #1e2a5a;
            color: #fff;
            border-radius: 50%;
            font-size: 9pt;
            font-weight: bold;
            margin-right: 8px;
        }
        .checklist {
            margin: 0;
            padding-left: 18px;
        }
        .checklist li { margin-bottom: 5px; }
        .footer-note {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            font-size: 9pt;
            color: #64748b;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="cover-badge">Sistem ALP DBKL · URS v1.2</div>
        <h1>Panduan Penyediaan Dokumen Persatuan / Pertubuhan</h1>
        <p class="muted">Untuk permohonan sumbangan melalui Ahli Lembaga Penasihat (ALP)<br>
        Dewan Bandaraya Kuala Lumpur · Tahun {{ $year }}</p>
    </div>

    <h2>1. Senarai Semak Dokumen Wajib</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Bil.</th>
                <th style="width: 34%;">Dokumen</th>
                <th>Keperluan Ringkas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $item['no'] }}</td>
                    <td><strong>{{ $item['label'] }}</strong></td>
                    <td>{{ $item['hint'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="muted">Format fail diterima: PDF, JPG, PNG, DOC/DOCX (maksimum 10 MB setiap fail).</p>

    <div class="page-break"></div>

    <h2>2. Penjelasan Terperinci Setiap Dokumen</h2>

    @foreach ($items as $item)
        <div class="item-box">
            <h3><span class="item-no">{{ $item['no'] }}</span>{{ $item['label'] }}</h3>
            <p>{{ $item['notes'] }}</p>
            @if ($item['type'] === \App\Enums\DocumentType::BORANG_EFT)
                <p><strong>Nota:</strong> Borang EFT rasmi DBKL 2026 dilampirkan di <strong>laman terakhir</strong> dokumen PDF ini.
                Sila cetak, lengkapkan, tandatangan dan cop persatuan sebelum dimuat naik semula ke sistem.</p>
            @endif
            @if ($item['type'] === \App\Enums\DocumentType::KERTAS_KERJA)
                <p><strong>Isi minimum kertas kerja:</strong></p>
                <ul class="checklist">
                    <li>Latar belakang dan masalah / keperluan komuniti</li>
                    <li>Objektif dan sasaran penerima manfaat</li>
                    <li>Jadual tarikh aktiviti dan lokasi (Wilayah Persekutuan KL)</li>
                    <li>Anggaran perbelanjaan selari dengan jumlah permohonan</li>
                </ul>
            @endif
        </div>
    @endforeach

    <h2>3. Laporan Program / Report Card (Selepas Program)</h2>
    <p>
        Selepas program dilaksanakan, persatuan hendaklah mengemukakan <strong>laporan program</strong> kepada ALP
        dalam tempoh <strong>satu bulan</strong> (BR-018). Format rasmi dilampirkan dalam PDF ini —
        <strong>Format Laporan Program ALP {{ $year }}</strong>.
    </p>
    <ul class="checklist">
        <li>Lengkapkan 12 bahagian: latar belakang, objektif, sasaran, peserta, pelaksanaan, kewangan, gambar, dll.</li>
        <li>Muat naik laporan siap (PDF) melalui tab <strong>Laporan Aktiviti</strong> dalam sistem selepas permohonan diluluskan.</li>
    </ul>

    <h2>4. Perkara Penting</h2>
    <ul class="checklist">
        <li>Pastikan <strong>nama persatuan, nombor ROS dan maklumat bank</strong> konsisten merentas semua dokumen.</li>
        <li>Permohonan maksimum <strong>RM3,000</strong> setiap kali (BR-005).</li>
        <li>Satu persatuan (nombor ROS) hanya <strong>satu permohonan setahun</strong> (BR-009).</li>
        <li>Dokumen tidak lengkap atau kabur akan diminta pembetulan semula.</li>
        <li>ALP bertanggungjawab memastikan semua lampiran dimuat naik sebelum hantar ke JP.</li>
    </ul>

    <h2>5. Hubungi Kami</h2>
    <p>{{ $contactOffice ?? \App\Services\Documents\AssociationDocumentGuideService::CONTACT_OFFICE }}</p>

    <div class="footer-note">
        Dokumen dijana oleh Sistem ALP DBKL. Format Laporan Program ALP {{ $year }} dan Borang EFT rasmi DBKL 2026 dilampirkan selepas halaman ini.
        Versi panduan: {{ now()->format('d/m/Y') }}.
    </div>
</body>
</html>
