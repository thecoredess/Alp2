@extends('layouts.app')
@section('title', 'Laporan Aktiviti / Report Card')
@section('heading', 'Laporan Aktiviti & Report Card')
@section('subheading', 'Pemantauan penerimaan laporan selepas program (URS M07)')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap gap-2">
        <select name="tahun" class="inp sm:w-auto">
            <option value="">Semua Tahun</option>
            @foreach ($years as $y)
                <option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>
            @endforeach
        </select>
        <select name="status" class="inp sm:w-auto">
            <option value="">Semua Status Report Card</option>
            <option value="missing" @selected(request('status') === 'missing')>Belum dimuat naik</option>
            <option value="submitted" @selected(request('status') === 'submitted')>Sudah dimuat naik</option>
        </select>
        <button type="submit" class="btn-primary">Tapis</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No.</th>
                    <th class="px-4 py-3">ALP</th>
                    <th class="px-4 py-3">Program</th>
                    <th class="px-4 py-3">Tarikh Akhir (BR-018)</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $app)
                    @php
                        $due = $reportCards->dueDate($app);
                        $overdue = $reportCards->isOverdue($app);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $app->application_number }}</td>
                        <td class="px-4 py-3">{{ $app->alp?->ref_code }}</td>
                        <td class="px-4 py-3">
                            <span class="line-clamp-1">{{ $app->project_title }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $due?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($app->report_card_submitted_at)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Diterima {{ $app->report_card_submitted_at->format('d/m/Y') }}</span>
                            @elseif ($overdue)
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Tertunggak</span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('applications.show', $app) }}" class="btn-primary !py-1 !px-3 text-xs">Buka</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tiada rekod.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $applications->links() }}</div>
@endsection
