@php
    $jpReview = $application->reviews->where('review_type', \App\Enums\ReviewType::SECRETARIAT)->last();
    $jpLabel = match ($jpReview?->decision) {
        \App\Enums\ReviewDecision::RECOMMEND => 'DISYORKAN',
        \App\Enums\ReviewDecision::NOT_RECOMMENDED => 'TIDAK DISYORKAN',
        \App\Enums\ReviewDecision::RETURN_FOR_REVISION => 'DIPULANGKAN',
        default => '—',
    };
@endphp

<div style="text-align:center; margin-bottom: 12px;">
    <img src="{{ $logoSrc }}" alt="Logo DBKL — Sistem ALP"
         style="max-width: 240px; width: 100%; height: auto;">
</div>
<h1>Borang Penyaluran Sumbangan<br>Ahli Lembaga Penasihat Bandaraya Kuala Lumpur</h1>
<p class="sub">{{ $templates[\App\Support\UrsDocumentTemplates::KEY_BORANG_HEADER] ?? 'Sistem Pengurusan Sumbangan ALP' }} · {{ $application->application_number }}</p>

<table class="form">
    <tr><th>Nama ALP</th><td>{{ $application->alp->ref_code }} — {{ $application->alp->name }}</td></tr>
    <tr><th>Nama Persatuan</th><td>{{ $application->recipient_name ?? '—' }}</td></tr>
    <tr><th>No. ROS</th><td>{{ $application->recipient_ros_number ?? '—' }}</td></tr>
    <tr><th>Tarikh Program</th><td>{{ $application->program_date?->format('d/m/Y') ?? '—' }}</td></tr>
    <tr><th>Jenis/Kategori Program</th><td>{{ $application->program_category?->label() ?? '—' }}</td></tr>
    <tr><th>Jumlah Sumbangan</th><td><strong>RM {{ number_format((float) $application->requested_amount, 2) }}</strong></td></tr>
    <tr><th>Tujuan Sumbangan</th><td>{{ $application->purpose }}</td></tr>
    <tr><th>No. Akaun Penerima</th><td>{{ $application->recipient_bank_account ?? '—' }}</td></tr>
    <tr><th>Alamat Persatuan</th><td>{{ $application->recipient_address ?? '—' }}</td></tr>
</table>

<p class="section">Ruang kegunaan JP</p>
<table class="form">
    <tr><th>Peruntukan</th><td>RM {{ number_format((float) $tbl10->allocation->value(), 2) }}</td></tr>
    <tr><th>Belanja diluluskan</th><td>RM {{ number_format((float) $tbl10->approvedSpend->value(), 2) }}</td></tr>
    <tr><th>Baki peruntukan</th><td>RM {{ number_format((float) $tbl10->balance->value(), 2) }}</td></tr>
    <tr><th>Permohonan semasa</th><td>RM {{ number_format((float) $tbl10->currentRequest->value(), 2) }}</td></tr>
    <tr><th>Baki selepas pending / permohonan</th><td>RM {{ number_format((float) $tbl10->balanceAfter->value(), 2) }}</td></tr>
    <tr><th>Status sistem</th><td>{{ $application->status->label() }}</td></tr>
</table>

<p class="section">Perakuan / Keputusan</p>
<table class="form">
    <tr>
        <th>{{ \App\Enums\ReviewType::SECRETARIAT->label() }}</th>
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
            <th>{{ $a->approvalLevel?->name ?? 'Kelulusan' }}</th>
            <td>{{ $a->approver?->name ?? '—' }} · {{ $a->decided_at?->format('d/m/Y H:i') ?? '—' }} · {{ $a->displayDecisionLabel($application) }}</td>
        </tr>
    @empty
        <tr><th>Keputusan PEPU / Pengurusan</th><td>Belum ada rekod kelulusan</td></tr>
    @endforelse
</table>

@if ($application->documents->isNotEmpty())
    <p class="section">Senarai lampiran (dimuat naik)</p>
    <table class="form">
        @foreach ($application->documents as $doc)
            <tr>
                <th>{{ $doc->document_type->label() }}</th>
                <td>{{ $doc->original_filename }}</td>
            </tr>
        @endforeach
    </table>
    <p style="font-size: 11px; color: #555;">Kandungan lampiran disertakan selepas borang ini dalam fail PDF gabungan.</p>
@endif

<table class="sign-table" style="width:100%; margin-top:36px; border:none;">
    <tr>
        <td style="width:50%; vertical-align:top; border:none; padding-right:12px;">
            <div class="line">Pegawai JP / Peraku</div>
        </td>
        <td style="width:50%; vertical-align:top; border:none;">
            <div class="line">PEPU / Pengurusan Tertinggi</div>
        </td>
    </tr>
</table>
<p style="margin-top:24px; font-size:11px; color:#555;">{{ $templates[\App\Support\UrsDocumentTemplates::KEY_BORANG_FOOTER] }} · {{ now()->format('d/m/Y H:i') }}</p>
