<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 24px; color: #111; }
        h2 { font-size: 14px; margin: 0 0 12px; }
        img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <h2>Lampiran — {{ $label }}</h2>
    <p style="font-size: 11px; color: #666; margin-bottom: 16px;">{{ $filename }}</p>
    <img src="data:{{ $mime }};base64,{{ $data }}" alt="">
</body>
</html>
