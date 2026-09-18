{{-- Ringkasan flow mendatar di atas tab — klik untuk buka tab Status --}}
@php
    $stages = collect($timelineStages ?? []);
    $isComplete = fn ($s) => ($s['done'] ?? false) || ($s['skipped'] ?? false);
    $doneCount = $stages->filter($isComplete)->count();
    $totalCount = $stages->count();
    $progress = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;
    $currentIndex = $stages->search(fn ($s) => ! $isComplete($s));
    if ($currentIndex === false) {
        $currentIndex = max(0, $totalCount - 1);
    }

    $shortLabels = [
        'submitted' => 'Permohonan dihantar',
        'jp_review' => 'Semakan Jabatan',
        'peraku' => 'Pengesyoran TP/JP',
        'pepu' => 'Kelulusan PEPU',
        'voucher' => 'Baucar disedia',
        'report' => 'Laporan aktiviti',
        'rejected' => 'Permohonan ditolak',
    ];

    $manySteps = $totalCount > 5;
    $trackInset = $totalCount > 1 ? (50 / $totalCount) : 0;
    $trackFill = $totalCount > 1
        ? min(100, ($currentIndex / ($totalCount - 1)) * 100)
        : ($doneCount > 0 ? 100 : 0);
@endphp

@if ($totalCount > 0)
    <button
        type="button"
        @click="tab = 'perjalanan'"
        class="mb-5 w-full overflow-hidden rounded-xl border border-gray-200 bg-white text-left shadow-sm transition hover:border-royal-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-royal-500"
        aria-label="Buka tab Status untuk butiran kemajuan permohonan"
    >
        <div class="border-b border-gray-100 bg-gradient-to-r from-navy-50 to-royal-50 px-4 py-3.5 sm:px-6">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">Status Permohonan</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $doneCount }} daripada {{ $totalCount }} langkah selesai</p>
                </div>
                <span class="inline-flex shrink-0 items-center gap-1 pt-0.5 text-xs font-medium text-royal-600">
                    Lihat butiran
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </span>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-white/80">
                <div class="h-full rounded-full bg-gradient-to-r from-royal-500 to-green-500 transition-all duration-500" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        <div @class(['px-4 py-5 sm:px-6', 'overflow-x-auto' => $manySteps])>
            <div @class(['relative', 'min-w-[36rem]' => $manySteps, 'w-full' => ! $manySteps])>
                @if ($totalCount > 1)
                    <div
                        class="pointer-events-none absolute top-4 h-0.5 rounded-full bg-gray-200"
                        style="left: {{ $trackInset }}%; right: {{ $trackInset }}%;"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="pointer-events-none absolute top-4 h-0.5 rounded-full bg-green-400 transition-all duration-500"
                        style="left: {{ $trackInset }}%; width: calc((100% - {{ $trackInset * 2 }}%) * {{ $trackFill / 100 }});"
                        aria-hidden="true"
                    ></div>
                @endif

                <ol
                    @class([
                        'relative z-10 w-full',
                        'grid gap-2' => ! $manySteps,
                        'flex min-w-max justify-between gap-6' => $manySteps,
                    ])
                    @unless ($manySteps)
                        style="grid-template-columns: repeat({{ $totalCount }}, minmax(0, 1fr));"
                    @endunless
                >
                    @foreach ($stages as $index => $stage)
                        @php
                            $key = $stage['key'] ?? '';
                            $isSkipped = $stage['skipped'] ?? false;
                            $isRejectedStage = $key === 'rejected'
                                || ($stage['variant'] ?? null) === 'rejected'
                                || ($stage['status_label'] ?? null) === 'Ditolak';
                            $isCurrent = $index === $currentIndex && ! $isComplete($stage) && ! $isRejectedStage;
                            $displayLabel = $shortLabels[$key] ?? $stage['label'];
                        @endphp
                        <li @class([
                            'flex flex-col items-center text-center',
                            'min-w-[5.5rem]' => $manySteps,
                        ])>
                            @if ($isRejectedStage)
                                <span class="timeline-icon timeline-icon--rejected !h-8 !w-8" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </span>
                            @else
                                <span @class([
                                    'relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                    'timeline-icon timeline-icon--done !h-8 !w-8' => $stage['done'] ?? false,
                                    'bg-gray-300 text-white' => $isSkipped,
                                    'bg-royal-500 text-white shadow-md shadow-royal-200 ring-4 ring-white' => $isCurrent,
                                    'bg-gray-100 text-gray-400 ring-4 ring-white ring-gray-100' => ! ($stage['done'] ?? false) && ! $isCurrent && ! $isSkipped,
                                ]) aria-hidden="true">
                                    @if ($stage['done'] ?? false)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    @elseif ($isSkipped)
                                        —
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                            @endif

                            <p @class([
                                'mt-2.5 w-full px-0.5 text-[11px] font-medium leading-snug sm:text-xs',
                                'text-gray-900' => ($stage['done'] ?? false) || $isCurrent,
                                'text-gray-400' => $isSkipped && ! ($stage['done'] ?? false),
                                'text-gray-500' => ! ($stage['done'] ?? false) && ! $isCurrent && ! $isSkipped,
                            ]) title="{{ $stage['label'] }}">
                                {{ $displayLabel }}
                            </p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </button>
@endif
