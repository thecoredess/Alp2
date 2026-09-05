<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Surat Kelulusan — {{ $application->application_number }}</title>
    <style>
        body { font-family: Georgia, 'Times New Roman', serif; color: #111; margin: 40px; line-height: 1.45; }
        h1 { font-size: 18px; margin: 0 0 4px; text-align: center; }
        .sub { text-align: center; font-size: 12px; color: #444; margin-bottom: 28px; }
        .meta { width: 100%; border-collapse: collapse; margin: 16px 0 24px; font-size: 13px; }
        .meta th, .meta td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #ddd; vertical-align: top; }
        .meta th { width: 32%; color: #555; font-weight: 600; }
        .box { border: 1px solid #ccc; padding: 14px 16px; margin: 18px 0; }
        .sign { margin-top: 48px; display: flex; justify-content: space-between; gap: 40px; }
        .sign div { width: 45%; font-size: 12px; }
        .line { margin-top: 64px; border-top: 1px solid #333; padding-top: 6px; }
        @media print {
            body { margin: 16mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; font-family: system-ui, sans-serif;">
        <button onclick="window.print()" style="padding: 8px 14px; cursor: pointer;">Cetak</button>
        <a href="{{ route('applications.show', $application) }}" style="margin-left: 12px;">Kembali</a>
    </div>

    <div style="text-align:center; margin-bottom: 14px;">
        <img src="{{ asset('images/logo-alp-dbkl.png') }}" alt="Logo DBKL — Sistem ALP"
             style="max-width: 240px; width: 100%; height: auto;">
    </div>
    <h1>SURAT / RINGKASAN KELULUSAN PERMOHONAN</h1>
    <p class="sub">{{ $templates[\App\Support\UrsDocumentTemplates::KEY_LETTER_HEADER] ?? 'Sistem Pengurusan Sumbangan ALP Bandaraya Kuala Lumpur' }}</p>

    <table class="meta">
        <tr><th>No. Permohonan</th><td>{{ $application->application_number }}</td></tr>
        <tr><th>Tajuk</th><td>{{ $application->project_title }}</td></tr>
        <tr><th>ALP</th><td>{{ $application->alp->ref_code }} — {{ $application->alp->name }}</td></tr>
        <tr><th>Tahun Kewangan</th><td>{{ $application->financialYear->year }}</td></tr>
        <tr><th>Jenis</th><td>{{ $application->application_type->label() }}</td></tr>
        <tr><th>Jumlah Diluluskan</th><td><strong>RM {{ number_format((float) $application->requested_amount, 2) }}</strong></td></tr>
        <tr><th>Status</th><td>{{ $application->status->label() }}</td></tr>
        <tr><th>Tarikh Jana</th><td>{{ now()->format('d/m/Y H:i') }}</td></tr>
    </table>

    <div class="box">
        <p style="margin:0 0 8px; font-size:13px;"><strong>Keputusan</strong></p>
        <p style="margin:0; font-size:13px; white-space: pre-line;">{{ $templates[\App\Support\UrsDocumentTemplates::KEY_LETTER_BODY] }}</p>
    </div>

    @if ($application->approvals->isNotEmpty())
        <p style="font-size:13px; font-weight:600; margin-bottom:8px;">Jejak Kelulusan</p>
        <table class="meta">
            <tr><th>Aras</th><th>Peraku / PEPU</th><th>Tarikh</th></tr>
            @foreach ($application->approvals->filter(fn ($a) => $a->decision === \App\Enums\ApprovalDecision::APPROVED) as $a)
                <tr>
                    <td>{{ $a->approvalLevel?->name ?? '—' }}</td>
                    <td>{{ $a->approver?->name ?? '—' }}</td>
                    <td>{{ $a->decided_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="sign">
        <div>
            <div class="line">Pegawai Berkenaan / Sistem</div>
        </div>
        <div>
            <div class="line">Pengesahan / Cap</div>
        </div>
    </div>

    <p style="margin-top:36px; font-size:11px; color:#666; white-space: pre-line;">
        {{ $templates[\App\Support\UrsDocumentTemplates::KEY_LETTER_FOOTER] }}
    </p>
</body>
</html>
