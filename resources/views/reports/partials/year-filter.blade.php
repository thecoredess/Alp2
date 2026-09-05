{{-- Penapis tahun kewangan + ALP (jika management). Guna dalam <form method="GET">. --}}
<div>
    <label class="block text-xs text-gray-500">Tahun Kewangan</label>
    <select name="fy" class="inp" onchange="this.form.requestSubmit()">
        @foreach ($years as $y)
            <option value="{{ $y->id }}" @selected(($selectedYear ?? $year)?->id === $y->id)>{{ $y->year }} @if($y->is_active) (Aktif) @endif</option>
        @endforeach
    </select>
</div>
@if (($alps ?? collect())->isNotEmpty())
    <div>
        <label class="block text-xs text-gray-500">ALP</label>
        <select name="alp" class="inp" onchange="this.form.requestSubmit()">
            <option value="">Semua ALP</option>
            @foreach ($alps as $a)
                <option value="{{ $a->id }}" @selected((string) request('alp') === (string) $a->id)>{{ $a->ref_code }}</option>
            @endforeach
        </select>
    </div>
@endif
