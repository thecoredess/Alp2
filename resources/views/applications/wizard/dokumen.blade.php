@extends('layouts.app')
@section('title', 'Dokumen')
@section('heading', 'Permohonan — '.$application->application_number)
@section('subheading', $application->application_type->label().' · Draf')

@php
    $uploadedTypes = $application->documents->pluck('document_type')->map(fn ($t) => $t->value)->all();
@endphp

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Checklist + senarai --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Senarai Semak Dokumen</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($requirements as $req)
                        @php $done = in_array($req->document_type->value, $uploadedTypes, true); @endphp
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span class="{{ $done ? 'text-green-600' : 'text-gray-300' }}">{{ $done ? '✓' : '○' }}</span>
                                {{ $req->document_type->label() }}
                            </span>
                            @if ($req->is_required)
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700">WAJIB</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">PILIHAN</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Nama Fail</th>
                            <th class="px-4 py-3">Saiz</th>
                            <th class="px-4 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($application->documents as $doc)
                            <tr>
                                <td class="px-4 py-3 text-gray-700">{{ $doc->document_type->label() }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $doc->original_filename }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ number_format($doc->file_size / 1024, 0) }} KB</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('applications.documents.download', [$application, $doc]) }}" class="text-royal-600 hover:text-royal-700 text-xs font-medium">Muat Turun</a>
                                        <form method="POST" action="{{ route('applications.documents.destroy', [$application, $doc]) }}" onsubmit="return confirm('Buang dokumen ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-danger hover:text-red-700 text-xs font-medium">Buang</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada dokumen dimuat naik.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between gap-3">
                <a href="{{ route('applications.wizard.bajet', $application) }}" class="btn-white">← Sebelumnya</a>
                <a href="{{ route('applications.wizard.semakan', $application) }}" class="btn-primary">Seterusnya →</a>
            </div>
        </div>

        {{-- Muat naik --}}
        <div>
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Muat Naik Dokumen</h3>
                <form method="POST" action="{{ route('applications.documents.store', $application) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <x-field label="Jenis Dokumen" name="document_type" :required="true">
                        <select name="document_type" class="inp" required>
                            @foreach (\App\Enums\DocumentType::options() as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    <x-field label="Fail" name="file" :required="true" hint="PDF, imej, Word, Excel. Maksimum 10 MB.">
                        <input type="file" name="file" required class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-700 file:px-3 file:py-2 file:text-white">
                    </x-field>
                    <button class="btn-primary w-full">Muat Naik</button>
                </form>
            </div>
        </div>
    </div>
@endsection
