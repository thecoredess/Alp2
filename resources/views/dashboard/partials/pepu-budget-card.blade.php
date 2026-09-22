@php($alpCount = $stats['alp_count'] ?? 0)

{{-- Bajet keseluruhan PEPU — empat metrik dalam satu baris --}}
<div class="card overflow-hidden p-0">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/60 px-5 py-4">
        <h3 class="text-sm font-semibold text-gray-900">
            Bajet Keseluruhan ALP
            @if ($activeYear)
                <span class="font-normal text-gray-400">· {{ $activeYear->year }}</span>
            @endif
        </h3>
        <a href="{{ route('allocations.index') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">Lihat peruntukan →</a>
    </div>

    <div class="flex w-full flex-row divide-x divide-gray-100">
        <div class="min-w-0 flex-1 bg-gradient-to-br from-navy-50/40 to-white px-4 py-5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Ahli Lembaga Aktif</p>
            <p class="mt-2 text-xl font-bold tabular-nums text-navy-700">{{ $alpCount }}</p>
            <p class="mt-1 truncate text-[11px] text-gray-400">ALP berstatus aktif</p>
        </div>

        <div class="min-w-0 flex-1 px-4 py-5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Peruntukan Tahunan</p>
            <p class="mt-2 text-xl font-bold tabular-nums text-navy-700">
                <x-money :value="$summary->allocation" />
            </p>
        </div>

        <div class="min-w-0 flex-1 px-4 py-5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Baki Tersedia</p>
            <p @class([
                'mt-2 text-xl font-bold tabular-nums',
                'text-danger' => $summary->available()->isNegative(),
                'text-green-600' => ! $summary->available()->isNegative(),
            ])>
                <x-money :value="$summary->available()" />
            </p>
        </div>

        <div class="min-w-0 flex-1 px-4 py-5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Baki Peruntukan Semasa</p>
            <p @class([
                'mt-2 text-xl font-bold tabular-nums',
                'text-danger' => $projected->isNegative(),
                'text-green-600' => ! $projected->isNegative(),
            ])>
                <x-money :value="$projected" />
            </p>
            <p class="mt-1 line-clamp-2 text-[11px] leading-snug text-gray-400">Peruntukan Diluluskan + Permohonan Dalam Proses</p>
        </div>
    </div>
</div>
