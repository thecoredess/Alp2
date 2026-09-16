@extends('layouts.app')
@section('title', 'Laras Peruntukan')
@section('heading', 'Laras Peruntukan')
@section('subheading', $allocation->alp->ref_code.' · Tahun '.$allocation->financialYear->year)

@section('content')
    <div class="mb-4 max-w-xl">
        <x-budget-cards :summary="$summary" />
        @if ($entitlement)
            <p class="mt-2 text-xs text-gray-500">
                Siling kelayakan (lantikan): <strong>RM {{ number_format((float) $entitlement->value(), 2) }}</strong>
            </p>
        @endif
    </div>

    <form method="POST" action="{{ route('allocations.adjust.store', $allocation) }}" class="card max-w-xl space-y-5 p-6">
        @csrf

        <x-field label="Arah Pelarasan" name="direction" :required="true">
            <select id="direction" name="direction" class="inp" required>
                <option value="increase" @selected(old('direction') === 'increase')>Tambah peruntukan</option>
                <option value="decrease" @selected(old('direction') === 'decrease')>Kurangkan peruntukan</option>
            </select>
        </x-field>

        <x-field label="Jumlah (RM)" name="amount" :required="true">
            <input id="amount" name="amount" type="number" step="0.01" min="0.01" class="inp"
                   value="{{ old('amount') }}" required>
            <p class="mt-1 text-xs text-gray-500">Pengurangan tidak boleh menjadikan peruntukan di bawah committed + spent.</p>
        </x-field>

        <x-field label="No. Rujukan" name="reference_no">
            <input id="reference_no" name="reference_no" type="text" class="inp" value="{{ old('reference_no') }}">
        </x-field>

        <x-field label="Catatan" name="remarks">
            <textarea id="remarks" name="remarks" rows="3" class="inp">{{ old('remarks') }}</textarea>
        </x-field>

        <div class="flex gap-2">
            <a href="{{ route('allocations.show', $allocation) }}" class="btn-white">Batal</a>
            <button type="submit" class="btn-primary">Rekod Pelarasan</button>
        </div>
    </form>
@endsection
