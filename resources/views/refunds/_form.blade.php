@csrf
@isset($refund) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field label="Tarikh Refund" name="refund_date" :required="true">
        <input name="refund_date" type="date" value="{{ old('refund_date', isset($refund) ? $refund->refund_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="inp">
    </x-field>
    <x-field label="Nombor Rujukan" name="reference_number">
        <input name="reference_number" type="text" value="{{ old('reference_number', $refund->reference_number ?? '') }}" class="inp">
    </x-field>
    <x-field label="Jumlah Refund (RM)" name="amount" :required="true">
        <input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $refund->amount ?? '') }}" required class="inp">
    </x-field>
    <x-field label="Sebab Refund" name="reason" :required="true" class="sm:col-span-2">
        <textarea name="reason" rows="3" required class="inp">{{ old('reason', $refund->reason ?? '') }}</textarea>
    </x-field>
</div>

<div class="mt-4 rounded-lg bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">
    Baki boleh dipulangkan: <strong><x-money :value="$refundable->value()" /></strong>.
    Refund hanya akan diposkan ke ledger (transaksi REFUND baharu) <strong>selepas disahkan</strong> oleh checker.
    Perbelanjaan asal yang telah disahkan <strong>tidak dipadam atau disunting</strong>.
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ isset($refund) ? route('refunds.show', $refund) : route('expenses.show', $expense) }}" class="btn-white">Batal</a>
    <button class="btn-primary">{{ isset($refund) ? 'Kemas Kini' : 'Simpan Draf' }}</button>
</div>
