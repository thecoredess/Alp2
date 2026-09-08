@props(['periodSummary', 'year' => null])

@if ($periodSummary)
    <div {{ $attributes->merge(['class' => '']) }}>
        <h3 class="mb-3 text-sm font-semibold text-gray-900">Kuota Tempoh Semasa · {{ $periodSummary['label'] }}@if($year)<span class="font-normal text-gray-400"> · {{ $year }}</span>@endif</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="card p-5">
                <p class="text-sm text-gray-500">Kuota tempoh</p>
                <p class="mt-2 text-2xl font-semibold text-navy-700"><x-money :value="$periodSummary['quota']" /></p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-gray-500">Digunakan (pending + diluluskan)</p>
                <p class="mt-2 text-2xl font-semibold text-amber-600"><x-money :value="$periodSummary['used']" /></p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-gray-500">Baki tempoh</p>
                <p class="mt-2 text-2xl font-semibold text-green-600"><x-money :value="$periodSummary['remaining']" /></p>
                <p class="mt-1 text-xs text-gray-400">Luput {{ $periodSummary['ends_at']->format('d/m/Y') }} · tiada bawa ke hadapan</p>
            </div>
        </div>
    </div>
@endif
