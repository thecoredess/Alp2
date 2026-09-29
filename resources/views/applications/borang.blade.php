<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Borang Penyaluran — {{ $application->application_number }}</title>
    <style>
        body { font-family: Georgia, 'Times New Roman', serif; color: #111; margin: 36px; line-height: 1.4; font-size: 13px; }
        h1 { font-size: 16px; margin: 0 0 4px; text-align: center; text-transform: uppercase; }
        .sub { text-align: center; font-size: 11px; color: #444; margin-bottom: 20px; }
        table.form { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.form th, table.form td { border: 1px solid #333; padding: 7px 8px; vertical-align: top; text-align: left; }
        table.form th { width: 34%; background: #f5f5f5; font-weight: 600; }
        .section { margin-top: 18px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .line { margin-top: 56px; border-top: 1px solid #333; padding-top: 6px; }
        @media print { body { margin: 12mm; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px; font-family: system-ui, sans-serif;">
        <a href="{{ route('applications.borang.pdf', $application) }}" style="padding: 8px 14px; background: #1e3a5f; color: #fff; text-decoration: none; border-radius: 6px;">Muat turun PDF (borang + lampiran)</a>
        <button type="button" onclick="window.print()" style="padding: 8px 14px; cursor: pointer; margin-left: 8px;">Cetak</button>
        <a href="{{ route('applications.show', $application) }}" style="margin-left: 12px;">Kembali</a>
    </div>

    @include('applications.partials.borang-penyaluran-content')
</body>
</html>
