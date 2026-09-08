@php
    $ledgerAvailable = $summary->available();
    $projected = $ledgerAvailable->minus($otherPending)->minus($thisRequest);
    $approvedTotal = $approvedApplications->reduce(
        fn ($carry, $app) => $carry->plus($app->requestedAmountMoney()),
        \App\Support\Money::zero(),
    );
@endphp

<div class="space-y-5">
    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Ringkasan Ledger · {{ $application->alp->ref_code }}</p>
        <dl class="space-y-2 text-sm">
            <div class="flex items-center justify-between rounded-lg bg-navy-50 px-3 py-2">
                <dt class="text-gray-600">Peruntukan Tahunan</dt>
                <dd class="font-semibold text-navy-700"><x-money :value="$summary->allocation" /></dd>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2">
                <dt class="text-gray-600">Committed</dt>
                <dd class="font-semibold text-amber-700"><x-money :value="$summary->committed" /></dd>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-purple-50 px-3 py-2">
                <dt class="text-gray-600">Perbelanjaan Sebenar</dt>
                <dd class="font-semibold text-purple-700"><x-money :value="$summary->spent" /></dd>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-green-50 px-3 py-2.5">
                <dt class="text-gray-600">Ledger Available</dt>
                <dd class="font-semibold text-green-700"><x-money :value="$ledgerAvailable" /></dd>
            </div>
        </dl>
        @if ($summary->allocation->isPositive())
            <div class="mt-3">
                <div class="flex items-center justify-between text-[11px] text-gray-500">
                    <span>Penggunaan bajet</span>
                    <span>{{ $summary->utilisationPercent() }}%</span>
                </div>
                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-royal-500" style="width: {{ min(100, $summary->utilisationPercent()) }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Permohonan Semasa</p>
        <dl class="space-y-2 text-sm">
            <div class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2">
                <dt class="text-gray-600">Pending Lain</dt>
                <dd class="font-semibold text-orange-700"><x-money :value="$otherPending" /></dd>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                <dt class="text-gray-600">Permohonan Ini</dt>
                <dd class="font-semibold text-navy-700"><x-money :value="$thisRequest" /></dd>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2.5">
                <dt class="font-medium text-gray-600">Projected Available</dt>
                <dd class="font-semibold {{ $projected->isNegative() ? 'text-danger' : 'text-green-600' }}">
                    <x-money :value="$projected" />
                </dd>
            </div>
        </dl>
    </div>

    @if ($periodSummary)
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Kuota Tempoh · {{ $periodSummary['label'] }}</p>
            <dl class="grid grid-cols-3 gap-2 text-center text-xs">
                <div class="rounded-lg bg-gray-50 px-2 py-2">
                    <dt class="text-gray-500">Kuota</dt>
                    <dd class="mt-0.5 font-semibold text-navy-700"><x-money :value="$periodSummary['quota']" /></dd>
                </div>
                <div class="rounded-lg bg-amber-50 px-2 py-2">
                    <dt class="text-gray-500">Digunakan</dt>
                    <dd class="mt-0.5 font-semibold text-amber-700"><x-money :value="$periodSummary['used']" /></dd>
                </div>
                <div class="rounded-lg bg-green-50 px-2 py-2">
                    <dt class="text-gray-500">Baki</dt>
                    <dd class="mt-0.5 font-semibold text-green-700"><x-money :value="$periodSummary['remaining']" /></dd>
                </div>
            </dl>
        </div>
    @endif

    <div>
        <div class="mb-2 flex items-center justify-between">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                Permohonan Diluluskan ({{ $approvedApplications->count() }})
            </p>
            @if ($approvedApplications->isNotEmpty())
                <span class="text-xs font-medium text-green-700">Jumlah: <x-money :value="$approvedTotal" /></span>
            @endif
        </div>
        @if ($approvedApplications->isEmpty())
            <p class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-400">Tiada permohonan diluluskan tahun ini.</p>
        @else
            <div class="max-h-44 overflow-y-auto rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-100 text-xs">
                    <thead class="sticky top-0 bg-gray-50 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-3 py-2">No.</th>
                            <th class="px-3 py-2">Penerima</th>
                            <th class="px-3 py-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @foreach ($approvedApplications as $app)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <a href="{{ route('applications.show', $app) }}" class="font-mono text-royal-700 hover:underline">{{ $app->application_number }}</a>
                                </td>
                                <td class="max-w-[7rem] truncate px-3 py-2 text-gray-600" title="{{ $app->recipient_name }}">{{ $app->recipient_name ?? '—' }}</td>
                                <td class="px-3 py-2 text-right font-medium text-gray-900"><x-money :value="$app->requested_amount" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
            Pending Lain ({{ $pendingApplications->count() }})
        </p>
        @if ($pendingApplications->isEmpty())
            <p class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-400">Tiada permohonan pending lain.</p>
        @else
            <div class="max-h-44 overflow-y-auto rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-100 text-xs">
                    <thead class="sticky top-0 bg-gray-50 text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-3 py-2">No.</th>
                            <th class="px-3 py-2">Penerima</th>
                            <th class="px-3 py-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @foreach ($pendingApplications as $app)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <a href="{{ route('applications.show', $app) }}" class="font-mono text-royal-700 hover:underline">{{ $app->application_number }}</a>
                                </td>
                                <td class="max-w-[7rem] truncate px-3 py-2 text-gray-600" title="{{ $app->recipient_name }}">{{ $app->recipient_name ?? '—' }}</td>
                                <td class="px-3 py-2 text-right font-medium text-gray-900"><x-money :value="$app->requested_amount" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
