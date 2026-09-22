@extends('layouts.app')
@section('title', 'Semak Laporan Aktiviti')
@section('heading', 'Semak Laporan Aktiviti')
@section('subheading', $queueStatus->label())

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="No. / tujuan / penerima…" class="inp sm:w-48">
        <select name="tahun" class="inp sm:w-auto">
            <option value="">Semua Tahun</option>
            @foreach ($years as $y)
                <option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary">Tapis</button>
        <x-filter-reset />
        <a href="{{ route('report-cards.index') }}" class="btn-white ml-auto">Pemantauan Laporan</a>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No.</th>
                    <th class="px-4 py-3">ALP</th>
                    <th class="px-4 py-3">Kategori / Penerima</th>
                    <th class="px-4 py-3">Tujuan</th>
                    <th class="px-4 py-3">Dimuat naik</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $app->application_number }}</td>
                        <td class="px-4 py-3">{{ $app->alp?->ref_code }}</td>
                        <td class="px-4 py-3">
                            <p>{{ $app->programCategoryLabelForReport(60) }}</p>
                            <p class="text-xs text-gray-500">{{ $app->recipientLabelForReport() }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <span class="line-clamp-1">{{ $app->purposeLabelForReport(60) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $app->report_card_submitted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-table-actions>
                                <x-table-action href="{{ route('report-cards.review.show', $app) }}" icon="clipboard" label="Semak" variant="primary" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            Tiada laporan menunggu {{ $fullJpDecision ? 'semakan Admin JP' : 'pengesahan Pegawai JP' }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $applications->links() }}</div>
@endsection
