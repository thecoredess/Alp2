@php
    $kpi = $contributionKpi['kpi'];
    $scopeLabel = $contributionKpi['scope_label'];
@endphp

<section class="mt-8" aria-labelledby="contribution-kpi-title">
    <div class="mb-4">
        <h2 id="contribution-kpi-title" class="text-lg font-semibold text-gray-900">Analisa Sumbangan</h2>
        <p class="mt-1 text-xs text-gray-500">
            {{ $scopeLabel }} ·
            {{ $contributionKpi['from']->format('d/m/Y') }} hingga {{ $contributionKpi['to']->format('d/m/Y') }}
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => 'Jumlah Sumbangan', 'value' => $kpi['total'], 'classes' => 'border-green-300 bg-green-50', 'iconClasses' => 'bg-cyan-100 text-cyan-700', 'icon' => 'wallet'],
            ['label' => 'Jumlah Sumbangan Dalam Proses', 'value' => $kpi['in_process'], 'classes' => 'border-orange-300 bg-orange-50', 'iconClasses' => 'bg-orange-100 text-orange-700', 'icon' => 'arrow-path'],
            ['label' => 'Jumlah Sumbangan Diluluskan', 'value' => $kpi['approved'], 'classes' => 'border-blue-300 bg-blue-50', 'iconClasses' => 'bg-blue-100 text-blue-700', 'icon' => 'banknotes'],
        ] as $card)
            <article class="flex items-center gap-4 rounded-2xl border-2 p-5 {{ $card['classes'] }}">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $card['iconClasses'] }}">
                    <x-icon :name="$card['icon']" class="h-7 w-7" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase leading-tight tracking-wide text-gray-700">{{ $card['label'] }}</p>
                    <p class="mt-1 text-xl font-bold text-gray-900 sm:text-2xl">RM {{ number_format($card['value'], 2) }}</p>
                    <p class="text-xs text-gray-500">({{ $scopeLabel }})</p>
                </div>
            </article>
        @endforeach
    </div>
</section>
