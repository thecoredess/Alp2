@extends('layouts.app')
@section('title', $application->application_number)
@section('heading', $application->project_title)
@section('subheading', $application->application_number.' · '.$application->application_type->label())

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('applications.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
        </a>
        <div class="flex items-center gap-3">
            <x-status-badge :label="$application->status->label()" :classes="$application->status->badgeClasses()" class="text-sm px-3 py-1" />
            @if ($application->isDraft() && auth()->user()->can('update', $application))
                <a href="{{ route('applications.wizard.maklumat', $application) }}" class="btn-primary">Sambung Draf</a>
            @elseif ($application->status === \App\Enums\ApplicationStatus::REVISION_REQUIRED && auth()->user()->can('update', $application))
                <a href="{{ route('applications.wizard.maklumat', $application) }}" class="btn-primary">Buat Pembetulan</a>
            @elseif ($application->status === \App\Enums\ApplicationStatus::APPROVED)
                <a href="{{ route('applications.borang', $application) }}" target="_blank" class="btn-white">Cetak Borang Penyaluran</a>
                <a href="{{ route('applications.letter', $application) }}" target="_blank" class="btn-navy">Cetak Surat Kelulusan</a>
            @endif
            @can('view', $application)
                @if ($application->status !== \App\Enums\ApplicationStatus::APPROVED)
                    <a href="{{ route('applications.borang', $application) }}" target="_blank" class="btn-white text-xs">Borang Penyaluran</a>
                @endif
            @endcan
        </div>
    </div>

    {{-- Banner pembetulan --}}
    @if ($activeRevision && auth()->user()->can('update', $application))
        <div class="mb-5 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
            <p class="font-semibold">Pembetulan Diperlukan</p>
            <p class="mt-1">Peringkat: <strong>{{ ucfirst($activeRevision->return_stage) }}</strong></p>
            @if ($activeRevision->reason)<p>Sebab: {{ $activeRevision->reason }}</p>@endif
            <p class="mt-1 text-xs">Sila betulkan permohonan melalui butang "Buat Pembetulan", kemudian hantar semula.</p>
        </div>
    @endif

    <div x-data="{ tab: 'ringkasan' }">
        {{-- Tabs --}}
        <div class="mb-4 flex flex-wrap gap-1 border-b border-gray-200">
            @foreach (['ringkasan' => 'Ringkasan', 'bajet' => 'Bajet', 'dokumen' => 'Dokumen', 'report' => 'Report Card', 'timeline' => 'Timeline', 'semakan' => 'Semakan', 'kelulusan' => 'Kelulusan', 'aktiviti' => 'Status / Aktiviti'] as $key => $label)
                <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-navy-700 text-navy-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="border-b-2 px-4 py-2 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Ringkasan --}}
        <div x-show="tab === 'ringkasan'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="card p-6 lg:col-span-2 space-y-4">
                <x-urs-tbl10-summary :application="$application" :snapshot="$tbl10Snapshot" />
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">ALP</dt><dd class="text-gray-900">{{ $application->alp->ref_code }} — {{ $application->alp->name }}</dd></div>
                    <div><dt class="text-gray-500">Tahun Kewangan</dt><dd class="text-gray-900">{{ $application->financialYear->year }}</dd></div>
                    <div><dt class="text-gray-500">Penerima / Persatuan</dt><dd class="text-gray-900">{{ $application->recipient_name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">No. ROS</dt><dd class="text-gray-900 font-mono">{{ $application->recipient_ros_number ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Jenis Program</dt><dd class="text-gray-900">{{ $application->program_category?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Lokasi</dt><dd class="text-gray-900">{{ $application->location ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Tarikh</dt><dd class="text-gray-900">{{ $application->proposed_start_date?->format('d/m/Y') ?? '—' }} – {{ $application->proposed_end_date?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Alamat Penerima</dt><dd class="text-gray-900">{{ $application->recipient_address ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Ringkasan</dt><dd class="text-gray-900 whitespace-pre-line">{{ $application->project_summary ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Objektif</dt><dd class="text-gray-900 whitespace-pre-line">{{ $application->objectives ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Skop</dt><dd class="text-gray-900 whitespace-pre-line">{{ $application->scope ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Kumpulan Sasaran</dt><dd class="text-gray-900">{{ $application->target_group ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Dihantar</dt><dd class="text-gray-900">{{ $application->submitted_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                </dl>
            </div>
            <div class="card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Kedudukan Kewangan</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Jumlah Dipohon</dt><dd class="font-semibold text-navy-700"><x-money :value="$application->requested_amount" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Ledger Available (ALP)</dt><dd class="text-green-600"><x-money :value="$ledgerAvailable" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Pending Request (ALP)</dt><dd class="text-orange-600"><x-money :value="$pending" /></dd></div>
                </dl>
                <p class="mt-3 text-[11px] text-gray-400">Pending Request BUKAN Committed.</p>

                @if ($application->commitmentTransaction)
                    <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3">
                        <p class="text-xs font-semibold text-green-800">Komitmen Bajet Dicipta</p>
                        <dl class="mt-1 space-y-1 text-xs text-green-900">
                            <div class="flex justify-between"><dt>Jenis</dt><dd>COMMITMENT</dd></div>
                            <div class="flex justify-between"><dt>Jumlah</dt><dd><x-money :value="$application->commitmentTransaction->amount" /></dd></div>
                            <div class="flex justify-between"><dt>Rujukan</dt><dd class="font-mono">{{ $application->commitmentTransaction->reference_no }}</dd></div>
                            <div class="flex justify-between"><dt>Tarikh</dt><dd>{{ $application->commitmentTransaction->created_at?->format('d/m/Y H:i') }}</dd></div>
                        </dl>
                    </div>
                @endif

                @if ($application->status === \App\Enums\ApplicationStatus::APPROVED && $application->payment_status)
                    <div class="mt-4 rounded-lg border border-royal-200 bg-royal-50 p-3">
                        <p class="text-xs font-semibold text-royal-800">Pembayaran / Baucar (URS)</p>
                        <dl class="mt-1 space-y-1 text-xs text-royal-900">
                            <div class="flex justify-between items-center">
                                <dt>Status</dt>
                                <dd>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium {{ $application->payment_status->badgeClasses() }}">
                                        {{ $application->payment_status->label() }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between"><dt>No. Baucar</dt><dd class="font-mono">{{ $application->payment_voucher_no ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt>Rujukan</dt><dd>{{ $application->payment_reference ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt>Dibayar</dt><dd>{{ $application->paid_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                        </dl>
                        @if ($application->payment_remarks)
                            <p class="mt-2 text-[11px] text-royal-800">{{ $application->payment_remarks }}</p>
                        @endif
                        <p class="mt-2 text-[10px] text-royal-600">Status bayaran (baucar) direkod pada permohonan — URS M06.</p>
                    </div>

                    @can('payments.manage')
                        <form method="POST" action="{{ route('payments.update', $application) }}" class="mt-3 space-y-2 rounded-lg border border-gray-200 bg-white p-3">
                            @csrf
                            @method('PUT')
                            <p class="text-xs font-semibold text-gray-800">Kemas kini status bayaran / JKEW</p>
                            <select name="payment_status" class="inp w-full text-xs" required>
                                @foreach (\App\Enums\ApplicationPaymentStatus::cases() as $ps)
                                    <option value="{{ $ps->value }}" @selected(old('payment_status', $application->payment_status->value) === $ps->value)>{{ $ps->label() }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="payment_voucher_no" class="inp w-full text-xs" placeholder="No. baucar"
                                   value="{{ old('payment_voucher_no', $application->payment_voucher_no) }}">
                            <input type="text" name="payment_reference" class="inp w-full text-xs" placeholder="Rujukan bayaran"
                                   value="{{ old('payment_reference', $application->payment_reference) }}">
                            <label class="block text-[11px] text-gray-500">Tarikh hantar ke JKEW (UR-M06-002)</label>
                            <input type="datetime-local" name="sent_to_jkew_at" class="inp w-full text-xs"
                                   value="{{ old('sent_to_jkew_at', $application->sent_to_jkew_at?->format('Y-m-d\TH:i')) }}">
                            <label class="block text-[11px] text-gray-500">Semakan silang JPKKB (BR-013)</label>
                            <select name="jkew_crosscheck_status" class="inp w-full text-xs">
                                <option value="">— Pilih —</option>
                                @foreach (\App\Enums\JkewCrosscheckStatus::options() as $val => $label)
                                    <option value="{{ $val }}" @selected(old('jkew_crosscheck_status', $application->jkew_crosscheck_status?->value) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <textarea name="jkew_crosscheck_remarks" rows="2" class="inp w-full text-xs" placeholder="Catatan semakan silang">{{ old('jkew_crosscheck_remarks', $application->jkew_crosscheck_remarks) }}</textarea>
                            <input type="datetime-local" name="paid_at" class="inp w-full text-xs"
                                   value="{{ old('paid_at', $application->paid_at?->format('Y-m-d\TH:i')) }}">
                            <textarea name="payment_remarks" rows="2" class="inp w-full text-xs" placeholder="Catatan">{{ old('payment_remarks', $application->payment_remarks) }}</textarea>
                            <button type="submit" class="btn-primary w-full !py-1.5 text-xs">Simpan Status Bayaran</button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Bajet --}}
        <div x-show="tab === 'bajet'" x-cloak class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Item</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3 text-right">Harga Seunit</th><th class="px-4 py-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($application->budgetItems as $item)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $item->description }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->unit ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-600"><x-money :value="$item->unit_cost" /></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900"><x-money :value="$item->total" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tiada item bajet.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold"><td class="px-4 py-3" colspan="4">Jumlah Dipohon</td>
                    <td class="px-4 py-3 text-right text-navy-700"><x-money :value="$application->requested_amount" /></td></tr>
                </tfoot>
            </table>
        </div>

        {{-- Dokumen --}}
        <div x-show="tab === 'dokumen'" x-cloak class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Nama Fail</th>
                        <th class="px-4 py-3">Dimuat Naik Oleh</th><th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($application->documents as $doc)
                        <tr>
                            <td class="px-4 py-3 text-gray-700">{{ $doc->document_type->label() }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $doc->original_filename }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $doc->uploader?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('applications.documents.download', [$application, $doc]) }}" class="text-royal-600 hover:text-royal-700 text-xs font-medium">Muat Turun</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tiada dokumen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Report Card M07 --}}
        <div x-show="tab === 'report'" x-cloak class="card p-6 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Laporan Aktiviti / Report Card (M07)</h3>
                    <p class="mt-1 text-xs text-gray-500">BR-018: kemukakan dalam 1 bulan selepas aktiviti. BR-019: wajib untuk rujukan DBKL.</p>
                </div>
                @if ($application->status === \App\Enums\ApplicationStatus::APPROVED)
                    @if ($hasReportCard ?? false)
                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                            Diterima {{ $application->report_card_submitted_at?->format('d/m/Y') ?? '' }}
                        </span>
                    @elseif ($reportCardOverdue ?? false)
                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">Tertunggak</span>
                    @else
                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                            Tarikh akhir: {{ $reportCardDue?->format('d/m/Y') ?? '—' }}
                        </span>
                    @endif
                @endif
            </div>

            @if ($application->report_card_remarks)
                <p class="text-sm text-gray-700">Catatan: {{ $application->report_card_remarks }}</p>
            @endif

            @php
                $reportDocs = $application->documents->filter(fn ($d) => in_array($d->document_type, [
                    \App\Enums\DocumentType::REPORT_CARD,
                    \App\Enums\DocumentType::LAPORAN_AKTIVITI,
                ], true));
            @endphp
            @if ($reportDocs->isNotEmpty())
                <ul class="divide-y divide-gray-100 rounded-lg border border-gray-100">
                    @foreach ($reportDocs as $doc)
                        <li class="flex items-center justify-between px-3 py-2 text-sm">
                            <span>{{ $doc->document_type->label() }} — {{ $doc->original_filename }}</span>
                            <a href="{{ route('applications.documents.download', [$application, $doc]) }}" class="text-xs font-medium text-royal-600">Muat turun</a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @can('uploadReportCard', $application)
                <form method="POST" action="{{ route('applications.report-card.store', $application) }}" enctype="multipart/form-data" class="space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    @csrf
                    <p class="text-xs font-semibold text-gray-800">Muat naik report card</p>
                    <select name="document_type" class="inp text-sm" required>
                        <option value="{{ \App\Enums\DocumentType::REPORT_CARD->value }}">Report Card / Kad Prestasi</option>
                        <option value="{{ \App\Enums\DocumentType::LAPORAN_AKTIVITI->value }}">Laporan Aktiviti</option>
                    </select>
                    <input type="file" name="file" class="inp text-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <textarea name="report_card_remarks" rows="2" class="inp text-sm" placeholder="Catatan (pilihan)">{{ old('report_card_remarks') }}</textarea>
                    <button type="submit" class="btn-primary text-sm">Muat Naik</button>
                </form>
            @else
                @if ($application->status !== \App\Enums\ApplicationStatus::APPROVED)
                    <p class="text-sm text-gray-400">Report card boleh dimuat naik selepas permohonan diluluskan.</p>
                @endif
            @endcan
        </div>

        {{-- Timeline URS M02 --}}
        <div x-show="tab === 'timeline'" x-cloak class="card p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-semibold text-gray-900">Garis masa hingga JKEW (UR-M02)</h3>
                @if ($timelineKpi ?? null)
                    <span @class([
                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                        'bg-green-100 text-green-800' => $timelineKpi['within_kpi'] === true,
                        'bg-red-100 text-red-800' => $timelineKpi['within_kpi'] === false,
                        'bg-gray-100 text-gray-600' => $timelineKpi['within_kpi'] === null,
                    ])>{{ $timelineKpi['label'] }}</span>
                @endif
            </div>
            <ol class="space-y-3">
                @foreach ($timelineStages ?? [] as $stage)
                    <li class="flex gap-3 text-sm">
                        <span @class([
                            'mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full',
                            'bg-green-500' => $stage['done'],
                            'bg-gray-300' => ! $stage['done'],
                        ])></span>
                        <div>
                            <p class="font-medium text-gray-900">{{ $stage['label'] }}</p>
                            <p class="text-xs text-gray-500">
                                @if ($stage['at'])
                                    {{ $stage['at']->format('d/m/Y H:i') }}
                                    @if ($stage['days_from_submit'] !== null)
                                        · +{{ $stage['days_from_submit'] }} hari dari hantar
                                    @endif
                                @else
                                    Belum direkod
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- Semakan --}}
        <div x-show="tab === 'semakan'" x-cloak class="card p-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Timeline Semakan</h3>
            @php
                $reviewsByType = $application->reviews->groupBy(fn ($r) => $r->review_type->value);
                $stages = [\App\Enums\ReviewType::SECRETARIAT, \App\Enums\ReviewType::FINANCE, \App\Enums\ReviewType::TECHNICAL];
            @endphp
            <div class="space-y-4">
                @foreach ($stages as $stage)
                    @php $last = optional($reviewsByType->get($stage->value))->last(); @endphp
                    <div class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $last ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        <div class="text-sm">
                            <p class="font-medium text-gray-900">{{ $stage->label() }}</p>
                            @if ($last)
                                <p class="text-gray-600">{{ $last->decision->label() }} · {{ $last->reviewer?->name }} · {{ $last->reviewed_at?->format('d/m/Y H:i') }}</p>
                                @if ($last->comments)<p class="text-xs text-gray-500">{{ $last->comments }}</p>@endif
                            @else
                                <p class="text-xs text-gray-400">Belum disemak</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Kelulusan --}}
        <div x-show="tab === 'kelulusan'" x-cloak class="card p-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Sejarah Kelulusan</h3>
            @forelse ($application->approvals as $a)
                <div class="flex items-start gap-3 border-b border-gray-100 py-2 last:border-0">
                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-navy-500"></span>
                    <div class="text-sm">
                        <p class="text-gray-900">{{ $a->approvalLevel?->name ?? 'Aras —' }} · <span class="font-medium">{{ $a->decision->label() }}</span></p>
                        <p class="text-xs text-gray-500">{{ $a->decided_at?->format('d/m/Y H:i') }} · {{ $a->approver?->name }} · Pusingan {{ $a->revision_number }}</p>
                        @if ($a->comments)<p class="text-xs text-gray-600">{{ $a->comments }}</p>@endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada rekod kelulusan.</p>
            @endforelse
        </div>

        {{-- Status / Aktiviti --}}
        <div x-show="tab === 'aktiviti'" x-cloak class="card p-6">
            <ol class="space-y-4">
                @forelse ($application->statusHistories as $h)
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-navy-500"></span>
                        <div class="text-sm">
                            <p class="text-gray-900">
                                {{ $h->from_status?->label() ?? 'Baharu' }} → <span class="font-medium">{{ $h->to_status->label() }}</span>
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $h->created_at?->format('d/m/Y H:i') }}
                                @if ($h->changedBy) · {{ $h->changedBy->name }} @endif
                                @if ($h->remarks) · {{ $h->remarks }} @endif
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">Tiada aktiviti.</li>
                @endforelse
            </ol>
        </div>
    </div>
@endsection
