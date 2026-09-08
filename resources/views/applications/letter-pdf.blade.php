<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Surat Pemakluman — {{ $application->application_number }}</title>
    <style>
        @page { margin: 0 2.5cm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; line-height: 1.5; margin: 0; padding: 0; }
        p { margin: 0; padding: 0; }
        .letterhead-space { margin: 0; padding: 0; }
        .head-gap { height: 1.5em; line-height: 1.5; font-size: 11pt; }
        .ref-block { margin: 0 0 0.5cm; padding: 0; text-align: right; }
        .ref-table { margin-left: auto; border-collapse: collapse; table-layout: fixed; }
        .ref-table td { margin: 0; padding: 0; line-height: 1.25; vertical-align: top; font-size: 11pt; }
        .ref-label { width: 105px; text-align: right; padding-right: 0; white-space: nowrap; font-weight: bold; }
        .ref-colon { width: 12px; text-align: center; padding: 0 4px; font-weight: bold; }
        .ref-value { text-align: left; white-space: nowrap; }
        .recipient { margin: 0 0 0.5cm; }
        .recipient .name { margin: 0; text-transform: uppercase; }
        .recipient .address { margin: 0; white-space: pre-line; }
        .salutation { margin: 0; }
        .title { font-weight: 700; text-transform: uppercase; margin: 0; padding: 0; font-size: 11pt; text-align: justify; line-height: 1.2; }
        .title strong { font-weight: 700; }
        .para { margin: 0 0 0.5cm; text-align: justify; }
        .motto { margin-top: 0.5cm; text-align: left; font-size: 11pt; line-height: 1.35; }
        .motto p { font-weight: bold; margin: 0; }
        .sign { margin-top: 0.5cm; }
        .sign p { margin: 0; line-height: 1.3; }
        .sign-gap { height: 1.3em; line-height: 1.3; font-size: 11pt; }
        .sign-name { margin-top: 0; font-weight: bold; }
        a { color: #000; text-decoration: none; }
    </style>
</head>
<body>
    @include('applications.partials.letter-body')
</body>
</html>
