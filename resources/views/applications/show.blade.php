@extends('layouts.app')
@section('title', $application->application_number)
@section('heading', $application->purpose)
@section('subheading', $application->application_number.' · Sumbangan ALP')

@php
    $user = auth()->user();
    $isAlpView = $user->alp_id === $application->alp_id && ! $user->can('applications.view_all');
    $showReportTab = $application->status === \App\Enums\ApplicationStatus::APPROVED
        || ($hasReportCard ?? false)
        || ($reportCardOverdue ?? false);
    $tabs = ['ringkasan' => 'Ringkasan', 'dokumen' => 'Lampiran'];
    if (! $isAlpView || $showReportTab) {
        $tabs['report'] = 'Laporan Aktiviti';
    }
    $tabs['perjalanan'] = 'Status';

    // ?tab=report membolehkan notifikasi mendarat terus pada tugasan berkenaan.
    $initialTab = array_key_exists(request()->query('tab'), $tabs)
        ? request()->query('tab')
        : 'ringkasan';

    $attachmentDocs = $application->documents->reject(fn ($d) => in_array($d->document_type, [
        \App\Enums\DocumentType::REPORT_CARD,
        \App\Enums\DocumentType::LAPORAN_AKTIVITI,
        \App\Enums\DocumentType::SEMAKAN_SILANG_JKEW,
    ], true));
    $crosscheckDocument = $application->documents
        ->first(fn ($d) => $d->document_type === \App\Enums\DocumentType::SEMAKAN_SILANG_JKEW);
    $attachmentCount = $attachmentDocs->count();
    $timelineDoneCount = collect($timelineStages ?? [])->filter(fn ($s) => ($s['done'] ?? false) || ($s['skipped'] ?? false))->count();
    $timelineTotalCount = count($timelineStages ?? []);
    $timelineProgress = $timelineTotalCount > 0 ? round(($timelineDoneCount / $timelineTotalCount) * 100) : 0;
    $jpIncomplete = $jpIncomplete ?? [];
    $jpCard = fn (string ...$keys) => \App\Support\JpReviewChecklist::cardClasses($jpIncomplete, ...$keys);
@endphp

