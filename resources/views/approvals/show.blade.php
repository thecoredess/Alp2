@extends('layouts.app')
@section('title', 'Kelulusan — '.$application->application_number)
@section('heading', 'Kelulusan Permohonan')
@section('subheading', $application->application_number.' · '.($application->recipient_name ?? $application->purpose))

@php
    $required = $progress['required'];
    $approvedCount = $progress['approvedCount'];
    $nextLevel = $progress['nextLevel'];
    $isFinalNext = $nextLevel && ($approvedCount + 1) === $required->count();
    $currentReviews = $application->reviews->where('revision_number', $application->revision_number);
@endphp

@section('content')
    <x-page-shell>
        <div class="mb-5">
            <a href="{{ route('approvals.queue') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke giliran
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <x-page-card title="Borang Penyaluran Sumbangan" icon="clipboard">
                    <dl class="flex flex-col gap-4">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">a) Nama ALP</dt>
                            <dd class="mt-1.5 font-medium text-gray-900">
                                @can('applications.view_all')
                                    <a href="{{ route('applications.all', ['alp' => $application->alp_id, 'status' => \App\Services\Reports\ApplicationReportService::FILTER_APPROVED, 'tahun' => $application->financial_year_id]) }}"
                                       class="text-royal-700 hover:text-royal-800 hover:underline"
                                       title="Senarai permohonan diluluskan {{ $application->alp->ref_code }}">
                                        {{ $application->alp->ref_code }} — {{ $application->alp->name }}
                                    </a>
                                @else
                                    {{ $application->alp->ref_code }} — {{ $application->alp->name }}
                                @endcan
                            </dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">b) Nama Persatuan</dt>
                            <dd class="mt-1.5 font-medium text-gray-900">{{ $application->recipient_name ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">c) No. ROS</dt>
                            <dd class="mt-1.5 font-mono font-medium text-gray-900">{{ $application->recipient_ros_number ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">d) Tarikh Program</dt>
                            <dd class="mt-1.5 font-medium text-gray-900">{{ $application->program_date?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">e) Jenis/Kategori Program</dt>
                            <dd class="mt-1.5 font-medium text-gray-900">{{ $application->program_category?->label() ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-royal-100 bg-gradient-to-br from-royal-50 to-navy-50 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-royal-600">f) Jumlah Sumbangan</dt>
                            <dd class="mt-1 text-2xl font-bold text-navy-700"><x-money :value="$application->requested_amount" /></dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">g) Tujuan</dt>
                            <dd class="mt-1.5 whitespace-pre-line text-gray-900">{{ $application->purpose ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">h) No. Akaun</dt>
                            <dd class="mt-1.5 font-mono font-medium text-gray-900">{{ $application->recipient_bank_account ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">i) Alamat Persatuan</dt>
                            <dd class="mt-1.5 whitespace-pre-line text-gray-900">{{ $application->recipient_address ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-page-card>

                <x-page-card title="Lampiran" icon="paper-clip">
                    <x-document-preview-list :application="$application" :documents="$application->documents" />
                </x-page-card>

                @if ($currentReviews->isNotEmpty())
                    <x-page-card title="Semakan JP" icon="search">
                        <div class="space-y-3">
                            @foreach ($currentReviews as $r)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-medium text-gray-900">{{ $r->review_type->label() }}</span>
                                        <x-status-badge :label="$r->decision->label()" :classes="$r->decision->badgeClasses()" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $r->reviewed_at?->format('d/m/Y H:i') }} · {{ $r->reviewer?->name }}
                                    </p>
                                    @if ($r->comments)
                                        <p class="mt-2 text-gray-600">{{ $r->comments }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-page-card>
                @endif

                @if ($application->approvals->isNotEmpty())
                    <x-page-card title="Sejarah Kelulusan" icon="clock">
                        <div class="space-y-3">
                            @foreach ($application->approvals as $a)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-medium text-gray-900">{{ $a->approvalLevel?->name ?? '—' }}</span>
                                        <x-status-badge :label="$a->displayDecisionLabel($application)" :classes="$a->displayDecisionBadgeClasses($application)" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $a->decided_at?->format('d/m/Y H:i') ?? $a->created_at?->format('d/m/Y H:i') }} · {{ $a->approver?->name }}
                                    </p>
                                    @if ($a->comments)
                                        <p class="mt-2 text-gray-600">{{ $a->comments }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-page-card>
                @endif
            </div>

            <div class="space-y-4" x-data="{ act: 'approve' }">
                <x-page-card title="Kedudukan Kewangan" icon="wallet" description="{{ $application->alp->ref_code }} · {{ $application->financialYear?->year ?? '—' }}">
                    @include('reviews.partials.alp-budget-detail')
                </x-page-card>

                <x-page-card title="Aras Kelulusan" icon="scale" description="Aras {{ $approvedCount }} / {{ $required->count() }} selesai">
                    <ol class="space-y-2 text-sm">
                        @foreach ($required as $i => $lvl)
                            @php $done = $i < $approvedCount; $isNext = $i === $approvedCount; @endphp
                            <li @class([
                                'flex items-center gap-3 rounded-xl border px-3 py-2.5',
                                'border-green-200 bg-green-50/60' => $done,
                                'border-royal-200 bg-royal-50/60' => $isNext,
                                'border-gray-100 bg-gray-50/40' => ! $done && ! $isNext,
                            ])>
                                <span @class([
                                    'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                    'bg-green-100 text-green-700' => $done,
                                    'bg-royal-100 text-royal-700' => $isNext,
                                    'bg-gray-100 text-gray-400' => ! $done && ! $isNext,
                                ])>{{ $done ? '✓' : ($isNext ? '→' : $i + 1) }}</span>
                                <div class="min-w-0 flex-1">
                                    <p @class(['font-medium', 'text-gray-900' => $done || $isNext, 'text-gray-500' => ! $done && ! $isNext])>{{ $lvl->name }}</p>
                                    <p class="text-xs text-gray-400">{{ \App\Enums\RoleName::from($lvl->required_role)->label() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                    <p class="mt-3 text-xs text-gray-400">Komitmen bajet dicipta pada aras terakhir sahaja.</p>
                </x-page-card>

                @if ($isFinalNext)
                    <x-page-card title="Kesan Kelulusan" icon="chart" description="Simulasi aras terakhir">
                        <dl class="space-y-2 text-sm">
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                <dt class="text-gray-600">Jumlah Permohonan</dt>
                                <dd class="font-semibold text-navy-700"><x-money :value="$thisRequest" /></dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-green-50 px-3 py-2">
                                <dt class="text-gray-600">Baki Peruntukan Diluluskan (semasa)</dt>
                                <dd class="font-semibold text-green-700"><x-money :value="$ledgerAvailable" /></dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2">
                                <dt class="text-gray-600">Pending Request</dt>
                                <dd class="font-semibold text-orange-700">−<x-money :value="$thisRequest" /></dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2">
                                <dt class="text-gray-600">Committed</dt>
                                <dd class="font-semibold text-amber-700">+<x-money :value="$thisRequest" /></dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2.5">
                                <dt class="font-medium text-gray-600">Baki Peruntukan Diluluskan Selepas</dt>
                                <dd class="font-semibold {{ $afterCommitAvailable->isNegative() ? 'text-danger' : 'text-green-600' }}">
                                    <x-money :value="$afterCommitAvailable" />
                                </dd>
                            </div>
                        </dl>
                    </x-page-card>
                @endif

                @php
                    $isEndorseStep = $nextLevel && ! $isFinalNext;
                @endphp

                <x-page-card
                    title="{{ $isEndorseStep ? 'Pengesyoran' : 'Keputusan Kelulusan' }}"
                    description="{{ $nextLevel?->name ?? 'Tiada aras seterusnya' }}"
                >
                    <div class="mb-4 flex gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1 text-sm">
                        <button type="button" @click="act='approve'" :class="act==='approve' ? 'bg-navy-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="flex-1 rounded-lg px-2 py-2 font-medium transition">
                            {{ $isEndorseStep ? 'Syor' : 'Luluskan' }}
                        </button>
                        <button type="button" @click="act='reject'" :class="act==='reject' ? 'bg-danger text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="flex-1 rounded-lg px-2 py-2 font-medium transition">Tolak</button>
                    </div>

                    <form x-show="act==='approve'" method="POST" action="{{ route('approvals.approve', $application) }}"
                          onsubmit="return confirm('{{ $isEndorseStep ? 'Sahkan syor aras ini kepada PEPU?' : 'Sahkan kelulusan aras ini?' }}')" class="space-y-4">
                        @csrf
                        <x-field label="Ulasan" name="comments" hint="Pilihan.">
                            <textarea name="comments" rows="4" class="inp" placeholder="{{ $isEndorseStep ? 'Ulasan syor' : 'Ulasan kelulusan' }}">{{ old('comments') }}</textarea>
                        </x-field>
                        <button type="submit" class="btn-navy w-full">
                            @if ($isEndorseStep)
                                Syor
                            @elseif ($isFinalNext)
                                Lulus
                            @else
                                Luluskan Aras Ini
                            @endif
                        </button>
                    </form>

                    <form x-show="act==='reject'" x-cloak method="POST" action="{{ route('approvals.reject', $application) }}"
                          onsubmit="return confirm('Sahkan penolakan permohonan ini?')" class="space-y-4">
                        @csrf
                        <x-field label="Sebab Penolakan" name="comments" :required="true">
                            <textarea name="comments" rows="4" required class="inp" placeholder="Nyatakan sebab penolakan">{{ old('comments') }}</textarea>
                        </x-field>
                        <button type="submit" class="btn-danger w-full">Tolak Permohonan</button>
                    </form>
                </x-page-card>
            </div>
        </div>
    </x-page-shell>
@endsection
