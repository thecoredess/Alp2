@extends('layouts.app')
@section('title', 'Laporan Aktiviti / Report Card')
@section('heading', 'Laporan Aktiviti & Report Card')
@section('subheading', 'Pemantauan penerimaan laporan — 1 bulan selepas baucar disedia')

@section('content')
    @if ($canReview ?? false)
        <div class="mb-4">
            <a href="{{ route('report-cards.review.index') }}" class="btn-primary text-sm">
                Semak Laporan Aktiviti (Giliran JP)
            </a>
        </div>
    @endif

    <form method="GET" class="mb-5 flex flex-wrap gap-2">
        <select name="tahun" class="inp sm:w-auto">
            <option value="">Semua Tahun</option>
            @foreach ($years as $y)
                <option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>
            @endforeach
        </select>
        <select name="status" class="inp sm:w-auto">
            <option value="">Semua Status</option>
            <option value="missing" @selected(request('status') === 'missing')>Belum dimuat naik</option>
            <option value="in_review" @selected(request('status') === 'in_review')>Dalam semakan JP</option>
            <option value="approved" @selected(request('status') === 'approved')>Disahkan</option>
            <option value="returned" @selected(request('status') === 'returned')>Dikembalikan</option>
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
                        $rcStatus = $app->report_card_status;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $app->application_number }}</td>
                        <td class="px-4 py-3">{{ $app->alp?->ref_code }}</td>
                        <td class="px-4 py-3">
                            <span class="line-clamp-1">{{ $app->project_title }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $due?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($rcStatus === \App\Enums\ReportCardStatus::APPROVED)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                    Disahkan {{ $app->report_card_submitted_at?->format('d/m/Y') }}
                                </span>
                            @elseif ($rcStatus instanceof \App\Enums\ReportCardStatus)
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $rcStatus->badgeClasses() }}">
                                    {{ $rcStatus->label() }}
                                </span>
                            @elseif ($overdue)
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Tertunggak</span>
                            @elseif (! $app->hasVoucherPrepared())
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Menunggu baucar</span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Menunggu laporan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('applications.show', [$app, 'tab' => 'report']) }}" class="btn-primary !py-1 !px-3 text-xs">Buka</a>
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
