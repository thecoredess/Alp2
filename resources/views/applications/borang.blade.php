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
        .sign { margin-top: 36px; display: flex; justify-content: space-between; gap: 24px; }
        .sign div { width: 48%; font-size: 12px; }
        .line { margin-top: 56px; border-top: 1px solid #333; padding-top: 6px; }
        @media print { body { margin: 12mm; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    @php
        $jpReview = $application->reviews->where('review_type', \App\Enums\ReviewType::SECRETARIAT)->last();
        $jpLabel = match ($jpReview?->decision) {
            \App\Enums\ReviewDecision::RECOMMEND => 'DISYORKAN',
            \App\Enums\ReviewDecision::NOT_RECOMMENDED => 'TIDAK DISYORKAN',
            \App\Enums\ReviewDecision::RETURN_FOR_REVISION => 'DIPULANGKAN',
            default => '—',
        };
    @endphp

    <div class="no-print" style="margin-bottom: 16px; font-family: system-ui, sans-serif;">
        <button onclick="window.print()" style="padding: 8px 14px; cursor: pointer;">Cetak</button>
        <a href="{{ route('applications.show', $application) }}" style="margin-left: 12px;">Kembali</a>
    </div>

    <div style="text-align:center; margin-bottom: 12px;">
        <img src="{{ asset('images/logo-alp-dbkl.png') }}" alt="Logo DBKL — Sistem ALP"
             style="max-width: 240px; width: 100%; height: auto;">
    </div>
    <h1>Borang Penyaluran Sumbangan<br>Ahli Lembaga Penasihat Bandaraya Kuala Lumpur</h1>
    <p class="sub">{{ $templates[\App\Support\UrsDocumentTemplates::KEY_BORANG_HEADER] ?? 'Sistem Pengurusan Sumbangan ALP' }} · BR-020 · TBL-10 · {{ $application->application_number }}</p>

    <table class="form">
        <tr><th>A · Nama ALP</th><td>{{ $application->alp->ref_code }} — {{ $application->alp->name }}</td></tr>
        <tr><th>B · Nama Persatuan</th><td>{{ $application->recipient_name ?? '—' }}</td></tr>
        <tr><th>C · No. ROS</th><td>{{ $application->recipient_ros_number ?? '—' }}</td></tr>
        <tr><th>D · Tarikh Program</th><td>{{ $application->program_date?->format('d/m/Y') ?? '—' }}</td></tr>
        <tr><th>E · Jenis/Kategori Program</th><td>{{ $application->program_category?->label() ?? '—' }}</td></tr>
        <tr><th>F · Jumlah Sumbangan</th><td><strong>RM {{ number_format((float) $application->requested_amount, 2) }}</strong></td></tr>
        <tr><th>G · Tujuan Sumbangan</th><td>{{ $application->purpose }}</td></tr>
        <tr><th>H · No. Akaun Penerima</th><td>{{ $application->recipient_bank_account ?? '—' }}</td></tr>
        <tr><th>I · Alamat Persatuan</th><td>{{ $application->recipient_address ?? '—' }}</td></tr>
    </table>

    <p class="section">Ruang kegunaan JP (F–J)</p>
    <table class="form">
        <tr><th>F · Peruntukan</th><td>RM {{ number_format((float) $tbl10->allocation->value(), 2) }}</td></tr>
        <tr><th>G · Komitmen / belanja diluluskan</th><td>RM {{ number_format((float) $tbl10->approvedSpend->value(), 2) }}</td></tr>
        <tr><th>H · Baki peruntukan</th><td>RM {{ number_format((float) $tbl10->balance->value(), 2) }}</td></tr>
        <tr><th>I · Permohonan semasa</th><td>RM {{ number_format((float) $tbl10->currentRequest->value(), 2) }}</td></tr>
        <tr><th>J · Baki selepas pending / permohonan</th><td>RM {{ number_format((float) $tbl10->balanceAfter->value(), 2) }}</td></tr>
        <tr><th>Status sistem</th><td>{{ $application->status->label() }}</td></tr>
    </table>

    <p class="section">Perakuan / Keputusan (K–L)</p>
    <table class="form">
        <tr>
            <th>K · Semakan Pegawai JP</th>
            <td>
                <strong>{{ $jpLabel }}</strong>
                @if ($jpReview)
                    · {{ $jpReview->reviewer?->name ?? '—' }}
                    · {{ $jpReview->reviewed_at?->format('d/m/Y H:i') ?? '—' }}
                @endif
            </td>
        </tr>
        @forelse ($application->approvals->where('decision', \App\Enums\ApprovalDecision::APPROVED) as $a)
            <tr>
                <th>L · {{ $a->approvalLevel?->name ?? 'Kelulusan' }}</th>
                <td>{{ $a->approver?->name ?? '—' }} · {{ $a->decided_at?->format('d/m/Y H:i') ?? '—' }} · {{ $a->decision->label() }}</td>
            </tr>
        @empty
            <tr><th>L · Keputusan PEPU / Pengurusan</th><td>Belum ada rekod kelulusan</td></tr>
        @endforelse
    </table>

    @if ($application->documents->isNotEmpty())
        <p class="section">Lampiran dimuat naik</p>
        <table class="form">
            @foreach ($application->documents as $doc)
                <tr>
                    <th>{{ $doc->document_type->label() }}</th>
                    <td>{{ $doc->original_filename }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="sign">
        <div>
            <div class="line">Pegawai JP / Peraku (K)</div>
        </div>
        <div>
            <div class="line">PEPU / Pengurusan Tertinggi (L)</div>
        </div>
    </div>
    <p style="margin-top:24px; font-size:11px; color:#555;">{{ $templates[\App\Support\UrsDocumentTemplates::KEY_BORANG_FOOTER] }} · {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
