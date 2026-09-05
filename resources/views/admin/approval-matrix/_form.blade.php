@csrf
@isset($level) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field label="Nama Aras" name="name" :required="true" class="sm:col-span-2">
        <input name="name" type="text" value="{{ old('name', $level->name ?? '') }}" required class="inp">
    </x-field>

    <x-field label="Urutan" name="sequence" :required="true" hint="1 = paling awal.">
        <input name="sequence" type="number" min="1" value="{{ old('sequence', $level->sequence ?? '') }}" required class="inp">
    </x-field>

    <x-field label="Peranan Diperlukan" name="required_role" :required="true">
        <select name="required_role" class="inp" required>
            @foreach ($roles as $val => $label)
                <option value="{{ $val }}" @selected(old('required_role', $level->required_role ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </x-field>

    <x-field label="Jumlah Minimum (RM)" name="min_amount" :required="true">
        <input name="min_amount" type="number" step="0.01" min="0" value="{{ old('min_amount', $level->min_amount ?? '0.00') }}" required class="inp">
    </x-field>

    <x-field label="Jumlah Maksimum (RM)" name="max_amount" hint="Kosongkan untuk tiada had (∞).">
        <input name="max_amount" type="number" step="0.01" min="0" value="{{ old('max_amount', $level->max_amount ?? '') }}" class="inp">
    </x-field>

    <x-field label="Tahun Kewangan" name="financial_year_id" hint="Kosong = terpakai semua tahun.">
        <select name="financial_year_id" class="inp">
            <option value="">Semua Tahun</option>
            @foreach ($years as $y)
                <option value="{{ $y->id }}" @selected(old('financial_year_id', $level->financial_year_id ?? '') == $y->id)>{{ $y->year }}</option>
            @endforeach
        </select>
    </x-field>

    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="active" value="1" @checked(old('active', $level->active ?? true)) class="rounded border-gray-300 text-royal-500 focus:ring-royal-500">
            Aktif
        </label>
    </div>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('approval-matrix.index') }}" class="btn-white">Batal</a>
    <button type="submit" class="btn-primary">{{ isset($level) ? 'Kemas Kini' : 'Simpan' }}</button>
</div>
