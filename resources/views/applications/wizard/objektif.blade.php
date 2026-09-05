@extends('layouts.app')
@section('title', 'Objektif & Skop')
@section('heading', 'Permohonan — '.$application->application_number)
@section('subheading', $application->application_type->label().' · Draf')

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('applications.wizard.objektif.update', $application) }}" class="space-y-5">
                @csrf @method('PUT')

                <x-field label="Objektif" name="objectives">
                    <textarea id="objectives" name="objectives" rows="4" class="inp">{{ old('objectives', $application->objectives) }}</textarea>
                </x-field>

                <x-field label="Skop" name="scope">
                    <textarea id="scope" name="scope" rows="4" class="inp">{{ old('scope', $application->scope) }}</textarea>
                </x-field>

                <x-field label="Kumpulan Sasaran" name="target_group">
                    <input id="target_group" name="target_group" type="text" value="{{ old('target_group', $application->target_group) }}" class="inp">
                </x-field>

                <div class="flex justify-between gap-3 pt-2">
                    <a href="{{ route('applications.wizard.maklumat', $application) }}" class="btn-white">← Sebelumnya</a>
                    <button type="submit" class="btn-primary">Seterusnya →</button>
                </div>
            </form>
        </div>
    </div>
@endsection
