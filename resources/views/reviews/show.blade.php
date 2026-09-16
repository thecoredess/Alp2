@extends('layouts.app')
@section('title', 'Semakan — '.$application->application_number)
@section('heading', $reviewType->label())
@section('subheading', $application->application_number.' · '.($application->recipient_name ?? $application->purpose))

@php
    $ledgerAvailable = $summary->available();
    $projected = $ledgerAvailable->minus($otherPending)->minus($thisRequest);
@endphp

@section('content')
    <x-page-shell>
        <div class="mb-5">
            <a href="{{ route('reviews.'.$reviewType->value) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke giliran
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <x-page-card title="Borang Penyaluran Sumbangan" icon="clipboard">
                    <div @if ($canEditBorang ?? false) x-data="{ editing: {{ ($errors->any() || request()->boolean('edit')) ? 'true' : 'false' }} }" @endif>
                    @if ($canEditBorang ?? false)
                        <div class="mb-4 flex justify-end" x-show="!editing">
                            <button type="button" class="btn-white text-sm" @click="editing = true">Kemaskini</button>
                        </div>
                    @endif
                    <div @if ($canEditBorang ?? false) x-show="!editing" x-cloak @endif>
                    <dl class="flex flex-col gap-4">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">a) Nama ALP</dt>
                            <dd class="mt-1.5 font-medium text-gray-900">
                                @can('applications.view_all')
                                    <a href="{{ route('applications.all', ['alp' => $application->alp_id, 'status' => \App\Enums\ApplicationStatus::APPROVED->value, 'tahun' => $application->financial_year_id]) }}"
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
                    </div>
                    @if ($canEditBorang ?? false)
                        <div x-show="editing" x-cloak>
                            @include('reviews.partials.borang-penyaluran-form')
                        </div>
                    @endif
                    </div>
                </x-page-card>

                <x-page-card title="Lampiran" icon="paper-clip">
                    <x-document-preview-list :application="$application" :documents="$attachmentDocuments ?? $application->documents" />
                </x-page-card>

                @include('reviews.partials.crosscheck-card', [
                    'application' => $application,
                    'crosscheckDocument' => $crosscheckDocument ?? null,
                    'uploadAction' => route('applications.crosscheck.store', $application),
                ])

                @if ($application->reviews->isNotEmpty())
                    <x-page-card title="Sejarah Semakan" icon="clock">
                        <div class="space-y-3">
                            @foreach ($application->reviews as $r)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-medium text-gray-900">{{ $r->review_type->label() }}</span>
                                        <x-status-badge :label="$r->decision->label()" :classes="$r->decision->badgeClasses()" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $r->reviewed_at?->format('d/m/Y H:i') }} · {{ $r->reviewer?->name }} · Pusingan {{ $r->revision_number }}
                                    </p>
                                    @if ($r->comments)
                                        <p class="mt-2 text-gray-600">{{ $r->comments }}</p>
                                    @endif
                                    @if (is_array($r->checklist) && $r->checklist !== [])
                                        <ul class="mt-2 space-y-1 text-xs text-gray-500">
                                            @foreach (\App\Support\JpReviewChecklist::items() as $ck => $clabel)
                                                @if (isset($r->checklist[$ck]))
                                                    <li>{{ $r->checklist[$ck] === 'lengkap' ? '✓' : '✗' }} {{ $clabel }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-page-card>
                @endif
            </div>

            <div class="space-y-4">
                <x-page-card title="Kedudukan Kewangan" icon="wallet" description="{{ $application->alp->ref_code }} · {{ $application->financialYear?->year ?? '—' }}">
                    @include('reviews.partials.alp-budget-detail')
                </x-page-card>

                <x-page-card
                    title="{{ $fullJpDecision ? 'Keputusan Semakan Admin JP' : 'Perakuan Pegawai JP' }}"
                    description="{{ $fullJpDecision ? 'UR-M04-001 · Keputusan penuh, kemudian Pegawai JP' : 'Syor kepada Pengarah JP (Peraku)' }}"
                >
                    <div x-data="{ act: '{{ old('decision', $fullJpDecision ? '' : 'recommend') }}' }" class="space-y-5">
                        <form method="POST" action="{{ route('reviews.store', [$application, $reviewType->value]) }}" class="space-y-5">
                            @csrf

                            @if ($fullJpDecision)
                                <div>
                                    <p class="mb-3 text-sm font-medium text-gray-800">Senarai Semak JP <span class="text-danger">*</span></p>
                                    @error('checklist')
                                        <p class="mb-2 text-xs text-danger">{{ $message }}</p>
                                    @enderror
                                    <ul class="space-y-3">
                                        @foreach ($checklistItems as $key => $label)
                                            @php
                                                $hint = $checklistHints[$key] ?? null;
                                                $oldVal = old('checklist.'.$key, ($hint['ok'] ?? false) ? 'lengkap' : '');
                                            @endphp
                                            <li class="rounded-xl border border-gray-100 p-4">
                                                <p class="text-sm font-medium text-gray-900">{{ $label }}</p>
                                                @if ($hint)
                                                    <p class="mt-1 text-xs {{ $hint['ok'] ? 'text-green-700' : 'text-amber-700' }}">
                                                        Petunjuk sistem: {{ $hint['note'] }}
                                                    </p>
                                                @endif
                                                <div class="mt-3 flex gap-4 text-sm">
                                                    <label class="inline-flex items-center gap-1.5">
                                                        <input type="radio" name="checklist[{{ $key }}]" value="lengkap" class="text-royal-600" @checked($oldVal === 'lengkap') required>
                                                        Lengkap
                                                    </label>
                                                    <label class="inline-flex items-center gap-1.5">
                                                        <input type="radio" name="checklist[{{ $key }}]" value="tidak_lengkap" class="text-royal-600" @checked($oldVal === 'tidak_lengkap')>
                                                        Tidak lengkap
                                                    </label>
                                                </div>
                                                @error('checklist.'.$key)
                                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                @enderror
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <x-field label="Keputusan" name="decision" :required="true">
                                    <select name="decision" class="inp" required x-model="act">
                                        <option value="">— Pilih —</option>
                                        @foreach (\App\Enums\ReviewDecision::cases() as $d)
                                            <option value="{{ $d->value }}" @selected(old('decision') === $d->value)>{{ $d->label() }}</option>
                                        @endforeach
                                    </select>
                                </x-field>

                                <x-field label="Ulasan / Catatan" name="comments" hint="Wajib jika bukan 'Disyorkan'.">
                                    <textarea name="comments" rows="4" class="inp">{{ old('comments') }}</textarea>
                                </x-field>

                                <button type="submit" class="btn-navy w-full">Hantar Keputusan</button>
                            @else
                                <input type="hidden" name="decision" :value="act">

                                <div class="flex gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1 text-sm">
                                    <button type="button" @click="act='recommend'" :class="act==='recommend' ? 'bg-navy-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="flex-1 rounded-lg px-2 py-2 font-medium transition">Syor kepada Pengarah JP</button>
                                    <button type="button" @click="act='return_for_revision'" :class="act==='return_for_revision' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="flex-1 rounded-lg px-2 py-2 font-medium transition">Kembalikan</button>
                                </div>

                                <x-field label="Ulasan" name="comments" hint="Catatan perakuan untuk Pengarah JP. Wajib jika kembalikan.">
                                    <textarea name="comments" rows="4" class="inp" placeholder="Ulasan / catatan syor kepada Pengarah JP">{{ old('comments') }}</textarea>
                                </x-field>

                                <button type="submit" class="btn-navy w-full" x-text="act === 'return_for_revision' ? 'Kembalikan Untuk Pembetulan' : 'Syor kepada Pengarah JP'"></button>
                            @endif
                        </form>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">
                        @if ($fullJpDecision)
                            Selepas hantar, permohonan masuk giliran Pegawai JP untuk pengesyoran kepada Pengarah JP. Keputusan Admin JP bersifat nasihat — tidak mencipta komitmen bajet.
                        @else
                            Pegawai JP menghantar perakuan dan ulasan kepada Pengarah JP (Peraku). Tiada penandaan lengkap / tidak lengkap pada peringkat ini.
                        @endif
                    </p>
                </x-page-card>
            </div>
        </div>
    </x-page-shell>
@endsection
