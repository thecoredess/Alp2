@extends('layouts.app')
@section('title', 'Set Peruntukan')
@section('heading', 'Set Peruntukan Awal')
@section('subheading', 'Admin JP — tetapkan peruntukan terus ke lejar (URS Fasa 7.12)')

@section('content')
    <div class="mb-4 max-w-xl rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
        Tiada maker-checker. Transaksi <strong>INITIAL_ALLOCATION</strong> dicipta terus.
        Polisi sumbangan dikuatkuasakan jika diaktifkan.
    </div>

    <form method="POST" action="{{ route('allocations.store') }}" class="card max-w-xl space-y-5 p-6">
        @csrf

        <x-field label="Tahun Kewangan" name="financial_year_id" :required="true">
            <select id="financial_year_id" name="financial_year_id" class="inp" required>
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected(old('financial_year_id', $year?->id) == $y->id)>
                        {{ $y->year }} @if($y->is_active)(Aktif)@endif · {{ $y->status->label() }}
                    </option>
                @endforeach
            </select>
        </x-field>

        <x-field label="ALP" name="alp_id" :required="true">
            <select id="alp_id" name="alp_id" class="inp" required>
                <option value="">— Pilih ALP —</option>
                @foreach ($alps as $alp)
                    <option value="{{ $alp->id }}"
                            data-cap="{{ $entitlements[$alp->id] ?? '' }}"
                            @selected(old('alp_id', $selectedAlpId) == $alp->id)>
                        {{ $alp->ref_code }} — {{ $alp->name }}
                        @if(isset($entitlements[$alp->id]))
                            (siling kelayakan: RM {{ number_format((float) $entitlements[$alp->id], 2) }})
                        @endif
                    </option>
                @endforeach
            </select>
            @if ($alps->isEmpty())
                <p class="mt-1 text-xs text-amber-700">Semua ALP aktif sudah mempunyai peruntukan bagi tahun ini — guna Laras.</p>
            @endif
        </x-field>

        <x-field label="Jumlah Peruntukan (RM)" name="amount" :required="true">
            <input id="amount" name="amount" type="number" step="0.01" min="0.01" class="inp"
                   value="{{ old('amount') }}" required>
            <p id="cap-hint" class="mt-1 text-xs text-gray-500"></p>
        </x-field>

        <x-field label="No. Rujukan" name="reference_no">
            <input id="reference_no" name="reference_no" type="text" class="inp" value="{{ old('reference_no') }}">
        </x-field>

        <x-field label="Catatan" name="remarks">
            <textarea id="remarks" name="remarks" rows="3" class="inp">{{ old('remarks') }}</textarea>
        </x-field>

        <div class="flex gap-2">
            <a href="{{ route('allocations.index') }}" class="btn-white">Batal</a>
            <button type="submit" class="btn-primary" @disabled($alps->isEmpty())>Simpan Peruntukan</button>
        </div>
    </form>

    <script>
        (function () {
            var sel = document.getElementById('alp_id');
            var hint = document.getElementById('cap-hint');
            function sync() {
                var opt = sel.options[sel.selectedIndex];
                var cap = opt && opt.getAttribute('data-cap');
                hint.textContent = cap ? ('Kelayakan mengikut lantikan: RM ' + Number(cap).toLocaleString('en-MY', {minimumFractionDigits: 2})) : '';
            }
            sel?.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
