@extends('layouts.app')
@section('title', 'Hantar kepada JP')
@section('heading', 'Semakan sebelum hantar — '.$application->application_number)
@section('subheading', 'Langkah 3 · Serahan kepada Jabatan Pentadbiran')

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    @php
        $jpIncomplete = $jpIncomplete ?? [];
        $jpCard = fn (string ...$keys) => \App\Support\JpReviewChecklist::cardClasses($jpIncomplete, ...$keys);
    @endphp

    <x-page-shell>
        @include('applications.partials.program-date-warning')
        @include('applications.partials.jp-incomplete-banner')

        @if ($missingDocuments->isNotEmpty())
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <strong>Lampiran belum lengkap:</strong>
                {{ $missingDocuments->map(fn ($t) => $t->label())->implode(', ') }}.
                <a href="{{ route('applications.wizard.dokumen', $application) }}" class="font-medium underline">Ke langkah Lampiran</a>.
            </div>
        @endif
        @unless ($position['sufficient'])
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>Baki peruntukan tidak mencukupi</strong> untuk jumlah permohonan ini.
            </div>
        @endunless

        <div class="space-y-4">
            <x-page-card title="Borang Penyaluran Sumbangan" icon="clipboard">
                <dl class="flex flex-col gap-4">
                    <div class="rounded-xl border border-gray-100 p-4">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">a) Nama ALP</dt>
                        <dd class="mt-1.5 font-medium text-gray-900">{{ $application->alp->name }}</dd>
                    </div>
                    <div class="{{ $jpCard('recipient') }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">b) Nama Persatuan</dt>
                        <dd class="mt-1.5 font-medium text-gray-900">{{ $application->recipient_name }}</dd>
                    </div>
                    <div class="{{ $jpCard('recipient') }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">c) No. ROS</dt>
                        <dd class="mt-1.5 font-mono font-medium text-gray-900">{{ $application->recipient_ros_number }}</dd>
                    </div>
                    <div class="{{ $jpCard('recipient') }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">d) Tarikh Program</dt>
                        <dd class="mt-1.5 font-medium text-gray-900">{{ $application->program_date?->format('d/m/Y') }}</dd>
                    </div>
                    <div class="{{ $jpCard('recipient') }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">e) Jenis/Kategori Program</dt>
                        <dd class="mt-1.5 font-medium text-gray-900">{{ $application->program_category?->label() ?? '—' }}</dd>
                    </div>
                    <div class="{{ \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'bajet', 'program_syarat') ? 'rounded-xl border-2 border-red-400 bg-red-50 p-4 ring-1 ring-red-200' : 'rounded-xl border border-royal-100 bg-gradient-to-br from-royal-50 to-navy-50 p-4' }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-royal-600">f) Jumlah Sumbangan</dt>
                        <dd class="mt-1 text-2xl font-bold text-navy-700"><x-money :value="$application->requested_amount" /></dd>
                    </div>
                    <div class="{{ $jpCard('program_syarat') }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">g) Tujuan</dt>
                        <dd class="mt-1.5 whitespace-pre-line text-gray-900">{{ $application->purpose }}</dd>
                    </div>
                    <div class="{{ $jpCard('recipient') }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">h) No. Akaun</dt>
                        <dd class="mt-1.5 font-mono font-medium text-gray-900">{{ $application->recipient_bank_account }}</dd>
                    </div>
                    <div class="{{ $jpCard('recipient') }}">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">i) Alamat Persatuan</dt>
                        <dd class="mt-1.5 whitespace-pre-line text-gray-900">{{ $application->recipient_address ?? '—' }}</dd>
                    </div>
                </dl>
            </x-page-card>

            <div @class([
                'rounded-xl',
                'ring-2 ring-red-400' => \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
            ])>
                <x-page-card title="Lampiran Senarai Semak" icon="paper-clip">
                    @if ($application->documents->isEmpty())
                        <p class="text-sm text-gray-400">Tiada lampiran.</p>
                    @else
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($application->documents as $doc)
                                <div @class([
                                    'rounded-lg border px-3 py-2 text-sm',
                                    'border-red-300 bg-red-50' => \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
                                    'border-gray-100 bg-gray-50' => ! \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, 'dokumen'),
                                ])>
                                    <p class="font-medium text-gray-900">{{ $doc->document_type->simpleLabel() }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $doc->original_filename }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-page-card>
            </div>

            <x-page-card title="Hantar Permohonan" description="Permohonan akan disemak oleh Jabatan Pentadbiran (JP) selepas dihantar">
                <form method="POST" action="{{ route('applications.submit', $application) }}"
                      data-swal-confirm="Adakah anda pasti mahu menghantar permohonan ini kepada Jabatan Pentadbiran (JP)? Selepas dihantar, permohonan tidak boleh dikemaskini."
                      data-swal-title="Hantar Permohonan"
                      data-swal-icon="warning">
                    @csrf
                    <div class="rounded-xl border border-navy-100 bg-navy-50/40 p-5">
                        <p class="text-sm text-gray-600">
                            Sila pastikan borang dan lampiran lengkap serta tepat. Permohonan yang telah dihantar tidak boleh dikemaskini.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3 border-t border-navy-100/80 pt-5">
                            <a href="{{ route('applications.wizard.dokumen', $application) }}" class="btn-white">← Lampiran</a>
                            @if (($programDateSubmitErrors ?? []) === [])
                                <button type="submit" class="btn-navy">
                                    {{ auth()->user()->canCreateApplicationOnBehalf() ? 'Hantar kepada Pegawai JP' : 'Hantar kepada JP' }}
                                </button>
                            @else
                                <button type="button" class="btn-navy cursor-not-allowed opacity-50" disabled title="Kemaskini tarikh program terlebih dahulu">
                                    {{ auth()->user()->canCreateApplicationOnBehalf() ? 'Hantar kepada Pegawai JP' : 'Hantar kepada JP' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-page-card>
        </div>
    </x-page-shell>
@endsection
