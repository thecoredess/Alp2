@csrf
@isset($year) @method('PUT') @endisset

<div class="space-y-5">
    <x-field label="Tahun" name="year" :required="true" hint="Contoh: 2026">
        <input id="year" name="year" type="number" min="2000" max="2100"
               value="{{ old('year', $year->year ?? '') }}" required class="inp">
    </x-field>

    <x-field label="Label" name="label" hint="Pilihan. Contoh: Tahun Kewangan 2026">
        <input id="label" name="label" type="text"
               value="{{ old('label', $year->label ?? '') }}" class="inp">
    </x-field>

    <x-field label="Catatan" name="remarks">
        <textarea id="remarks" name="remarks" rows="3" class="inp">{{ old('remarks', $year->remarks ?? '') }}</textarea>
    </x-field>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('financial-years.index') }}" class="btn-white">Batal</a>
    <button type="submit" class="btn-primary">{{ isset($year) ? 'Kemas Kini' : 'Simpan' }}</button>
</div>
