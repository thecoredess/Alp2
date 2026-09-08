@props(['statement'])
{{-- $statement: array of ['transaction' => BudgetTransaction, 'running_available' => float] --}}

<div class="card overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-4 py-3">Tarikh</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Rujukan</th>
                <th class="px-4 py-3">Keterangan</th>
                <th class="px-4 py-3 text-right">Jumlah</th>
                <th class="px-4 py-3 text-right">Baki Tersedia</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($statement as $row)
                @php $txn = $row['transaction']; $isNegative = \App\Support\Money::of((string) $txn->amount)->isNegative(); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $txn->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <x-status-badge :label="$txn->type->label()" :classes="$txn->type->badgeClasses()" />
                    </td>
                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $txn->reference_no ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $txn->description ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium {{ $isNegative ? 'text-danger' : 'text-gray-900' }}">
                        <x-money :value="$txn->amount" />
                    </td>
                    <td class="px-4 py-3 text-right text-gray-900">
                        <x-money :value="$row['running_available']" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
