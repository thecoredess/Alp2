{{-- Bajet keseluruhan Admin JP — tiga metrik dalam satu baris --}}
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

    <div class="flex w-full flex-col sm:flex-row sm:divide-x sm:divide-gray-100">
        <div class="min-w-0 flex-1 border-b border-gray-100 px-4 py-5 sm:border-b-0">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Peruntukan Tahunan</p>
            <p class="mt-2 text-xl font-bold tabular-nums text-navy-700 sm:text-2xl">
                <x-money :value="$summary->allocation" />
            </p>
        </div>

        <div class="min-w-0 flex-1 border-b border-gray-100 px-4 py-5 sm:border-b-0">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Baki Tersedia</p>
            <p @class([
                'mt-2 text-xl font-bold tabular-nums sm:text-2xl',
                'text-danger' => $summary->available()->isNegative(),
                'text-green-600' => ! $summary->available()->isNegative(),
            ])>
                <x-money :value="$summary->available()" />
            </p>
        </div>

        <div class="min-w-0 flex-1 px-4 py-5">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Baki Peruntukan Semasa</p>
            <p @class([
                'mt-2 text-xl font-bold tabular-nums sm:text-2xl',
                'text-danger' => $projected->isNegative(),
                'text-green-600' => ! $projected->isNegative(),
            ])>
                <x-money :value="$projected" />
            </p>
            <p class="mt-1 line-clamp-2 text-[11px] leading-snug text-gray-400">Peruntukan Diluluskan + Permohonan Dalam Proses</p>
        </div>
    </div>

    @if ($summary->allocation->isPositive())
        <div class="border-t border-gray-100 px-5 py-4">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span class="flex items-center gap-1.5 font-medium"><x-icon name="chart" class="h-3.5 w-3.5" /> Penggunaan Bajet</span>
                <span class="font-semibold text-navy-700">{{ $summary->utilisationPercent() }}%</span>
            </div>
            <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-gradient-to-r from-royal-500 to-royal-400 transition-all" style="width: {{ min(100, $summary->utilisationPercent()) }}%"></div>
            </div>
        </div>
    @endif
</div>
