@extends('layouts.app')
@section('title', 'Laporan Akhir')
@section('heading', 'Laporan Akhir — '.$project->project_number)

@section('content')
    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('projects.report.store', $project) }}" class="space-y-5">
                @csrf
                <x-field label="Ringkasan Projek" name="summary">
                    <textarea name="summary" rows="3" class="inp">{{ old('summary', $report->summary ?? '') }}</textarea>
                </x-field>
                <x-field label="Hasil / Outcome" name="outcome">
                    <textarea name="outcome" rows="3" class="inp">{{ old('outcome', $report->outcome ?? '') }}</textarea>
                </x-field>
                @if ($project->project_type === \App\Enums\ApplicationType::CSR)
                    <x-field label="Bilangan Penerima Manfaat" name="beneficiary_count">
                        <input name="beneficiary_count" type="number" min="0" value="{{ old('beneficiary_count', $report->beneficiary_count ?? '') }}" class="inp">
                    </x-field>
                    <x-field label="Ringkasan Impak (CSR)" name="impact_summary">
                        <textarea name="impact_summary" rows="3" class="inp">{{ old('impact_summary', $report->impact_summary ?? '') }}</textarea>
                    </x-field>
                @else
                    <x-field label="Ringkasan Penyiapan" name="completion_summary">
                        <textarea name="completion_summary" rows="3" class="inp">{{ old('completion_summary', $report->completion_summary ?? '') }}</textarea>
                    </x-field>
                @endif
                <x-field label="Isu / Cabaran" name="issues">
                    <textarea name="issues" rows="2" class="inp">{{ old('issues', $report->issues ?? '') }}</textarea>
                </x-field>
                <x-field label="Pengajaran" name="lessons_learned">
                    <textarea name="lessons_learned" rows="2" class="inp">{{ old('lessons_learned', $report->lessons_learned ?? '') }}</textarea>
                </x-field>
                <x-field label="Catatan Akhir" name="final_remarks">
                    <textarea name="final_remarks" rows="2" class="inp">{{ old('final_remarks', $report->final_remarks ?? '') }}</textarea>
                </x-field>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('projects.show', $project) }}" class="btn-white">Batal</a>
                    <button class="btn-primary">Hantar Laporan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
