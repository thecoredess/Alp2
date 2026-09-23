@extends('layouts.app')
@section('subheading', $application->application_number.' · '.($application->recipient_name ?? $application->purpose))

@php
    $required = $progress['required'];
    $approvedCount = $progress['approvedCount'];
    $nextLevel = $progress['nextLevel'];
    $isFinalNext = $nextLevel && ($approvedCount + 1) === $required->count();
    $isEndorseStep = $nextLevel && ! $isFinalNext;
    $isPerakuStep = auth()->user()->hasRole(\App\Enums\RoleName::PELULUS->value)
        && $isEndorseStep
        && $nextLevel?->required_role === \App\Enums\RoleName::PELULUS->value;
    $currentReviews = $application->reviews->where('revision_number', $application->revision_number);
    $currentApprovals = $application->approvals->where('revision_number', $application->revision_number);
    $processHistory = $currentReviews
        ->map(fn ($r) => [
            'label' => $r->review_type->label(),
            'at' => $r->reviewed_at ?? $r->created_at,
            'actor' => $r->reviewer?->name,
            'comments' => $r->comments,
            'badge' => $r->decision->label(),
            'badgeClasses' => $r->decision->badgeClasses(),
        ])
        ->merge($currentApprovals->map(fn ($a) => [
            'label' => $a->approvalLevel?->name ?? '—',
            'at' => $a->decided_at ?? $a->created_at,
            'actor' => $a->approver?->name,
            'comments' => $a->comments,
            'badge' => $a->displayDecisionLabel($application),
            'badgeClasses' => $a->displayDecisionBadgeClasses($application),
        ]))
        ->sortBy('at')
        ->values();
@endphp

@section('title', ($isPerakuStep ? 'Pengesyoran' : 'Kelulusan').' — '.$application->application_number)
@section('heading', $isPerakuStep ? 'Pengesyoran Permohonan' : 'Kelulusan Permohonan')

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

                @if ($processHistory->isNotEmpty())
                    <x-page-card title="Sejarah Semakan & Kelulusan" icon="clock">
                        <div class="space-y-3">
                            @foreach ($processHistory as $entry)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-medium text-gray-900">{{ $entry['label'] }}</span>
                                        <x-status-badge :label="$entry['badge']" :classes="$entry['badgeClasses']" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $entry['at']?->format('d/m/Y H:i') }} · {{ $entry['actor'] }}
                                    </p>
                                    @if ($entry['comments'])
                                        <p class="mt-2 text-gray-600">{{ $entry['comments'] }}</p>
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
                                <dt class="text-gray-600">Peruntukan Permohonan Dalam Proses (Belum diluluskan)</dt>
                                <dd class="font-semibold text-orange-700">−<x-money :value="$thisRequest" /></dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2">
                                <dt class="text-gray-600">Diluluskan</dt>
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
