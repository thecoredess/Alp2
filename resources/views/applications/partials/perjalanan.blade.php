{{-- Status permohonan — timeline menegak --}}
@php
    $isAlpView = $isAlpView ?? false;
    $stages = collect($timelineStages ?? []);
    $isComplete = fn ($s) => ($s['done'] ?? false) || ($s['skipped'] ?? false);
    $doneCount = $stages->filter($isComplete)->count();
    $totalCount = $stages->count();
    $currentIndex = $stages->search(fn ($s) => ! $isComplete($s));
    if ($currentIndex === false) {
        $currentIndex = max(0, $totalCount - 1);
    }
@endphp

<div class="space-y-8">
    {{-- Ringkasan kemajuan --}}
    <div class="card">
        <div class="overflow-hidden border-b border-gray-100 bg-gradient-to-r from-navy-50 to-royal-50 px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Status Permohonan</h3>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $doneCount }} daripada {{ $totalCount }} langkah selesai</p>
                </div>
                @if (($timelineKpi ?? null) && ! $isAlpView)
                    <span @class([
                        'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-green-100 text-green-800' => $timelineKpi['within_kpi'] === true,
                        'bg-red-100 text-red-800' => $timelineKpi['within_kpi'] === false,
                        'bg-gray-100 text-gray-600' => $timelineKpi['within_kpi'] === null,
                    ])>{{ $timelineKpi['label'] }}</span>
                @endif
            </div>
            @if ($totalCount > 0)
                <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-white/80">
                    <div class="h-full rounded-full bg-gradient-to-r from-royal-500 to-green-500 transition-all duration-500"
                         style="width: {{ round(($doneCount / $totalCount) * 100) }}%"></div>
                </div>
            @endif
        </div>

        {{-- Timeline menegak --}}
        <div class="p-6">
            <style>
                .timeline-step { position: relative; }
                .timeline-step:not(:last-child)::after {
                    content: '';
                    position: absolute;
                    left: 15px;
                    top: 16px;
                    bottom: -16px;
                    width: 2px;
                    background: #d1d5db;
                    z-index: 0;
                }
                .timeline-step.is-connected:not(:last-child)::after {
                    background: #4ade80;
                }
            </style>
            <ol class="relative space-y-0">
                @foreach ($stages as $index => $stage)
                    @php
                        $isSkipped = $stage['skipped'] ?? false;
                        $isRejectedStage = ($stage['key'] ?? '') === 'rejected'
                            || ($stage['variant'] ?? null) === 'rejected'
                            || ($stage['status_label'] ?? null) === 'Ditolak';
                        $isCurrent = $index === $currentIndex && ! $isComplete($stage) && ! $isRejectedStage;
                        $isLast = $index === $totalCount - 1;
                        $connectorGreen = (($stage['done'] ?? false) || $isSkipped) && ! $isRejectedStage;
                    @endphp
                    <li @class([
                        'timeline-step relative flex gap-4 pb-12 last:pb-0',
                        'is-connected' => $connectorGreen && ! $isLast,
                    ])>
                        @if ($isRejectedStage)
                            <span class="timeline-icon timeline-icon--rejected" style="background-color:#dc2626;color:#fff" aria-label="Ditolak">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="#fff" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </span>
                        @else
                            <span @class([
                                'timeline-icon',
                                'timeline-icon--done' => $stage['done'] ?? false,
                                'bg-gray-300 text-white shadow-sm' => $isSkipped,
                                'bg-royal-500 text-white shadow-md shadow-royal-200' => $isCurrent,
                                'bg-gray-100 text-gray-400' => ! ($stage['done'] ?? false) && ! $isCurrent && ! $isSkipped,
                            ])>
                                @if ($stage['done'] ?? false)
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                @elseif ($isSkipped)
                                    <span class="text-[10px] font-bold">—</span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </span>
                        @endif

                        <div @class([
                            'min-w-0 flex-1 rounded-xl border px-4 py-3 transition',
                            'border-red-200 bg-red-50/60' => $isRejectedStage,
                            'border-green-200 bg-green-50/60' => $stage['done'] && ! $isRejectedStage,
                            'border-gray-200 bg-gray-100/60' => $isSkipped,
                            'border-royal-200 bg-royal-50/60 shadow-sm' => $isCurrent,
                            'border-gray-100 bg-gray-50/50' => ! $stage['done'] && ! $isCurrent && ! $isSkipped,
                        ])>
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p @class([
                                    'font-semibold',
                                    'text-gray-900' => $stage['done'] || $isCurrent,
                                    'text-gray-500' => ! $stage['done'] && ! $isCurrent,
                                ])>{{ $stage['label'] }}</p>
                                @if ($isCurrent)
                                    <span class="inline-flex rounded-full bg-royal-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-royal-700">{{ $stage['status_label'] ?? 'Sedang diproses' }}</span>
                                @elseif ($isRejectedStage)
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-700">Ditolak</span>
                                @elseif ($stage['done'])
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-green-700">Selesai</span>
                                @elseif ($isSkipped)
                                    <span class="inline-flex rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-gray-600">Tidak diperluan</span>
                                @endif
                            </div>
                            @php
                                $showStageDetail = $stage['at']
                                    || ($stage['hint'] ?? null)
                                    || ($isSkipped && ($stage['hint'] ?? null))
                                    || (! $isAlpView && ! $isCurrent);
                            @endphp
                            @if ($showStageDetail)
                                @if ($stage['at'])
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $stage['at']->format('d/m/Y H:i') }}
                                        @if ($stage['days_from_submit'] !== null)
                                            <span class="text-gray-400">· +{{ $stage['days_from_submit'] }} hari</span>
                                        @endif
                                    </p>
                                @endif
                                @if ($stage['hint'] ?? null)
                                    <p @class([
                                        'mt-1 text-sm',
                                        'text-red-800' => $isRejectedStage,
                                        'text-gray-600' => ! $isRejectedStage,
                                    ])>
                                        @if ($stage['key'] === 'voucher' && str_contains($stage['hint'], 'https://dbayar.dbkl.gov.my'))
                                            Semakan bayaran boleh disemak melalui
                                            <a href="https://dbayar.dbkl.gov.my" target="_blank" rel="noopener noreferrer" class="font-medium text-royal-600 underline hover:text-royal-700">https://dbayar.dbkl.gov.my</a>
                                            dengan menggunakan no. pembekal
                                            @if (filled($stage['payment_supplier_no'] ?? null))
                                                <span class="font-mono font-semibold text-gray-800">{{ $stage['payment_supplier_no'] }}</span>.
                                            @else
                                                <span class="text-gray-500">(belum direkod)</span>.
                                            @endif
                                        @else
                                            {{ $stage['hint'] }}
                                        @endif
                                    </p>
                                @elseif (! $stage['at'] && ! $isAlpView && ! $isCurrent)
                                    <p class="mt-1 text-sm text-gray-500">Menunggu langkah sebelumnya</p>
                                @endif
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
