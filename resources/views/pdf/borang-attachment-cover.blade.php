<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 48px; color: #111; }
        h2 { font-size: 16px; margin: 0 0 8px; }
        p { font-size: 12px; line-height: 1.5; color: #444; }
    </style>
</head>
<body>
    <h2>Lampiran — {{ $label }}</h2>
    <p><strong>Fail:</strong> {{ $filename }}</p>
    @if ($note ?? null)
        <p>{{ $note }}</p>
    @endif
</body>
</html>
