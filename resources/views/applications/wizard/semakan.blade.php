@extends('layouts.app')
@section('title', 'Semakan')
@section('heading', 'Semakan Sebelum Hantar — '.$application->application_number)
@section('subheading', $application->application_type->label().' · Draf')

@section('content')
    <x-wizard-steps :application="$application" :current="$step" />

    {{-- Amaran --}}
    @if ($missingDocuments->isNotEmpty())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <strong>DOKUMEN WAJIB BELUM LENGKAP:</strong>
            {{ $missingDocuments->map(fn ($t) => $t->label())->implode(', ') }}.
            Sila lengkapkan di <a href="{{ route('applications.wizard.dokumen', $application) }}" class="underline font-medium">langkah Dokumen</a>.
        </div>
    @endif
    @unless ($position['sufficient'])
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <strong>BAKI PERUNTUKAN TIDAK MENCUKUPI</strong> untuk jumlah permohonan ini.
        </div>
    @endunless
    @if ($application->budgetItems->isEmpty())
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Tiada item bajet. Jumlah dipohon mesti melebihi RM0.00.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            {{-- Ringkasan --}}
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Ringkasan Permohonan</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">No. Permohonan</dt><dd class="font-mono text-gray-900">{{ $application->application_number }}</dd></div>
                    <div><dt class="text-gray-500">ALP</dt><dd class="text-gray-900">{{ $application->alp->ref_code }} — {{ $application->alp->name }}</dd></div>
                    <div><dt class="text-gray-500">Tahun Kewangan</dt><dd class="text-gray-900">{{ $application->financialYear->year }}</dd></div>
                    <div><dt class="text-gray-500">Jenis</dt><dd class="text-gray-900">{{ $application->application_type->label() }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Nama Projek</dt><dd class="text-gray-900">{{ $application->project_title }}</dd></div>
                    <div><dt class="text-gray-500">Lokasi</dt><dd class="text-gray-900">{{ $application->location ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Tarikh</dt><dd class="text-gray-900">{{ $application->proposed_start_date?->format('d/m/Y') ?? '—' }} – {{ $application->proposed_end_date?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Objektif</dt><dd class="text-gray-900 whitespace-pre-line">{{ $application->objectives ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Skop</dt><dd class="text-gray-900 whitespace-pre-line">{{ $application->scope ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Kumpulan Sasaran</dt><dd class="text-gray-900">{{ $application->target_group ?? '—' }}</dd></div>
                </dl>
            </div>

            {{-- Bajet --}}
            <div class="card overflow-x-auto">
                <div class="px-4 pt-4 text-sm font-semibold text-gray-900">Pecahan Bajet</div>
                <table class="mt-2 min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-2">Item</th><th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2 text-right">Harga</th><th class="px-4 py-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($application->budgetItems as $item)
                            <tr>
                                <td class="px-4 py-2 text-gray-900">{{ $item->description }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-2 text-right text-gray-600"><x-money :value="$item->unit_cost" /></td>
                                <td class="px-4 py-2 text-right font-medium"><x-money :value="$item->total" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr class="font-semibold"><td class="px-4 py-2" colspan="3">Jumlah Dipohon</td>
                        <td class="px-4 py-2 text-right text-navy-700"><x-money :value="$application->budgetItemsTotal()" /></td></tr>
                    </tfoot>
                </table>
            </div>

            {{-- Dokumen --}}
            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Dokumen</h3>
                @if ($application->documents->isEmpty())
                    <p class="text-sm text-gray-400">Tiada dokumen.</p>
                @else
                    <ul class="space-y-1 text-sm">
                        @foreach ($application->documents as $doc)
                            <li class="flex justify-between"><span class="text-gray-700">{{ $doc->document_type->label() }}</span><span class="text-gray-500">{{ $doc->original_filename }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <x-budget-position :position="$position" />

            <div class="card p-5">
                <form method="POST" action="{{ route('applications.submit', $application) }}"
                      onsubmit="return confirm('Hantar permohonan ini? Selepas dihantar, ia tidak boleh disunting.')">
                    @csrf
                    <button type="submit" class="btn-navy w-full">Hantar Permohonan</button>
                </form>
                <a href="{{ route('applications.wizard.dokumen', $application) }}" class="mt-2 block text-center text-sm text-gray-500 hover:text-gray-700">← Sebelumnya</a>
            </div>
        </div>
    </div>
@endsection
