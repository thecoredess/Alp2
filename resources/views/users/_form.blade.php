@csrf
@isset($user) @method('PUT') @endisset

@php
    $selectedRole = old('role', $currentRole ?? '');
    $alpRoles = [\App\Enums\RoleName::ALP->value, \App\Enums\RoleName::URUSSETIA_ALP->value];
@endphp

<div x-data="{ role: '{{ $selectedRole }}', alpRoles: @js($alpRoles) }"
     class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    <x-field label="Nama" name="name" :required="true" class="sm:col-span-2">
        <input id="name" name="name" type="text" value="{{ old('name', $user->name ?? '') }}" required class="inp">
    </x-field>

    <x-field label="E-mel" name="email" :required="true">
        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required class="inp">
    </x-field>

    <x-field label="Unit / Jabatan" name="unit">
        <input id="unit" name="unit" type="text" value="{{ old('unit', $user->unit ?? '') }}" class="inp">
    </x-field>

    <x-field label="Peranan" name="role" :required="true">
        <select id="role" name="role" x-model="role" required class="inp">
            <option value="">— Pilih Peranan —</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </x-field>

    <x-field label="Kaitkan dengan ALP" name="alp_id" x-show="alpRoles.includes(role)" x-cloak
             hint="Wajib untuk peranan ALP / Urus Setia ALP.">
        <select id="alp_id" name="alp_id" class="inp">
            <option value="">— Pilih ALP —</option>
            @foreach ($alps as $alp)
                <option value="{{ $alp->id }}" @selected(old('alp_id', $user->alp_id ?? '') == $alp->id)>
                    {{ $alp->ref_code }} — {{ $alp->name }}
                </option>
            @endforeach
        </select>
    </x-field>
</div>

@unless(isset($user))
    <p class="mt-4 rounded-lg bg-gray-50 px-4 py-3 text-xs text-gray-500">
        Kata laluan sementara akan dijana secara automatik dan dipaparkan sekali sahaja selepas pengguna dicipta.
        Pengguna wajib menukarnya semasa log masuk pertama.
    </p>
@endunless

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('users.index') }}" class="btn-white">Batal</a>
    <button type="submit" class="btn-primary">{{ isset($user) ? 'Kemas Kini' : 'Cipta Pengguna' }}</button>
</div>
