{{-- Tindakan Diperlukan PEPU — kad tunggal --}}
<div class="card overflow-hidden p-0">
    <div class="border-b border-gray-100 bg-gray-50/60 px-5 py-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Tindakan Diperlukan</p>
    </div>
    <div class="space-y-3 p-5">
        @foreach ($officerQueues as $q)
            @php
                $toneClasses = match ($q['tone'] ?? 'royal') {
                    'amber' => ['icon' => 'bg-amber-500', 'count' => 'text-amber-600'],
                    'blue' => ['icon' => 'bg-blue-500', 'count' => 'text-blue-600'],
                    'green' => ['icon' => 'bg-green-500', 'count' => 'text-green-600'],
                    'navy' => ['icon' => 'bg-navy-600', 'count' => 'text-navy-700'],
                    default => ['icon' => 'bg-royal-500', 'count' => 'text-royal-600'],
                };
            @endphp
            <a href="{{ route($q['route']) }}" class="group flex flex-col gap-4 rounded-xl border border-gray-200 bg-gray-50/40 p-4 transition hover:border-royal-200 hover:bg-white sm:flex-row sm:items-center">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <div @class(['flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm', $toneClasses['icon']])>
                        <x-icon :name="$q['icon'] ?? 'inbox'" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $q['label'] }}</p>
                        @if (! empty($q['description']))
                            <p class="mt-0.5 text-xs leading-relaxed text-gray-500">{{ $q['description'] }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 items-center justify-between gap-4 border-t border-gray-200/80 pt-3 sm:flex-col sm:items-end sm:border-t-0 sm:pt-0">
                    <div class="text-left sm:text-right">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Menunggu</p>
                        <p @class(['text-2xl font-bold tabular-nums leading-none', $toneClasses['count'], 'opacity-40' => (int) $q['count'] === 0])>{{ $q['count'] }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 ring-1 ring-gray-200 transition group-hover:text-royal-700">
                        Buka giliran
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</div>
