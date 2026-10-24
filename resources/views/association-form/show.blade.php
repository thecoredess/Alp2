@extends('layouts.association-public')
@section('title', 'Isi Borang Penyaluran')
@section('content')
    @php
        use App\Services\Application\ApplicationAssociationInviteService;
    @endphp

    <div class="mb-6 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
        <p><strong>ALP:</strong> {{ $application->alp->name }}</p>
        <p class="mt-1"><strong>Rujukan draf:</strong> {{ $application->application_number }}</p>
        <p class="mt-1 text-royal-800">
            Pautan ini sah sehingga <strong>{{ $expiresAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</strong>
            ({{ ApplicationAssociationInviteService::VALID_DAYS }} hari dari dijana ALP).
        </p>
    </div>

    @if ($application->association_invite_filled_at)
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
            Borang pernah dihantar pada {{ $application->association_invite_filled_at->format('d/m/Y H:i') }}.
            Anda masih boleh kemaskini sebelum tarikh tamat di atas.
        </div>
    @endif

    <div class="card p-6">
        <h2 class="mb-1 text-base font-semibold text-gray-900">Borang Penyaluran Sumbangan</h2>
        <p class="mb-6 text-sm text-gray-500">
            Lengkapkan borang, muat naik lampiran di bahagian seterusnya, kemudian hantar borang di bawah. ALP akan semak semuanya sebelum hantar kepada Admin JP.
        </p>

        <form id="association-borang-form" method="POST" action="{{ route('association-form.update', $token) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-6">
                <x-recipient-name-suggest :application="$application" :association-token="$token" />

                <x-field label="c) No. ROS (PPM-0XX-XX-XXXXXXXX)" name="recipient_ros_number" :required="true" hint="Contoh: PPM-060-01-12345678">
                    <input id="recipient_ros_number" name="recipient_ros_number" type="text"
                           value="{{ old('recipient_ros_number', $application->recipient_ros_number) }}"
                           placeholder="PPM-060-01-12345678" required class="inp font-mono uppercase">
                    <x-recipient-ros-mismatch-alert :association-token="$token" />
                </x-field>

                <x-field
                    label="d) Tarikh Program"
                    name="program_date"
                    :required="true"
                    :hint="'Sekurang-kurangnya 2 bulan dari hari ini (dari '.\Carbon\Carbon::parse($programDateMin)->format('d/m/Y').')'"
                >
                    <input id="program_date" name="program_date" type="date"
                           value="{{ old('program_date', $application->program_date?->format('Y-m-d')) }}"
                           min="{{ $programDateMin }}" required class="inp">
                </x-field>

                <x-program-category-field :selected="old('program_category', $application->program_category?->value)" />

                <x-application-amount-field :limits="$amountLimits" :value="$application->requested_amount" />

                <x-kertas-kerja-fields :application="$application" />

                <x-field label="h) No. Akaun Penerima Sumbangan" name="recipient_bank_account" :required="true">
                    <input id="recipient_bank_account" name="recipient_bank_account" type="text"
                           value="{{ old('recipient_bank_account', $application->recipient_bank_account) }}" required class="inp">
                </x-field>

                <x-field label="i) Alamat Persatuan" name="recipient_address" :required="true" hint="Alamat mesti dalam Wilayah Persekutuan Kuala Lumpur">
                    <textarea id="recipient_address" name="recipient_address" rows="3" required class="inp"
                              placeholder="Contoh: No. 12, Jalan Raja Laut, 50350 Kuala Lumpur">{{ old('recipient_address', $application->recipient_address) }}</textarea>
                </x-field>
            </div>
        </form>
    </div>

    <div class="card mt-8 p-6">
        <h2 class="mb-1 text-base font-semibold text-gray-900">Lampiran Dokumen Sokongan</h2>
        <p class="mb-6 text-sm text-gray-500">Muat naik 4 dokumen wajib (sama seperti senarai semak ALP). Anda boleh kemaskini sebelum pautan tamat.</p>

        @include('applications.partials.contribution-document-upload-grid', [
            'application' => $application,
            'requirements' => $requirements,
            'documentStoreUrl' => route('association-form.documents.store', $token),
            'documentDestroyUrl' => fn ($doc) => route('association-form.documents.destroy', [$token, $doc]),
            'confirmDestroy' => false,
            'showProgress' => true,
        ])
    </div>

    <div class="mt-8 space-y-4 border-t border-gray-200 pt-6">
        <x-association-pdpa-consent class="mt-0" />

        <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:justify-end">
            <p class="text-sm text-gray-600 sm:mr-auto sm:self-center">
                Pastikan borang, lampiran wajib dan persetujuan PDPA lengkap sebelum hantar kepada ALP.
            </p>
            <button type="submit" form="association-borang-form" class="btn-primary">
                Simpan &amp; Hantar Maklumat Borang
            </button>
        </div>
    </div>
@endsection
