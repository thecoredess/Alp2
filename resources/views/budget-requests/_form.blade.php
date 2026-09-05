@csrf
@isset($request) @method('PUT') @endisset

@php
    $curType = old('request_type', isset($request) ? $request->request_type->value : ($preselect ?? ''));
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field label="Jenis Cadangan" name="request_type" :required="true">
        <select name="request_type" class="inp" required @isset($request) disabled @endisset>
            <option value="">— Pilih —</option>
            @foreach ($types as $val => $label)
                <option value="{{ $val }}" @selected($curType === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @isset($request)<input type="hidden" name="request_type" value="{{ $request->request_type->value }}">@endisset
    </x-field>

    <x-field label="Tahun Kewangan" name="financial_year_id" :required="true">
        <select name="financial_year_id" class="inp" required @isset($request) disabled @endisset>
            @foreach ($years as $y)
                <option value="{{ $y->id }}" @selected(old('financial_year_id', $request->financial_year_id ?? '') == $y->id)>{{ $y->year }}</option>
            @endforeach
        </select>
        @isset($request)<input type="hidden" name="financial_year_id" value="{{ $request->financial_year_id }}">@endisset
    </x-field>

    <x-field label="ALP" name="alp_id" :required="true">
        <select name="alp_id" class="inp" required @isset($request) disabled @endisset>
            <option value="">— Pilih ALP —</option>
            @foreach ($alps as $alp)
                <option value="{{ $alp->id }}" @selected(old('alp_id', $request->alp_id ?? request('alp')) == $alp->id)>{{ $alp->ref_code }} — {{ $alp->name }}</option>
            @endforeach
        </select>
        @isset($request)<input type="hidden" name="alp_id" value="{{ $request->alp_id }}">@endisset
    </x-field>

    <x-field label="Jumlah (RM)" name="amount" :required="true" hint="Magnitud positif; arah ditentukan oleh jenis.">
        <input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $request->amount ?? '') }}" required class="inp">
    </x-field>

    <x-field label="Nombor Rujukan" name="reference_number" class="sm:col-span-2">
        <input name="reference_number" type="text" value="{{ old('reference_number', $request->reference_number ?? '') }}" class="inp">
    </x-field>

    <x-field label="Sebab / Catatan / Asas Peruntukan" name="reason" class="sm:col-span-2" hint="Wajib untuk pelarasan.">
        <textarea name="reason" rows="3" class="inp">{{ old('reason', $request->reason ?? '') }}</textarea>
    </x-field>
</div>

<div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
    Peruntukan hanya akan berkuat kuasa <strong>selepas diluluskan</strong> oleh pegawai yang diberi kuasa (checker).
    Anda tidak boleh meluluskan cadangan anda sendiri (maker ≠ checker).
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('budget-requests.index') }}" class="btn-white">Batal</a>
    <button type="submit" class="btn-primary">{{ isset($request) ? 'Kemas Kini' : 'Simpan Draf' }}</button>
</div>
