@csrf
@isset($alp) @method('PUT') @endisset

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field label="Nama" name="name" :required="true" class="sm:col-span-2">
        <input id="name" name="name" type="text" value="{{ old('name', $alp->name ?? '') }}" required class="inp">
    </x-field>

    <x-field label="Kod Rujukan" name="ref_code" :required="true" hint="Contoh: ALP-01">
        <input id="ref_code" name="ref_code" type="text" value="{{ old('ref_code', $alp->ref_code ?? '') }}" required class="inp">
    </x-field>

    <x-field label="Portfolio / Zon / Kawasan" name="portfolio_zone">
        <input id="portfolio_zone" name="portfolio_zone" type="text" value="{{ old('portfolio_zone', $alp->portfolio_zone ?? '') }}" class="inp">
    </x-field>

    <x-field label="Tarikh Mula Lantikan" name="appointment_start">
        <input id="appointment_start" name="appointment_start" type="date"
               value="{{ old('appointment_start', isset($alp) && $alp->appointment_start ? $alp->appointment_start->format('Y-m-d') : '') }}" class="inp">
    </x-field>

    <x-field label="Tarikh Tamat Lantikan" name="appointment_end">
        <input id="appointment_end" name="appointment_end" type="date"
               value="{{ old('appointment_end', isset($alp) && $alp->appointment_end ? $alp->appointment_end->format('Y-m-d') : '') }}" class="inp">
    </x-field>

    <x-field label="Telefon" name="phone">
        <input id="phone" name="phone" type="text" value="{{ old('phone', $alp->phone ?? '') }}" class="inp">
    </x-field>

    <x-field label="E-mel" name="email">
        <input id="email" name="email" type="email" value="{{ old('email', $alp->email ?? '') }}" class="inp">
    </x-field>

    <x-field label="Alamat" name="address" class="sm:col-span-2">
        <input id="address" name="address" type="text" value="{{ old('address', $alp->address ?? '') }}" class="inp">
    </x-field>

    <x-field label="Catatan" name="remarks" class="sm:col-span-2">
        <textarea id="remarks" name="remarks" rows="3" class="inp">{{ old('remarks', $alp->remarks ?? '') }}</textarea>
    </x-field>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ isset($alp) ? route('alps.show', $alp) : route('alps.index') }}" class="btn-white">Batal</a>
    <button type="submit" class="btn-primary">{{ isset($alp) ? 'Kemas Kini' : 'Simpan' }}</button>
</div>
