@extends('layouts.app')
@section('title', 'Maklumat Permohonan')
@section('heading', 'Permohonan — '.$application->application_number)
@section('subheading', $application->application_type->label().' · Draf')

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('applications.wizard.maklumat.update', $application) }}" class="space-y-5">
                @csrf @method('PUT')

                <x-field label="Jenis Permohonan" name="application_type" :required="true"
                         hint="Menukar jenis akan mengubah dokumen wajib yang diperlukan.">
                    <select id="application_type" name="application_type" class="inp" required>
                        @foreach (\App\Enums\ApplicationType::options() as $val => $label)
                            <option value="{{ $val }}" @selected(old('application_type', $application->application_type->value) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-field>

                <x-field label="Tujuan / Nama Program" name="project_title" :required="true">
                    <input id="project_title" name="project_title" type="text" value="{{ old('project_title', $application->project_title) }}" required class="inp">
                </x-field>

                <x-field label="Jenis Program (URS BR-011)" name="program_category" :required="true">
                    <select id="program_category" name="program_category" class="inp" required>
                        <option value="">— Pilih —</option>
                        @foreach (\App\Enums\ProgramCategory::options() as $val => $label)
                            <option value="{{ $val }}" @selected(old('program_category', $application->program_category?->value) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-field>

                <x-field label="Ringkasan" name="project_summary">
                    <textarea id="project_summary" name="project_summary" rows="3" class="inp">{{ old('project_summary', $application->project_summary) }}</textarea>
                </x-field>

                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Penerima sumbangan (TBL-10 B / E / O / P)</p>

                    <x-field label="Nama Penerima / Persatuan" name="recipient_name" :required="true">
                        <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name', $application->recipient_name) }}" required class="inp">
                    </x-field>

                    <x-field label="Nombor Pendaftaran ROS" name="recipient_ros_number" :required="true"
                             hint="Kawalan satu persatuan sekali setahun (BR-009 / BR-023).">
                        <input id="recipient_ros_number" name="recipient_ros_number" type="text" value="{{ old('recipient_ros_number', $application->recipient_ros_number) }}" required class="inp">
                    </x-field>

                    <x-field label="No. Akaun Penerima" name="recipient_bank_account" :required="true">
                        <input id="recipient_bank_account" name="recipient_bank_account" type="text" value="{{ old('recipient_bank_account', $application->recipient_bank_account) }}" required class="inp">
                    </x-field>

                    <x-field label="Alamat Persatuan (Kuala Lumpur)" name="recipient_address" :required="true"
                             hint="BR-010: mesti berdaftar di Kuala Lumpur.">
                        <textarea id="recipient_address" name="recipient_address" rows="2" required class="inp">{{ old('recipient_address', $application->recipient_address) }}</textarea>
                    </x-field>
                </div>

                <x-field label="Lokasi Program (KL)" name="location" :required="true">
                    <input id="location" name="location" type="text" value="{{ old('location', $application->location) }}" required class="inp" placeholder="cth. Kampung Baru, Kuala Lumpur">
                </x-field>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field label="Tarikh Program Mula" name="proposed_start_date" :required="true"
                             hint="BR-014: hantar ≥ 2 bulan sebelum tarikh program.">
                        <input id="proposed_start_date" name="proposed_start_date" type="date"
                               value="{{ old('proposed_start_date', $application->proposed_start_date?->format('Y-m-d')) }}" required class="inp">
                    </x-field>
                    <x-field label="Tarikh Program Tamat" name="proposed_end_date">
                        <input id="proposed_end_date" name="proposed_end_date" type="date"
                               value="{{ old('proposed_end_date', $application->proposed_end_date?->format('Y-m-d')) }}" class="inp">
                    </x-field>
                </div>

                <label class="flex items-start gap-3 rounded-lg border border-amber-100 bg-amber-50 px-4 py-3">
                    <input type="checkbox" name="compliance_declaration" value="1" class="mt-1 rounded border-gray-300 text-royal-600 focus:ring-royal-500"
                           @checked(old('compliance_declaration', $application->compliance_declared_at)) required>
                    <span class="text-sm text-amber-950">
                        Saya mengesahkan sumbangan <strong>bukan</strong> untuk kos pentadbiran persatuan, aktiviti politik, sambutan perayaan atau aktiviti keagamaan (BR-012).
                    </span>
                </label>
                @error('compliance_declaration')<p class="text-xs text-danger">{{ $message }}</p>@enderror

                <div class="flex justify-between gap-3 pt-2">
                    <a href="{{ route('applications.index') }}" class="btn-white">Simpan &amp; Keluar</a>
                    <button type="submit" class="btn-primary">Seterusnya →</button>
                </div>
            </form>
        </div>
    </div>
@endsection
