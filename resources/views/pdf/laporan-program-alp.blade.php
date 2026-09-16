<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Format Laporan Program ALP</title>
    <style>
        @page { margin: 18mm 16mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5pt;
            color: #000;
            line-height: 1.4;
        }
        h1, h2, h3 { color: #1e2a5a; margin: 0 0 8px; }
        h1 { font-size: 16pt; text-align: center; text-transform: uppercase; }
        h2 { font-size: 12pt; margin-top: 16px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        h3 { font-size: 11pt; margin-top: 12px; }
        p { margin: 0 0 8px; }
        .notice {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 10px 12px;
            margin-bottom: 16px;
            font-size: 10pt;
        }
        .meta-table { width: 100%; margin: 16px 0 20px; border-collapse: collapse; }
        .meta-table td { padding: 6px 4px; vertical-align: bottom; }
        .meta-label { width: 18%; font-weight: bold; white-space: nowrap; }
        .meta-line { border-bottom: 1px dotted #64748b; min-width: 200px; height: 18px; }
        .meta-hint { font-size: 9pt; color: #64748b; font-style: italic; padding-left: 8px; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 14px;
            font-size: 10pt;
        }
        table.data th, table.data td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            vertical-align: top;
        }
        table.data th { background: #eef1f8; text-align: left; font-weight: bold; }
        .fill-box {
            border: 1px solid #cbd5e1;
            min-height: 64px;
            margin: 6px 0 12px;
            padding: 8px;
        }
        .fill-box.tall { min-height: 96px; }
        .fill-box.xtall { min-height: 120px; }
        .section-num { font-weight: bold; color: #1e2a5a; }
        .hint { font-size: 9.5pt; color: #475569; margin-bottom: 6px; }
        .sign-block { margin-top: 24px; }
        .sign-line { border-bottom: 1px dotted #64748b; width: 55%; height: 20px; margin-top: 28px; }
        .page-break { page-break-before: always; }
        .toc-num { width: 8%; text-align: center; }
        .photo-grid {
            border: 1px dashed #94a3b8;
            min-height: 140px;
            margin: 8px 0;
            text-align: center;
            color: #94a3b8;
            padding-top: 56px;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>
    <div class="notice">
        <strong>Nota:</strong> Ini adalah contoh format laporan penganjuran program/aktiviti kemasyarakatan.
        Laporan hendaklah dikemukakan kepada ALP <strong>satu bulan</strong> selepas penganjuran program dilaksanakan.
    </div>

    <h1>Laporan Program</h1>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Program</td>
            <td><div class="meta-line"></div></td>
            <td class="meta-hint">(Nama program)</td>
        </tr>
        <tr>
            <td class="meta-label">Tarikh</td>
            <td><div class="meta-line"></div></td>
            <td class="meta-hint">(Tarikh program)</td>
        </tr>
        <tr>
            <td class="meta-label">Tempat</td>
            <td><div class="meta-line"></div></td>
            <td class="meta-hint">(Tempat program)</td>
        </tr>
        <tr>
            <td class="meta-label">Anjuran</td>
            <td><div class="meta-line"></div></td>
            <td class="meta-hint">(Nama persatuan/NGO berdaftar ROS)</td>
        </tr>
    </table>

    <h2>Isi Kandungan</h2>
    <table class="data">
        <thead>
            <tr>
                <th class="toc-num">Bil.</th>
                <th>Perkara</th>
                <th style="width: 18%;">Muka Surat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ([
                'Latar Belakang Program',
                'Objektif Program',
                'Komuniti Sasaran',
                'Bilangan Peserta',
                'Tempoh Pelaksanaan',
                'Tentatif Program',
                'Pelaksanaan Program',
                'Isu/Cabaran',
                'Laporan Kewangan',
                'Pencapaian Aktiviti',
                'Gambar Aktiviti',
                'Penutup',
            ] as $i => $label)
                <tr>
                    <td class="toc-num">{{ $i + 1 }}.</td>
                    <td>{{ $label }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2><span class="section-num">1.</span> Latar Belakang Program</h2>
    <p class="hint">Latar belakang, pengisian, kepentingan dan impak program.</p>
    <div class="fill-box xtall"></div>

    <h2><span class="section-num">2.</span> Objektif Program</h2>
    <p class="hint">Penyataan yang menjelaskan tujuan utama yang ingin dicapai oleh suatu program atau kegiatan.</p>
    <div class="fill-box tall"></div>

    <h2><span class="section-num">3.</span> Komuniti Sasaran</h2>
    <p class="hint">Kumpulan/masyarakat tertentu yang menjadi fokus atau menerima manfaat utama daripada program atau kegiatan tersebut.</p>
    <div class="fill-box"></div>

    <h2><span class="section-num">4.</span> Bilangan Peserta yang Terlibat</h2>
    <p class="hint">Jumlah peserta.</p>
    <div class="fill-box" style="min-height: 40px;"></div>

    <h2><span class="section-num">5.</span> Tempoh Pelaksanaan</h2>
    <p class="hint">Tarikh dan tempoh.</p>
    <div class="fill-box" style="min-height: 40px;"></div>

    <h2><span class="section-num">6.</span> Tentatif Program</h2>
    <p class="hint">Jadual rancangan aktiviti yang akan dilaksanakan dalam program atau kegiatan.</p>
    <div class="fill-box tall"></div>

    <div class="page-break"></div>

    <h2><span class="section-num">7.</span> Pelaksanaan Program <span style="font-weight: normal; font-size: 10pt;">(jika ada)</span></h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 28%;">Tarikh dan Hari</th>
                <th>Penerangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['Hari 1', 'Hari 2', 'Hari 3'] as $day)
                <tr>
                    <td>{{ $day }}:</td>
                    <td style="height: 48px;"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2><span class="section-num">8.</span> Isu/Cabaran</h2>
    <p class="hint">Kekangan atau masalah yang dihadapi dalam pelaksanaan program atau kegiatan.</p>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 8%;">Bil.</th>
                <th style="width: 32%;">Isu/Cabaran</th>
                <th>Penerangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ([1, 2, 3] as $n)
                <tr>
                    <td style="text-align: center;">{{ $n }}.</td>
                    <td style="height: 40px;"></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2><span class="section-num">9.</span> Laporan Kewangan</h2>
    <p class="hint">Penyata perbelanjaan yang digunakan untuk tujuan program atau kegiatan.</p>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 8%;">Bil.</th>
                <th>Perkara</th>
                <th style="width: 22%;">Jumlah (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ([1, 2, 3, 4, 5] as $n)
                <tr>
                    <td style="text-align: center;">{{ $n }}.</td>
                    <td style="height: 28px;"></td>
                    <td></td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">Jumlah Keseluruhan</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2><span class="section-num">10.</span> Pencapaian Aktiviti</h2>
    <p class="hint">Hasil yang dicapai setelah melaksanakan aktiviti atau kegiatan dalam rangka mencapai tujuan yang tertentu.</p>
    <div class="fill-box xtall"></div>

    <h2><span class="section-num">11.</span> Gambar Aktiviti</h2>
    <p class="hint">Gambar-gambar aktiviti semasa program.</p>
    <div class="photo-grid">[ Ruang gambar aktiviti ]</div>
    <div class="photo-grid">[ Ruang gambar aktiviti ]</div>

    <h2><span class="section-num">12.</span> Penutup</h2>
    <p class="hint">Kesimpulan terhadap program atau aktiviti — sama ada objektif program tercapai atau tidak.</p>
    <div class="fill-box xtall"></div>

    <div class="sign-block">
        <p><strong>Disediakan oleh:</strong></p>
        <div class="sign-line"></div>
        <p style="margin-top: 16px;"><strong>Tarikh:</strong></p>
        <div class="sign-line" style="width: 35%;"></div>
    </div>

    <p style="margin-top: 20px; font-size: 9pt; color: #64748b;">
        Format Laporan Program ALP
    </p>
</body>
</html>
