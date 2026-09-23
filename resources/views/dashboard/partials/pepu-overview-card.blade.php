{{-- Tindakan Diperlukan — baris giliran rata (PEPU, Admin JP, Pegawai JP) --}}
@php
    $queueCount = count($officerQueues);
@endphp

<div class="card overflow-hidden p-0">
    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/60 px-5 py-2.5">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Tindakan Diperlukan</p>
        <p class="text-[11px] font-medium text-gray-400">{{ $queueCount }} giliran</p>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach ($officerQueues as $q)
            @php
                $tone = $q['tone'] ?? 'royal';
                $iconToneClass = match ($tone) {
                    'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
                    'blue' => 'bg-blue-50 text-blue-600 ring-blue-100',
                    'green' => 'bg-green-50 text-green-600 ring-green-100',
                    'navy' => 'bg-navy-50 text-navy-600 ring-navy-100',
                    default => 'bg-royal-50 text-royal-600 ring-royal-100',
                };
                $countToneClass = match ($tone) {
                    'amber' => 'text-amber-600',
                    'blue' => 'text-blue-600',
                    'green' => 'text-green-600',
                    'navy' => 'text-navy-700',
                    default => 'text-royal-600',
                };
                $queueCountValue = (int) ($q['count'] ?? 0);
                $countClass = $queueCountValue === 0 ? 'text-gray-300' : $countToneClass;
                $iconName = $q['icon'] ?? 'inbox';
                $queueRoute = $q['route'];
                $queueLabel = $q['label'];
                $queueDescription = $q['description'] ?? null;
                $queueHref = route($queueRoute);
            @endphp
            <a href="{{ $queueHref }}" class="group flex flex-col gap-3 px-5 py-4 transition hover:bg-gray-50/80 sm:flex-row sm:items-center">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ring-1 {{ $iconToneClass }}">
                        <x-icon name="{{ $iconName }}" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $queueLabel }}</p>
                        @if ($queueDescription)
                            <p class="mt-0.5 text-xs leading-relaxed text-gray-500">{{ $queueDescription }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-6">
                    <div class="w-16 text-right">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Menunggu</p>
                        <p class="mt-0.5 text-lg font-bold tabular-nums leading-none {{ $countClass }}">{{ $queueCountValue }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition group-hover:border-royal-300 group-hover:text-royal-700">
                        Buka giliran
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</div>
