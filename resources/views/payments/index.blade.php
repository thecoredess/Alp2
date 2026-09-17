@extends('layouts.app')
@section('title', 'Pembayaran / Baucar')
@section('heading', 'Pembayaran & Baucar ALP')
@section('subheading', 'Jejak status bayaran sumbangan (URS) — berasingan daripada belanja projek')

@section('content')
    @if (!empty($jkewScoped))
        <div class="mb-4 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
            <strong>Skop JKEW (SEC-007):</strong> senarai terhad kepada permohonan yang telah dihantar ke JKEW
            (<code>sent_to_jkew_at</code> atau status Dihantar ke JKEW). Kerani dengan <em>payments.manage</em> melihat barisan penuh.
        </div>
    @endif

    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="No. / tajuk / baucar…" class="inp w-full min-w-[12rem] shrink-0 sm:w-56">
        <select name="tahun" class="inp-select shrink-0">
            <option value="">Semua Tahun</option>
            @foreach ($years as $y)
                <option value="{{ $y->id }}" @selected(request('tahun') == $y->id)>{{ $y->year }}</option>
            @endforeach
        </select>
        <select name="status" class="inp-select inp-select--status shrink-0">
            <option value="open" @selected(request('status', 'open') === 'open')>Belum Selesai</option>
            @foreach ($statusOptions as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary shrink-0">Tapis</button>
        <a href="{{ route('payments.export', request()->query()) }}" class="btn-white shrink-0">Eksport CSV</a>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No.</th>
                    <th class="px-4 py-3">ALP</th>
                    <th class="px-4 py-3">Tajuk</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3">Status Bayaran</th>
                    <th class="px-4 py-3">Baucar</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-800">{{ $app->application_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $app->alp?->ref_code }}</td>
                        <td class="px-4 py-3 text-gray-900">
                            <span class="line-clamp-1">{{ $app->programLabelForReport(60) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-4 py-3">
                            @if ($app->payment_status)
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $app->payment_status->badgeClasses() }}">
                                    {{ $app->payment_status->label() }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $app->payment_voucher_no ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-table-actions>
                                <x-table-action href="{{ route('applications.show', $app) }}" icon="eye" label="Buka" variant="primary" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">Tiada rekod pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $applications->links() }}</div>
@endsection
