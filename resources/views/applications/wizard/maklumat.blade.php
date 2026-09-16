@extends('layouts.app')
@section('title', 'Borang Penyaluran')
@section('heading', 'Permohonan — '.$application->application_number)
@section('subheading', 'Draf · Borang Penyaluran Sumbangan · Langkah 1')

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    @php
        $jpIncomplete = $jpIncomplete ?? [];
        $jpFieldWrap = fn (string ...$keys) => \App\Support\JpReviewChecklist::hasIncomplete($jpIncomplete, ...$keys)
            ? 'rounded-xl border-2 border-red-400 bg-red-50 p-4 ring-1 ring-red-200'
            : '';
    @endphp

    <x-page-shell>
        @include('applications.partials.program-date-warning')
        @include('applications.partials.jp-incomplete-banner')

        <x-page-card title="Borang Penyaluran Sumbangan" description="Kemaskini medan a–i" icon="clipboard">
            <form method="POST" action="{{ route('applications.wizard.maklumat.update', $application) }}" class="space-y-8">
                @csrf @method('PUT')

                <div class="flex flex-col gap-6">
                    <x-field label="a) Nama Ahli Lembaga Penasihat" name="alp_name">
                        <input type="text" value="{{ $alp?->name ?? $application->alp?->name }}" class="inp bg-gray-50" readonly>
                    </x-field>

                    <div class="{{ $jpFieldWrap('recipient') }}">
                        <x-field label="b) Nama Persatuan" name="recipient_name" :required="true">
                            <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name', $application->recipient_name) }}" required class="inp">
                        </x-field>
                    </div>

                    <div class="{{ $jpFieldWrap('recipient') }}">
                        <x-field label="c) No. ROS (PPM-0XX-XX-XXXXXXXX)" name="recipient_ros_number" :required="true" hint="Contoh: PPM-060-01-12345678">
                            <input id="recipient_ros_number" name="recipient_ros_number" type="text"
                                   value="{{ old('recipient_ros_number', $application->recipient_ros_number) }}"
                                   placeholder="PPM-060-01-12345678" required class="inp font-mono uppercase">
                        </x-field>
                    </div>

                    <div class="{{ $jpFieldWrap('recipient') }}">
                        <x-field
                            label="d) Tarikh Program"
                            name="program_date"
                            :required="true"
                            :hint="($waiveProgramLeadTime ?? false)
                                ? 'Admin JP: tarikh program boleh kurang dari 2 bulan (notis pendek).'
                                : 'Sekurang-kurangnya 2 bulan dari hari ini untuk hantar permohonan (dari '.\Carbon\Carbon::parse($programDateMin)->format('d/m/Y').')'"
                        >
                            <input id="program_date" name="program_date" type="date"
                                   value="{{ old('program_date', $application->program_date?->format('Y-m-d')) }}"
                                   min="{{ $programDateMin }}" required class="inp">
                        </x-field>
                    </div>

                    <div class="{{ $jpFieldWrap('recipient') }}">
                        <x-field label="e) Jenis/Kategori Program" name="program_category" :required="true">
                            <select id="program_category" name="program_category" required class="inp">
                                <option value="">— Pilih kategori —</option>
                                @foreach (\App\Enums\ProgramCategory::options() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('program_category', $application->program_category?->value) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-field>
                    </div>

                    <div class="{{ $jpFieldWrap('bajet', 'program_syarat') }}">
                        <x-application-amount-field :limits="$amountLimits" :value="$application->requested_amount" />
                    </div>

                    <div class="{{ $jpFieldWrap('program_syarat') }}">
                        <x-field label="g) Tujuan Sumbangan" name="purpose" :required="true">
                            <textarea id="purpose" name="purpose" rows="4" required class="inp">{{ old('purpose', $application->purpose) }}</textarea>
                        </x-field>
                    </div>

                    <div class="{{ $jpFieldWrap('recipient') }}">
                        <x-field label="h) No. Akaun Penerima Sumbangan" name="recipient_bank_account" :required="true">
                            <input id="recipient_bank_account" name="recipient_bank_account" type="text"
                                   value="{{ old('recipient_bank_account', $application->recipient_bank_account) }}" required class="inp">
                        </x-field>
                    </div>

                    <div class="{{ $jpFieldWrap('recipient') }}">
                        <x-field label="i) Alamat Persatuan" name="recipient_address" :required="true" hint="Alamat mesti dalam Wilayah Persekutuan Kuala Lumpur">
                            <textarea id="recipient_address" name="recipient_address" rows="3" required class="inp"
                                      placeholder="Contoh: No. 12, Jalan Raja Laut, 50350 Kuala Lumpur">{{ old('recipient_address', $application->recipient_address) }}</textarea>
                        </x-field>
                    </div>
                </div>

                <div class="flex flex-wrap justify-between gap-4 border-t border-gray-100 pt-6">
                    <a href="{{ route('applications.index') }}" class="btn-white">Simpan &amp; Keluar</a>
                    <button type="submit" class="btn-primary">Seterusnya → Lampiran</button>
                </div>
            </form>
        </x-page-card>
    </x-page-shell>
@endsection
