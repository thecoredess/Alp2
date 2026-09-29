@extends('layouts.app')
@section('title', 'Aktiviti Selesai — '.$alp->ref_code)
@section('heading', 'Aktiviti Selesai Mengikut ALP')
@section('subheading', $alp->ref_code.' · '.$alp->name.' · Tahun Kewangan '.$year->year)

@section('content')
    <div class="mb-4">
        <a href="{{ route('reports.index', ['fy' => $year->id]) }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Kembali ke Laporan</a>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-4">
            <p class="text-xs text-gray-500">Peruntukan Tahunan (ALP)</p>
            <p class="mt-1 text-lg font-semibold text-navy-700"><x-money :value="$budget->allocation" /></p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Belanja Bersih (lejar)</p>
            <p class="mt-1 text-lg font-semibold text-purple-700"><x-money :value="$budget->netSpent()" /></p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Jumlah Sumbangan — aktiviti selesai</p>
            <p class="mt-1 text-lg font-semibold text-green-700"><x-money :value="$totalAmount" /></p>
            <p class="mt-1 text-xs text-gray-400">{{ $activities->count() }} aktiviti</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Baki Peruntukan Tersedia</p>
            <p class="mt-1 text-lg font-semibold text-teal-600"><x-money :value="$budget->available()" /></p>
        </div>
    </div>

    <p class="mb-4 text-sm text-gray-500">
        Senarai permohonan sumbangan <strong>diluluskan</strong> dengan <strong>laporan aktiviti disahkan JP</strong>.
        Amaun sumbangan = jumlah diluluskan bagi setiap program.
    </p>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">No. Permohonan</th>
                    <th class="px-4 py-3">Kategori Aktiviti</th>
                    <th class="px-4 py-3">Penerima / Program</th>
                    <th class="px-4 py-3">Tarikh Program</th>
                    <th class="px-4 py-3 text-right">Sumbangan (RM)</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($activities as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $app->application_number }}</td>
                        <td class="px-4 py-2 text-gray-800">{{ $app->program_category?->label() ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <span class="block text-gray-900">{{ $app->recipient_name ?? '—' }}</span>
                            @if ($app->purpose)
                                <span class="mt-0.5 block max-w-md truncate text-xs text-gray-500" title="{{ $app->purpose }}">{{ $app->purpose }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $app->program_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-2 text-right font-medium tabular-nums text-gray-900"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-4 py-2 text-right">
                            <x-table-actions>
                                <x-table-action href="{{ route('applications.show', $app) }}" icon="eye" label="Lihat" variant="primary" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">Tiada aktiviti selesai bagi ALP ini pada tahun dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($activities->isNotEmpty())
                <tfoot class="bg-navy-50/60">
                    <tr class="font-semibold">
                        <td class="px-4 py-3 text-gray-800" colspan="4">Jumlah sumbangan (aktiviti selesai)</td>
                        <td class="px-4 py-3 text-right tabular-nums text-navy-800"><x-money :value="$totalAmount" /></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
