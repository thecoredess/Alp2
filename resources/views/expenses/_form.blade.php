@csrf
@isset($expense) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field label="Tarikh Perbelanjaan" name="expense_date" :required="true">
        <input name="expense_date" type="date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="inp">
    </x-field>
    <x-field label="Nombor Rujukan" name="reference_number" :required="true">
        <input name="reference_number" type="text" value="{{ old('reference_number', $expense->reference_number ?? '') }}" required class="inp">
    </x-field>
    <x-field label="Penerima / Vendor" name="payee">
        <input name="payee" type="text" value="{{ old('payee', $expense->payee ?? '') }}" class="inp">
    </x-field>
    <x-field label="Jumlah (RM)" name="amount" :required="true">
        <input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $expense->amount ?? '') }}" required class="inp">
    </x-field>
    <x-field label="Keterangan" name="description" :required="true" class="sm:col-span-2">
        <input name="description" type="text" value="{{ old('description', $expense->description ?? '') }}" required class="inp">
    </x-field>
</div>

<div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
    Perbelanjaan hanya akan diposkan ke ledger <strong>selepas disahkan</strong> oleh pegawai kewangan (checker).
    Anda tidak boleh mengesahkan perbelanjaan yang anda cipta/hantar.
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ isset($expense) ? route('expenses.show', $expense) : route('projects.show', $project) }}" class="btn-white">Batal</a>
    <button class="btn-primary">{{ isset($expense) ? 'Kemas Kini' : 'Simpan Draf' }}</button>
</div>