@section('content')
    <div class="page-shell">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('applications.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke senarai
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <x-status-badge :label="$application->status->label()" :classes="$application->status->badgeClasses()" class="text-sm px-3 py-1" />
            @if ($application->isDraft() && $user->can('update', $application))
                <a href="{{ route('applications.wizard.maklumat', $application) }}" class="btn-primary">Sambung Draf</a>
            @elseif ($application->status === \App\Enums\ApplicationStatus::REVISION_REQUIRED && $user->can('update', $application))
                <a href="{{ route('applications.wizard.maklumat', $application) }}" class="btn-primary">Buat Pembetulan</a>
            @elseif ($application->status === \App\Enums\ApplicationStatus::APPROVED)
                <a href="{{ route('applications.borang', $application) }}" target="_blank" class="btn-white">Cetak Borang</a>
                <a href="{{ route('applications.letter.pdf', $application) }}" target="_blank" class="btn-navy">Surat Pemakluman (Lulus)</a>
            @elseif ($application->status === \App\Enums\ApplicationStatus::REJECTED)
                <a href="{{ route('applications.letter.pdf', $application) }}" target="_blank" class="btn-navy">Surat Pemakluman (Tidak Lulus)</a>
            @endif
        </div>
    </div>

    @if ($activeRevision && $user->can('update', $application))
        <div class="mb-6 flex gap-4 rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 p-4 shadow-sm">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </span>
            <div class="text-sm text-orange-900">
                <p class="font-semibold">Pembetulan Diperlukan</p>
                @if ($activeRevision->reason)<p class="mt-1">{{ $activeRevision->reason }}</p>@endif
                <p class="mt-1 text-orange-700">Sila betulkan melalui butang <strong>Buat Pembetulan</strong>, kemudian hantar semula.</p>
            </div>
        </div>
    @endif

    @include('applications.partials.jp-incomplete-banner')

    <div x-data="{ tab: '{{ $initialTab }}' }">
        @include('applications.partials.timeline-summary', ['isAlpView' => $isAlpView])

        <nav class="mb-6 flex gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1" aria-label="Tab permohonan">
            @foreach ($tabs as $key => $label)
                <button type="button"
                        @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'bg-navy-700 text-white shadow-sm'
                            : 'text-gray-600 hover:bg-white/60 hover:text-gray-900'"
                        class="flex min-w-0 flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition sm:px-5">
                    @if ($key === 'ringkasan')
                        <x-icon name="clipboard" class="h-4 w-4 shrink-0" />
                    @elseif ($key === 'dokumen')
                        <x-icon name="paper-clip" class="h-4 w-4 shrink-0" />
                    @elseif ($key === 'report')
                        <x-icon name="document" class="h-4 w-4 shrink-0" />
                    @else
                        <x-icon name="chart" class="h-4 w-4 shrink-0" />
                    @endif
                    <span class="truncate">{{ $label }}</span>
                </button>
            @endforeach
        </nav>

        {{-- Ringkasan --}}
        <div x-show="tab === 'ringkasan'" x-cloak x-transition.opacity.duration.200ms class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="card overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-royal-50 text-royal-600">
                                <x-icon name="clipboard" class="h-4 w-4" />
                            </span>
                            Borang Penyaluran Sumbangan
                        </h3>
                    </div>
                    <div class="p-6">
                        <dl class="flex flex-col gap-4">
                            @unless ($isAlpView)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">ALP</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ $application->alp->ref_code }} — {{ $application->alp->name }}</dd>
                                </div>
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tahun Kewangan</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ $application->financialYear->year }}</dd>
                                </div>
                            @endunless

                            <div class="rounded-xl border border-gray-100 p-4">
                                <dt class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-400">
                                    <x-icon name="user" class="h-3.5 w-3.5" /> a) Nama ALP
                                </dt>
                                <dd class="mt-1.5 font-medium text-gray-900">{{ $application->alp->name }}</dd>
                            </div>

                            <div class="{{ $jpCard('recipient') }}">
                                <dt class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'recipient') ? 'text-red-600' : 'text-gray-400' }}">
                                    <x-icon name="building" class="h-3.5 w-3.5" /> b) Nama Persatuan
                                    @if (\App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'recipient'))
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">Tidak lengkap</span>
                                    @endif
                                </dt>
                                <dd class="mt-1.5 font-medium text-gray-900">{{ $application->recipient_name ?? '—' }}</dd>
                            </div>

                            <div class="{{ $jpCard('recipient') }}">
                                <dt class="text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'recipient') ? 'text-red-600' : 'text-gray-400' }}">c) No. ROS</dt>
                                <dd class="mt-1.5 font-mono font-medium text-gray-900">{{ $application->recipient_ros_number ?? '—' }}</dd>
                            </div>

                            <div class="{{ $jpCard('recipient') }}">
                                <dt class="text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'recipient') ? 'text-red-600' : 'text-gray-400' }}">d) Tarikh Program</dt>
                                <dd class="mt-1.5 font-medium text-gray-900">{{ $application->program_date?->format('d/m/Y') ?? '—' }}</dd>
                            </div>

                            <div class="{{ $jpCard('recipient') }}">
                                <dt class="text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'recipient') ? 'text-red-600' : 'text-gray-400' }}">e) Jenis/Kategori Program</dt>
                                <dd class="mt-1.5 font-medium text-gray-900">{{ $application->program_category?->label() ?? '—' }}</dd>
                            </div>

                            <div class="{{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'bajet', 'program_syarat') ? 'rounded-xl border-2 border-red-400 bg-red-50 p-4 ring-1 ring-red-200' : 'rounded-xl border border-royal-100 bg-gradient-to-br from-royal-50 to-navy-50 p-4' }}">
                                <dt class="text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'bajet', 'program_syarat') ? 'text-red-600' : 'text-royal-600' }}">
                                    f) Jumlah Sumbangan
                                    @if (\App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'bajet', 'program_syarat'))
                                        <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">Tidak lengkap</span>
                                    @endif
                                </dt>
                                <dd class="mt-1 text-2xl font-bold {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'bajet', 'program_syarat') ? 'text-red-800' : 'text-navy-700' }}"><x-money :value="$application->requested_amount" /></dd>
                            </div>

                            <div class="{{ $jpCard('program_syarat') }}">
                                <dt class="text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'program_syarat') ? 'text-red-600' : 'text-gray-400' }}">
                                    g) Tujuan
                                    @if (\App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'program_syarat'))
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">Tidak lengkap</span>
                                    @endif
                                </dt>
                                <dd class="mt-1.5 whitespace-pre-line text-gray-900">{{ $application->purpose ?? '—' }}</dd>
                            </div>

                            <div class="{{ $jpCard('recipient') }}">
                                <dt class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'recipient') ? 'text-red-600' : 'text-gray-400' }}">
                                    <x-icon name="bank" class="h-3.5 w-3.5" /> h) No. Akaun
                                </dt>
                                <dd class="mt-1.5 font-mono font-medium text-gray-900">{{ $application->recipient_bank_account ?? '—' }}</dd>
                            </div>

                            <div class="{{ $jpCard('recipient') }}">
                                <dt class="text-xs font-medium uppercase tracking-wide {{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'recipient') ? 'text-red-600' : 'text-gray-400' }}">i) Alamat Persatuan</dt>
                                <dd class="mt-1.5 whitespace-pre-line text-gray-900">{{ $application->recipient_address ?? '—' }}</dd>
                            </div>

                            @if (\App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'baki', 'polisi'))
                                @foreach (['baki', 'polisi'] as $jpKey)
                                    @if (isset($jpIncomplete[$jpKey]))
                                        <div class="rounded-xl border-2 border-red-400 bg-red-50 p-4 ring-1 ring-red-200">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-red-600">
                                                Semakan JP
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">Tidak lengkap</span>
                                            </dt>
                                            <dd class="mt-1.5 text-sm font-medium text-red-900">{{ $jpIncomplete[$jpKey] }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                                <dt class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-400">
                                    <x-icon name="clock" class="h-3.5 w-3.5" /> Dihantar kepada JP
                                </dt>
                                <dd class="mt-1.5 font-medium text-gray-900">{{ $application->submitted_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                @if ($isAlpView)
                    <div class="card overflow-hidden">
                        <div class="bg-gradient-to-br from-navy-700 to-royal-600 px-5 py-4 text-white">
                            <p class="text-xs font-medium uppercase tracking-wide text-navy-200">Status Semasa</p>
                            <p class="mt-1 text-lg font-bold">{{ $application->status->label() }}</p>
                        </div>
                        <div class="p-5">
                            <p class="text-sm text-gray-600">Semak tab <button type="button" @click="tab = 'perjalanan'" class="font-semibold text-royal-600 hover:text-royal-700">Status</button> untuk lihat kemajuan semakan dan kelulusan.</p>
                            @if ($timelineTotalCount > 0)
                                <div class="mt-4">
                                    <div class="mb-1.5 flex justify-between text-xs font-medium text-gray-500">
                                        <span>Kemajuan</span>
                                        <span>{{ $timelineProgress }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-royal-500 transition-all" style="width: {{ $timelineProgress }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card p-5">
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-50 text-green-600">
                                <x-icon name="wallet" class="h-4 w-4" />
                            </span>
                            Kedudukan Kewangan
                        </h3>
                        <dl class="space-y-3">
                            <div class="flex items-center justify-between rounded-lg bg-green-50 px-3 py-2.5">
                                <dt class="text-sm text-gray-500">Baki Peruntukan Diluluskan</dt>
                                <dd class="font-semibold text-green-700"><x-money :value="$ledgerAvailable" /></dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2.5">
                                <dt class="text-sm text-gray-500">Jumlah Dipohon</dt>
                                <dd class="font-semibold text-navy-700"><x-money :value="$application->requested_amount" /></dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2.5">
                                <dt class="text-sm text-gray-500">Pending Request</dt>
                                <dd class="font-semibold text-orange-700"><x-money :value="$pending" /></dd>
                            </div>
                        </dl>
                    </div>

                    @if ($application->commitmentTransaction)
                        <div class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 p-4">
                            <p class="flex items-center gap-2 text-sm font-semibold text-green-800">
                                <x-icon name="check" class="h-4 w-4" /> Komitmen Bajet
                            </p>
                            <p class="mt-2 text-sm text-green-900">
                                <x-money :value="$application->commitmentTransaction->amount" />
                                <span class="text-green-600">·</span>
                                {{ $application->commitmentTransaction->reference_no }}
                            </p>
                        </div>
                    @endif
                @endif

                @if ($application->status === \App\Enums\ApplicationStatus::APPROVED && $application->payment_status)
                    <div class="card overflow-hidden">
                        <div class="border-b border-royal-100 bg-royal-50 px-5 py-3">
                            <h3 class="text-sm font-semibold text-royal-800">Status Pembayaran</h3>
                        </div>
                        <dl class="space-y-3 p-5 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Status</dt>
                                <dd>
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $application->payment_status->badgeClasses() }}">
                                        {{ $application->payment_status->label() }}
                                    </span>
                                </dd>
                            </div>
                            @if ($application->payment_supplier_no)
                                <div class="flex justify-between"><dt class="text-gray-500">No. Pembekal</dt><dd class="font-mono font-medium">{{ $application->payment_supplier_no }}</dd></div>
                            @endif
                            @if ($application->payment_voucher_no)
                                <div class="flex justify-between"><dt class="text-gray-500">No. Baucar</dt><dd class="font-mono font-medium">{{ $application->payment_voucher_no }}</dd></div>
                            @endif
                            @if ($application->payment_voucher_date)
                                <div class="flex justify-between"><dt class="text-gray-500">Tarikh Baucar</dt><dd class="font-medium">{{ $application->payment_voucher_date->format('d/m/Y') }}</dd></div>
                            @endif
                            @if ($application->payment_remarks)
                                <div>
                                    <dt class="text-gray-500">Catatan</dt>
                                    <dd class="mt-1 whitespace-pre-line text-gray-900">{{ $application->payment_remarks }}</dd>
                                </div>
                            @endif
                            @if ($application->paid_at)
                                <div class="flex justify-between"><dt class="text-gray-500">Tarikh Bayar</dt><dd class="font-medium">{{ $application->paid_at->format('d/m/Y') }}</dd></div>
                            @endif
                        </dl>
                    </div>

                    @can('payments.manage')
                        @php
                            $canRegisterVoucher = $application->payment_status === \App\Enums\ApplicationPaymentStatus::PENDING_PAYMENT;
                            $canEditVoucher = $application->hasVoucherPrepared() && auth()->user()->can('payments.voucher_edit');
                        @endphp
                        @if ($canRegisterVoucher || $canEditVoucher)
                        <form method="POST" action="{{ route('payments.update', $application) }}" class="card space-y-3 p-4">
                            @csrf @method('PUT')
                            <p class="text-xs font-semibold text-gray-800">{{ $canEditVoucher ? 'Kemaskini baucar (Super Admin)' : 'Daftar baucar' }}</p>
                            <input type="hidden" name="payment_status" value="{{ \App\Enums\ApplicationPaymentStatus::VOUCHER_PREPARED->value }}">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                                <p class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-800">Baucar Disedia</p>
                            </div>
                            <div>
                                <label for="payment_supplier_no" class="mb-1 block text-xs font-medium text-gray-600">No. Pembekal</label>
                                <input type="text" id="payment_supplier_no" name="payment_supplier_no" class="inp w-full text-xs" value="{{ old('payment_supplier_no', $application->payment_supplier_no) }}" required>
                                @error('payment_supplier_no')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="payment_voucher_no" class="mb-1 block text-xs font-medium text-gray-600">No. Baucar</label>
                                <input type="text" id="payment_voucher_no" name="payment_voucher_no" class="inp w-full text-xs" value="{{ old('payment_voucher_no', $application->payment_voucher_no) }}" required>
                                @error('payment_voucher_no')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="payment_voucher_date" class="mb-1 block text-xs font-medium text-gray-600">Tarikh Baucar</label>
                                <input type="date" id="payment_voucher_date" name="payment_voucher_date" class="inp w-full text-xs" value="{{ old('payment_voucher_date', $application->payment_voucher_date?->format('Y-m-d')) }}" required>
                                @error('payment_voucher_date')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="payment_remarks" class="mb-1 block text-xs font-medium text-gray-600">Catatan</label>
                                <textarea id="payment_remarks" name="payment_remarks" rows="2" class="inp w-full text-xs" placeholder="Catatan (pilihan)">{{ old('payment_remarks', $application->payment_remarks) }}</textarea>
                                @error('payment_remarks')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn-primary w-full !py-1.5 text-xs">{{ $canEditVoucher ? 'Simpan Perubahan' : 'Simpan Baucar' }}</button>
                        </form>
                        @elseif ($application->hasVoucherPrepared())
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-600">
                            Baucar telah direkod. Maklumat di atas adalah baca sahaja. Hubungi Super Admin jika pembetulan diperlukan.
                        </div>
                        @endif
                    @endcan
                @endif
            </div>
        </div>

        {{-- Lampiran --}}
        <div x-show="tab === 'dokumen'" x-cloak x-transition.opacity.duration.200ms>
            @if (\App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'))
                <div class="mb-4 rounded-xl border-2 border-red-400 bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
                    <strong>Lampiran ditanda tidak lengkap</strong> oleh Admin JP. Sila semak dan kemaskini dokumen melalui <strong>Buat Pembetulan</strong>.
                </div>
            @endif
            @if ($attachmentDocs->isNotEmpty())
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">{{ $attachmentCount }} fail dimuat naik</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($attachmentDocs as $doc)
                        <div @class([
                            'group card flex items-center gap-4 p-5 transition',
                            'border-2 border-red-400 bg-red-50 ring-1 ring-red-200' => \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
                            'hover:border-royal-200 hover:shadow-md' => ! \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
                        ])>
                            <span @class([
                                'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition',
                                'bg-red-100 text-red-600' => \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
                                'bg-royal-50 text-royal-600 group-hover:bg-royal-100' => ! \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
                            ])>
                                <x-icon name="document" class="h-6 w-6" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900">{{ $doc->document_type->simpleLabel() }}</p>
                                <p class="truncate text-sm text-gray-500" title="{{ $doc->original_filename }}">{{ $doc->original_filename }}</p>
                            </div>
                            <a href="{{ route('applications.documents.view', [$application, $doc]) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn-white shrink-0 !px-3 !py-2 text-xs">
                                Lihat
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div @class([
                    'card p-12 text-center',
                    'border-2 border-red-400 bg-red-50 ring-1 ring-red-200' => \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
                ])>
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                        <x-icon name="paper-clip" class="h-8 w-8" />
                    </span>
                    <p class="mt-4 font-medium text-gray-900">Tiada lampiran</p>
                    <p class="mt-1 text-sm text-gray-500">Dokumen sokongan belum dimuat naik.</p>
                </div>
            @endif

            @unless ($isAlpView)
                <div class="mt-6">
                    @include('reviews.partials.crosscheck-card', [
                        'application' => $application,
                        'crosscheckDocument' => $crosscheckDocument,
                        'uploadAction' => route('applications.crosscheck.store', $application),
                    ])
                </div>
            @endunless
        </div>

        {{-- Laporan Aktiviti --}}
        @if (isset($tabs['report']))
            <div x-show="tab === 'report'" x-cloak x-transition.opacity.duration.200ms>
                <div class="card overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Laporan Aktiviti</h3>
                                @php
                                    $reportTemplateYear = (int) ($application->financialYear?->year ?? now()->year);
                                @endphp
                                <p class="mt-1 text-sm text-gray-500">Muat naik dalam 1 bulan selepas program dijalankan.</p>
                                <a href="{{ route('applications.report-template.pdf', $application) }}"
                                   class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-royal-700 underline hover:text-royal-800">
                                    <x-icon name="download" class="h-4 w-4" />
                                    Format Laporan Program
                                </a>
                            </div>
                            @if ($application->status === \App\Enums\ApplicationStatus::APPROVED)
                                @if ($hasReportCard ?? false)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Disahkan ✓</span>
                                @elseif ($application->report_card_status === \App\Enums\ReportCardStatus::RETURNED)
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">Dikembalikan — sila muat naik semula</span>
                                @elseif ($application->report_card_status instanceof \App\Enums\ReportCardStatus)
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $application->report_card_status->badgeClasses() }}">
                                        {{ $application->report_card_status->label() }}
                                    </span>
                                @elseif ($reportCardOverdue ?? false)
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Tertunggak</span>
                                @elseif ($reportCardDue ?? null)
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Akhir: {{ $reportCardDue->format('d/m/Y') }}</span>
                                @elseif (! $application->hasVoucherPrepared())
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Menunggu baucar</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="space-y-4 p-6">
                        @php
                            $reportDocs = $application->documents->filter(fn ($d) => in_array($d->document_type, [
                                \App\Enums\DocumentType::REPORT_CARD,
                                \App\Enums\DocumentType::LAPORAN_AKTIVITI,
                            ], true));
                        @endphp
                        @php
                            $reportCardDraft = $reportCardDraft ?? null;
                        @endphp

                        @if ($reportCardDraft)
                            <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-5">
                                <p class="text-sm font-semibold text-amber-900">Draf laporan aktiviti</p>
                                <p class="mt-1 text-xs text-amber-800">Semak fail di bawah. Hantar ke Admin JP hanya selepas anda pasti fail betul.</p>
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-100 bg-white px-4 py-3 text-sm">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $reportCardDraft->original_filename }}</p>
                                        <p class="text-xs text-gray-500">{{ number_format($reportCardDraft->file_size / 1024, 1) }} KB</p>
                                    </div>
                                    <a href="{{ route('applications.documents.view', [$application, $reportCardDraft]) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn-white text-xs !px-3 !py-1.5">
                                        <x-icon name="eye" class="h-4 w-4" />
                                        Preview
                                    </a>
                                </div>

                                @can('submitReportCard', $application)
                                    <form method="POST" action="{{ route('applications.report-card.submit', $application) }}" class="mt-4 space-y-3">
                                        @csrf
                                        <label class="flex items-start gap-2 text-sm text-gray-700">
                                            <input type="checkbox" name="confirmed" value="1" required class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500">
                                            <span>Saya sahkan laporan aktiviti ini lengkap dan betul untuk dihantar ke Admin JP.</span>
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" class="btn-primary text-sm">Hantar ke Admin JP</button>
                                        </div>
                                    </form>
                                @endcan

                                @can('uploadReportCard', $application)
                                    <form method="POST" action="{{ route('applications.report-card.store', $application) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-2 border-t border-amber-100 pt-3">
                                        @csrf
                                        <input type="hidden" name="document_type" value="{{ \App\Enums\DocumentType::LAPORAN_AKTIVITI->value }}">
                                        <label class="btn-white cursor-pointer text-xs">
                                            Tukar fail
                                            <input type="file" name="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="if(this.files.length) this.form.requestSubmit()">
                                        </label>
                                    </form>
                                @endcan

                                @can('discardReportCardDraft', $application)
                                    <form method="POST" action="{{ route('applications.report-card.draft.destroy', $application) }}" class="mt-2" onsubmit="return confirm('Batalkan draf laporan aktiviti?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Batal draf</button>
                                    </form>
                                @endcan
                            </div>
                        @else
                            @foreach ($reportDocs as $doc)
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                                    <span class="font-medium text-gray-900">{{ $doc->document_type->label() }}</span>
                                    <a href="{{ route('applications.documents.view', [$application, $doc]) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-royal-600 hover:text-royal-700">
                                        Lihat
                                    </a>
                                </div>
                            @endforeach
                        @endif

                        @can('uploadReportCard', $application)
                            @if (! $reportCardDraft)
                                <form method="POST" action="{{ route('applications.report-card.store', $application) }}" enctype="multipart/form-data" class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-6 text-center">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ \App\Enums\DocumentType::LAPORAN_AKTIVITI->value }}">
                                    <p class="mb-1 text-sm font-medium text-gray-700">Muat naik Laporan Aktiviti</p>
                                    <p class="mb-4 text-xs text-gray-500">Fail akan disimpan sebagai draf untuk preview sebelum dihantar.</p>
                                    <label class="btn-primary inline-flex cursor-pointer items-center text-sm !px-5 !py-2.5">
                                        <x-icon name="paper-clip" class="h-4 w-4" />
                                        Pilih Fail
                                        <input type="file" name="file" class="sr-only" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    </label>
                                    <button type="submit" class="btn-white mt-3 text-sm">Simpan draf</button>
                                </form>
                            @endif
                        @elseif ($application->report_card_status === \App\Enums\ReportCardStatus::AWAITING_ADMIN_JP || $application->report_card_status === \App\Enums\ReportCardStatus::AWAITING_PEGAWAI_JP)
                            <p class="text-center text-sm text-gray-500">Laporan aktiviti dalam semakan JP. Anda akan dimaklumkan selepas pengesahan.</p>
                        @elseif ($application->status !== \App\Enums\ApplicationStatus::APPROVED)
                            <p class="text-center text-sm text-gray-400">Laporan aktiviti boleh dimuat naik selepas permohonan diluluskan.</p>
                        @elseif (! $application->hasVoucherPrepared())
                            <p class="text-center text-sm text-gray-400">Menunggu Kewangan JP menyediakan baucar. Tempoh 1 bulan bermula selepas program dijalankan.</p>
                        @endcan
                    </div>
                </div>
            </div>
        @endif

        {{-- Status --}}
        <div x-show="tab === 'perjalanan'" x-cloak x-transition.opacity.duration.200ms>
            @include('applications.partials.perjalanan', ['isAlpView' => $isAlpView])
        </div>
    </div>
    </div>
@endsection
