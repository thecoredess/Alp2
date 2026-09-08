<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Surat Pemakluman — {{ $application->application_number }}</title>
    <style>
        @page { margin: 0 2.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; margin: 0; padding: 0 2.5cm; }
        p { margin: 0; padding: 0; }
        .letterhead-space { margin: 0; padding: 0; }
        .head-gap { height: 1.5em; line-height: 1.5; margin: 0; }
        .toolbar { font-family: system-ui, sans-serif; margin-bottom: 20px; }
        .toolbar button, .toolbar a { font-size: 14px; }
        .toolbar button { padding: 8px 14px; cursor: pointer; }
        .toolbar a { margin-left: 12px; text-decoration: none; color: #1e3a5f; }
        .ref-block { margin: 0 0 0.5cm; padding: 0; text-align: right; }
        .ref-table { margin-left: auto; border-collapse: collapse; table-layout: fixed; }
        .ref-table td { margin: 0; padding: 0; line-height: 1.25; vertical-align: top; }
        .ref-label { width: 105px; text-align: right; padding-right: 0; white-space: nowrap; font-weight: bold; }
        .ref-colon { width: 12px; text-align: center; padding: 0 4px; font-weight: bold; }
        .ref-value { text-align: left; white-space: nowrap; }
        .recipient { margin: 0 0 0.5cm; }
        .recipient .name { margin: 0; text-transform: uppercase; }
        .recipient .address { margin: 0; white-space: pre-line; }
        .title { font-weight: 700; text-transform: uppercase; text-align: justify; margin: 0; padding: 0; font-size: 11pt; line-height: 1.2; }
        .title strong { font-weight: 700; }
        .salutation { margin: 0; }
        .para { margin: 0 0 0.5cm; text-align: justify; }
        .para-num { font-weight: normal; }
        .motto { margin-top: 0.5cm; text-align: left; font-size: 11pt; line-height: 1.6; }
        .motto p { margin: 0; font-weight: bold; }
        .sign { margin-top: 0.5cm; font-size: 11pt; }
        .sign p { margin: 0 0 2px; }
        .sign-gap { height: 1.3em; line-height: 1.3; margin: 0; }
        .sign-name { margin-top: 0; font-weight: bold; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print toolbar">
        <button type="button" onclick="window.print()">Cetak</button>
        <a href="{{ route('applications.letter.pdf', $application) }}">Muat turun PDF</a>
        <a href="{{ route('applications.show', $application) }}">Kembali</a>
    </div>

    @include('applications.partials.letter-body')
</body>
</html>
