@props(['finance'])

<div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="card p-5">
        <p class="text-sm text-gray-500">Jumlah Diluluskan</p>
        <p class="mt-2 text-2xl font-semibold text-navy-700"><x-money :value="$finance['approved']" /></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-gray-500">Perbelanjaan Bersih</p>
        <p class="mt-2 text-2xl font-semibold text-purple-600"><x-money :value="$finance['spent']" /></p>
        @if(isset($finance['refunded']) && $finance['refunded']->isPositive())
            <p class="mt-1 text-[11px] text-teal-600">Kasar <x-money :value="$finance['gross']" /> − Refund <x-money :value="$finance['refunded']" /></p>
        @endif
    </div>
    <div class="card p-5">
        <p class="text-sm text-gray-500">Baki Komitmen</p>
        <p class="mt-2 text-2xl font-semibold text-amber-600"><x-money :value="$finance['outstanding']" /></p>
    </div>
    <div class="card p-5">
        <p class="text-sm text-gray-500">Dilepaskan / Jimat</p>
        <p class="mt-2 text-2xl font-semibold text-teal-600"><x-money :value="$finance['released']" /></p>
    </div>
</div>
<p class="mt-2 text-[11px] text-gray-400">Rekonsiliasi: Diluluskan = Baki Komitmen + Perbelanjaan + Dilepaskan (dari ledger).</p>
