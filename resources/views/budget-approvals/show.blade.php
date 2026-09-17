@extends('layouts.app')
@section('title', 'Kelulusan Bajet')
@section('heading', 'Kelulusan Cadangan Bajet')
@section('subheading', $request->request_type->label().' · '.$request->alp->ref_code.' · Tahun '.$request->financialYear->year)

@section('content')
    <div class="mb-5">
        <a href="{{ route('budget-approvals.queue') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke giliran
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-6">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Jenis</dt><dd class="text-gray-900">{{ $request->request_type->label() }}</dd></div>
                    <div><dt class="text-gray-500">Jumlah Cadangan</dt><dd class="font-semibold text-navy-700"><x-money :value="$request->amount" /></dd></div>
                    <div><dt class="text-gray-500">ALP</dt><dd class="text-gray-900">{{ $request->alp->ref_code }} — {{ $request->alp->name }}</dd></div>
                    <div><dt class="text-gray-500">Tahun Kewangan</dt><dd class="text-gray-900">{{ $request->financialYear->year }}</dd></div>
                    <div><dt class="text-gray-500">No. Rujukan</dt><dd class="font-mono text-gray-900">{{ $request->reference_number ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Maker</dt><dd class="text-gray-900">{{ $request->maker?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Dihantar Oleh</dt><dd class="text-gray-900">{{ $request->submitter?->name ?? '—' }} · {{ $request->submitted_at?->format('d/m/Y H:i') }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Sebab</dt><dd class="text-gray-900 whitespace-pre-line">{{ $request->reason ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Kedudukan & kesan --}}
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Kedudukan &amp; Kesan</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Peruntukan Semasa</dt><dd class="text-navy-700"><x-money :value="$summary->allocation" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Committed</dt><dd class="text-amber-600"><x-money :value="$summary->committed" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Spent</dt><dd class="text-purple-600"><x-money :value="$summary->spent" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Pending Application</dt><dd class="text-orange-600"><x-money :value="$pending" /></dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="text-gray-600 font-medium">Baki Peruntukan Diluluskan</dt><dd class="font-semibold text-green-600"><x-money :value="$available" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Baki Peruntukan Semasa</dt><dd class="{{ $projected->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$projected" /></dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="text-gray-500">Peruntukan Baharu</dt><dd class="font-semibold text-navy-700"><x-money :value="$newAllocation" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Projected Selepas</dt><dd class="font-semibold {{ $projectedAfter->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$projectedAfter" /></dd></div>
                </dl>
            </div>

            {{-- Tindakan --}}
            @if ($isMaker)
                <div class="card p-5 text-sm text-red-700 bg-red-50 border-red-200">
                    MAKER ≠ CHECKER: anda cipta/hantar cadangan ini, jadi anda <strong>tidak boleh</strong> meluluskannya.
                </div>
            @else
                <div class="card p-5" x-data="{ act: 'approve' }">
                    <div class="mb-3 flex gap-1 text-sm">
                        <button @click="act='approve'" :class="act==='approve' ? 'bg-navy-700 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Luluskan</button>
                        <button @click="act='return'" :class="act==='return' ? 'bg-orange-500 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Kembalikan</button>
                        <button @click="act='reject'" :class="act==='reject' ? 'bg-danger text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Tolak</button>
                    </div>

                    <form x-show="act==='approve'" method="POST" action="{{ route('budget-approvals.approve', $request) }}"
                          onsubmit="return confirm('Sahkan kelulusan? Transaksi ledger akan dicipta.')" class="space-y-3">
                        @csrf
                        <div class="rounded-lg bg-navy-50 px-3 py-2 text-xs text-navy-800">
                            Anda akan meluluskan <strong>{{ $request->request_type->label() }}</strong> berjumlah
                            <strong><x-money :value="$request->amount" /></strong>. Peruntukan baharu: <strong><x-money :value="$newAllocation" /></strong>.
                        </div>
                        <textarea name="comments" rows="2" class="inp" placeholder="Ulasan (pilihan)"></textarea>
                        <button class="btn-navy w-full">Luluskan &amp; Poskan Ledger</button>
                    </form>

                    <form x-show="act==='return'" x-cloak method="POST" action="{{ route('budget-approvals.return', $request) }}" class="space-y-3">
                        @csrf
                        <textarea name="comments" rows="3" required class="inp" placeholder="Sebab pembetulan (wajib)"></textarea>
                        <button class="btn-white w-full">Kembalikan Untuk Pembetulan</button>
                    </form>

                    <form x-show="act==='reject'" x-cloak method="POST" action="{{ route('budget-approvals.reject', $request) }}"
                          onsubmit="return confirm('Sahkan penolakan?')" class="space-y-3">
                        @csrf
                        <textarea name="comments" rows="3" required class="inp" placeholder="Sebab penolakan (wajib)"></textarea>
                        <button class="btn-danger w-full">Tolak Cadangan</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
