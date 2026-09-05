@props(['position'])
@php $s = $position['summary']; @endphp

<div class="card p-5">
    <h3 class="mb-3 text-sm font-semibold text-gray-900">Kedudukan Bajet</h3>
    <dl class="space-y-2 text-sm">
        <div class="flex justify-between"><dt class="text-gray-500">Peruntukan</dt><dd class="font-medium text-navy-700"><x-money :value="$s->allocation" /></dd></div>
        <div class="flex justify-between"><dt class="text-gray-500">Committed</dt><dd class="text-amber-600"><x-money :value="$s->committed" /></dd></div>
        <div class="flex justify-between"><dt class="text-gray-500">Spent</dt><dd class="text-purple-600"><x-money :value="$s->spent" /></dd></div>
        <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="text-gray-600 font-medium">Ledger Available</dt><dd class="font-semibold text-green-600"><x-money :value="$position['ledger_available']" /></dd></div>
        <div class="flex justify-between"><dt class="text-gray-500">Pending Request Lain</dt><dd class="text-orange-600"><x-money :value="$position['other_pending'] " /></dd></div>
        <div class="flex justify-between"><dt class="text-gray-500">Permohonan Ini</dt><dd class="font-medium text-gray-900"><x-money :value="$position['this_request']" /></dd></div>
        <div class="flex justify-between border-t border-gray-100 pt-2">
            <dt class="text-gray-600 font-medium">Baki Selepas Permohonan</dt>
            <dd class="font-semibold {{ $position['balance_after']->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$position['balance_after']" /></dd>
        </div>
    </dl>

    @unless ($position['sufficient'])
        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800">
            Baki peruntukan tidak mencukupi untuk jumlah permohonan ini.
        </div>
    @endunless

    <p class="mt-3 text-[11px] text-gray-400">
        Pending Request BUKAN Committed. Ia tidak menjejaskan ledger sehingga diluluskan.
    </p>
</div>
