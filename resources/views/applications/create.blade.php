@extends('layouts.app')
@section('title', 'Permohonan Baharu')
@section('heading', 'Permohonan Baharu')

@section('content')
    <div class="max-w-2xl">
        @if (! $activeYear)
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Tiada tahun kewangan aktif. Permohonan tidak boleh dicipta buat masa ini.
            </div>
        @else
            <div class="card p-6">
                <p class="mb-4 text-sm text-gray-500">Langkah 1 daripada 6 — Maklumat Projek. Permohonan akan disimpan sebagai draf untuk tahun kewangan {{ $activeYear->year }}.</p>
                <form method="POST" action="{{ route('applications.store') }}" class="space-y-5">
                    @csrf
                    <x-field label="Jenis Permohonan" name="application_type" :required="true">
                        <select id="application_type" name="application_type" class="inp" required>
                            <option value="">— Pilih Jenis —</option>
                            @foreach ($types as $val => $label)
                                <option value="{{ $val }}" @selected(old('application_type') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-field>

                    <x-field label="Nama Projek" name="project_title" :required="true">
                        <input id="project_title" name="project_title" type="text" value="{{ old('project_title') }}" required class="inp">
                    </x-field>

                    <x-field label="Ringkasan" name="project_summary">
                        <textarea id="project_summary" name="project_summary" rows="3" class="inp">{{ old('project_summary') }}</textarea>
                    </x-field>

                    <x-field label="Lokasi" name="location">
                        <input id="location" name="location" type="text" value="{{ old('location') }}" class="inp">
                    </x-field>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-field label="Tarikh Cadangan Mula" name="proposed_start_date">
                            <input id="proposed_start_date" name="proposed_start_date" type="date" value="{{ old('proposed_start_date') }}" class="inp">
                        </x-field>
                        <x-field label="Tarikh Cadangan Tamat" name="proposed_end_date">
                            <input id="proposed_end_date" name="proposed_end_date" type="date" value="{{ old('proposed_end_date') }}" class="inp">
                        </x-field>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('applications.index') }}" class="btn-white">Batal</a>
                        <button type="submit" class="btn-primary">Simpan Draf &amp; Seterusnya</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection
