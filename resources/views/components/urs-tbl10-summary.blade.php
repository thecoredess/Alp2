@props([
    'application',
    'snapshot' => null,
    'ledgerAvailable' => null,
    'pending' => null,
    'committed' => null,
])

@php
    use App\Support\Money;
    use App\Support\Tbl10BudgetSnapshot;

    if ($snapshot instanceof Tbl10BudgetSnapshot) {
        $f = $snapshot->allocation;
        $g = $snapshot->approvedSpend;
        $h = $snapshot->balance;
        $i = $snapshot->currentRequest;
        $j = $snapshot->balanceAfter;
        $pendingAmt = $snapshot->pending;
    } else {
        $h = $ledgerAvailable instanceof Money ? $ledgerAvailable : Money::zero();
        $pendingAmt = $pending instanceof Money ? $pending : Money::zero();
        $g = $committed instanceof Money ? $committed : Money::zero();
        $f = $h->plus($g); // anggaran jika peruntukan penuh tidak dihantar
        $i = Money::of((string) ($application->requested_amount ?? '0'));
        $j = $h->minus($pendingAmt);
        if ($j->isNegative()) {
            $j = Money::zero();
        }
    }

    $jpReview = $application->relationLoaded('reviews')
        ? $application->reviews->where('review_type', \App\Enums\ReviewType::SECRETARIAT)->last()
        : null;
    $jpLabel = match ($jpReview?->decision) {
        \App\Enums\ReviewDecision::RECOMMEND => 'DISYORKAN',
        \App\Enums\ReviewDecision::NOT_RECOMMENDED => 'TIDAK DISYORKAN',
        \App\Enums\ReviewDecision::RETURN_FOR_REVISION => 'DIPULANGKAN',
        default => null,
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-100 bg-gray-50 p-4']) }}>
    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Ringkasan TBL-10 (Borang Penyaluran)</p>
    <dl class="grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
        <div><dt class="text-gray-500">A · ALP</dt><dd>{{ $application->alp?->name ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">B · Nama Persatuan</dt><dd>{{ $application->recipient_name ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">C · No. ROS</dt><dd class="font-mono text-xs">{{ $application->recipient_ros_number ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">D · Tarikh Program</dt><dd>{{ $application->program_date?->format('d/m/Y') ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">E · Jenis/Kategori Program</dt><dd>{{ $application->program_category?->label() ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">F · Jumlah</dt><dd class="font-semibold"><x-money :value="$application->requested_amount" /></dd></div>
        <div><dt class="text-gray-500">G · Tujuan</dt><dd>{{ $application->purpose }}</dd></div>
        <div><dt class="text-gray-500">H · No. Akaun</dt><dd class="font-mono text-xs">{{ $application->recipient_bank_account ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">I · Alamat Persatuan</dt><dd>{{ $application->recipient_address ?? '—' }}</dd></div>
    </dl>

    <p class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Ruang JP (F–J)</p>
    <dl class="grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
        <div><dt class="text-gray-500">F · Peruntukan</dt><dd><x-money :value="$f" /></dd></div>
        <div><dt class="text-gray-500">G · Belanja diluluskan</dt><dd><x-money :value="$g" /></dd></div>
        <div><dt class="text-gray-500">H · Baki peruntukan</dt><dd><x-money :value="$h" /></dd></div>
        <div><dt class="text-gray-500">I · Permohonan semasa</dt><dd><x-money :value="$i" /></dd></div>
        <div><dt class="text-gray-500">J · Baki selepas pending / permohonan</dt><dd><x-money :value="$j" /></dd></div>
        <div><dt class="text-gray-500">Permohonan dalam proses (rujukan)</dt><dd><x-money :value="$pendingAmt" /></dd></div>
    </dl>

    @if ($jpLabel || ($application->relationLoaded('approvals') && $application->approvals->isNotEmpty()))
        <p class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Perakuan / Keputusan (K–L)</p>
        <dl class="grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
            @if ($jpLabel)
                <div>
                    <dt class="text-gray-500">K · Semakan JP</dt>
                    <dd>
                        <strong>{{ $jpLabel }}</strong>
                        @if ($jpReview?->reviewer)
                            · {{ $jpReview->reviewer->name }}
                        @endif
                        @if ($jpReview?->reviewed_at)
                            · {{ $jpReview->reviewed_at->format('d/m/Y') }}
                        @endif
                    </dd>
                </div>
            @endif
            @foreach ($application->approvals->where('decision', \App\Enums\ApprovalDecision::APPROVED) as $a)
                <div>
                    <dt class="text-gray-500">L · {{ $a->approvalLevel?->name ?? 'Kelulusan' }}</dt>
                    <dd>{{ $a->approver?->name ?? '—' }} · {{ $a->decided_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

</div>
