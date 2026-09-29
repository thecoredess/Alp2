<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Borang Penyaluran — {{ $application->application_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; margin: 36px; line-height: 1.4; font-size: 13px; }
        h1 { font-size: 16px; margin: 0 0 4px; text-align: center; text-transform: uppercase; }
        .sub { text-align: center; font-size: 11px; color: #444; margin-bottom: 20px; }
        table.form { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.form th, table.form td { border: 1px solid #333; padding: 7px 8px; vertical-align: top; text-align: left; }
        table.form th { width: 34%; background: #f5f5f5; font-weight: 600; }
        .section { margin-top: 18px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .sign { margin-top: 36px; width: 100%; }
        .sign-table { width: 100%; border: none; }
        .sign-table td { width: 50%; vertical-align: top; border: none; padding: 0 8px 0 0; font-size: 12px; }
        .line { margin-top: 56px; border-top: 1px solid #333; padding-top: 6px; }
    </style>
</head>
<body>
    @include('applications.partials.borang-penyaluran-content')
</body>
</html>
