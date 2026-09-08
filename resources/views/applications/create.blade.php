@extends('layouts.app')
@section('title', 'Permohonan Baharu')
@section('heading', 'Borang Penyaluran Sumbangan')
@section('subheading', ($onBehalf ?? false) ? 'Admin JP · Bagi pihak ALP · Langkah 1 daripada 3' : 'Ahli Lembaga Penasihat · Langkah 1 daripada 3')

@section('content')
    <x-wizard-steps :current="1" />

    @if (! $activeYear)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Tiada tahun kewangan aktif. Permohonan tidak boleh dicipta buat masa ini.
        </div>
    @else
        <div class="mb-4 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
            @if ($onBehalf ?? false)
                Admin JP mengisi Borang Penyaluran bagi pihak ALP. Selepas lampiran lengkap, hantar terus kepada
                <strong>Pegawai JP</strong> untuk semakan.
            @else
                ALP mengemukakan permohonan sumbangan dengan borang ini, kemudian menyerahkannya kepada
                <strong>Jabatan Pentadbiran (JP)</strong> bersama lampiran senarai semak.
            @endif
            <x-association-guide-links class="mt-2" />
        </div>

        @if ($waiveProgramLeadTime ?? false)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <strong>Notis pendek dibenarkan:</strong> Admin JP boleh isi tarikh program kurang daripada 2 bulan dari hari ini.
            </div>
        @endif

        <div class="card p-6">
            <h3 class="mb-5 text-sm font-semibold text-gray-900">Borang Penyaluran Sumbangan</h3>
            <form method="POST" action="{{ route('applications.store') }}" class="space-y-8">
                @csrf

                <div class="flex flex-col gap-6">
                    @if ($onBehalf ?? false)
                        <x-field label="a) Nama Ahli Lembaga Penasihat (ALP)" name="alp_id" :required="true">
                            <select id="alp_id" name="alp_id" required class="inp"
                                    onchange="window.location='{{ route('applications.create') }}?alp='+this.value">
                                <option value="">— Pilih ALP —</option>
                                @foreach ($alps as $option)
                                    <option value="{{ $option->id }}" @selected((int) old('alp_id', $alp?->id) === $option->id)>
                                        {{ $option->ref_code }} — {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-field>
                    @else
                        <x-field label="a) Nama Ahli Lembaga Penasihat" name="alp_name">
                            <input type="text" value="{{ $alp?->name }}" class="inp bg-gray-50" readonly>
                        </x-field>
                    @endif

                    <x-field label="b) Nama Persatuan" name="recipient_name" :required="true">
                        <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name') }}" required class="inp">
                    </x-field>

                    <x-field label="c) No. ROS" name="recipient_ros_number" :required="true">
                        <input id="recipient_ros_number" name="recipient_ros_number" type="text" value="{{ old('recipient_ros_number') }}" required class="inp">
                    </x-field>

                    <x-field
                        label="d) Tarikh Program"
                        name="program_date"
                        :required="true"
                        :hint="($waiveProgramLeadTime ?? false)
                            ? 'Admin JP: tarikh program boleh kurang dari 2 bulan (notis pendek).'
                            : 'Sekurang-kurangnya 2 bulan dari hari ini untuk hantar permohonan (dari '.\Carbon\Carbon::parse($programDateMin)->format('d/m/Y').')'"
                    >
                        <input id="program_date" name="program_date" type="date" value="{{ old('program_date') }}"
                               min="{{ $programDateMin }}" required class="inp">
                    </x-field>

                    <x-field label="e) Jenis/Kategori Program" name="program_category" :required="true">
                        <select id="program_category" name="program_category" required class="inp">
                            <option value="">— Pilih kategori —</option>
                            @foreach (\App\Enums\ProgramCategory::options() as $value => $label)
                                <option value="{{ $value }}" @selected(old('program_category') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-field>

                    <x-application-amount-field :limits="$amountLimits" />

                    <x-field label="g) Tujuan Sumbangan" name="purpose" :required="true">
                        <textarea id="purpose" name="purpose" rows="4" required class="inp">{{ old('purpose') }}</textarea>
                    </x-field>

                    <x-field label="h) No. Akaun Penerima Sumbangan" name="recipient_bank_account" :required="true">
                        <input id="recipient_bank_account" name="recipient_bank_account" type="text" value="{{ old('recipient_bank_account') }}" required class="inp">
                    </x-field>

                    <x-field label="i) Alamat Persatuan" name="recipient_address" :required="true" hint="Alamat mesti dalam Wilayah Persekutuan Kuala Lumpur">
                        <textarea id="recipient_address" name="recipient_address" rows="3" required class="inp" placeholder="Contoh: No. 12, Jalan Raja Laut, 50350 Kuala Lumpur">{{ old('recipient_address') }}</textarea>
                    </x-field>
                </div>

                <div class="flex flex-wrap justify-end gap-4 border-t border-gray-100 pt-6">
                    <a href="{{ ($onBehalf ?? false) ? route('applications.all') : route('applications.index') }}" class="btn-white">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Draf &amp; Muat Naik Lampiran</button>
                </div>
            </form>
        </div>
    @endif
@endsection
