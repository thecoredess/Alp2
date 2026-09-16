@extends('layouts.app')
@section('title', 'Cadangan Bajet')
@section('heading', 'Cadangan Bajet')
@section('subheading', $request->request_type->label().' · '.$request->alp->ref_code.' · Tahun '.$request->financialYear->year)

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('budget-requests.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
        </a>
        <x-status-badge :label="$request->status->label()" :classes="$request->status->badgeClasses()" class="text-sm px-3 py-1" />
    </div>

    @if ($request->status === \App\Enums\BudgetRequestStatus::REVISION_REQUIRED && $request->return_reason)
        <div class="mb-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
            <p class="font-semibold">Pembetulan Diperlukan</p>
            <p>Sebab: {{ $request->return_reason }}</p>
            <p class="text-xs">Dikembalikan oleh: {{ $request->maker?->name }} · {{ $request->returned_at?->format('d/m/Y H:i') }}</p>
        </div>
    @endif
    @if ($request->status === \App\Enums\BudgetRequestStatus::REJECTED && $request->rejection_reason)
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Ditolak</p><p>Sebab: {{ $request->rejection_reason }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-6">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Jenis</dt><dd class="text-gray-900">{{ $request->request_type->label() }}</dd></div>
                    <div><dt class="text-gray-500">Jumlah</dt><dd class="font-semibold text-navy-700"><x-money :value="$request->amount" /></dd></div>
                    <div><dt class="text-gray-500">ALP</dt><dd class="text-gray-900">{{ $request->alp->ref_code }} — {{ $request->alp->name }}</dd></div>
                    <div><dt class="text-gray-500">Tahun Kewangan</dt><dd class="text-gray-900">{{ $request->financialYear->year }}</dd></div>
                    <div><dt class="text-gray-500">No. Rujukan</dt><dd class="font-mono text-gray-900">{{ $request->reference_number ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Maker</dt><dd class="text-gray-900">{{ $request->maker?->name ?? '—' }}</dd></div>
                    @if($request->approver)<div><dt class="text-gray-500">Diluluskan Oleh (Checker)</dt><dd class="text-gray-900">{{ $request->approver->name }}</dd></div>@endif
                    <div class="sm:col-span-2"><dt class="text-gray-500">Sebab / Catatan</dt><dd class="text-gray-900 whitespace-pre-line">{{ $request->reason ?? '—' }}</dd></div>
                </dl>
            </div>

            @if ($request->transaction)
                <div class="card p-5 border-green-200 bg-green-50">
                    <h3 class="text-sm font-semibold text-green-800">Transaksi Ledger Diposkan</h3>
                    <dl class="mt-2 space-y-1 text-sm text-green-900">
                        <div class="flex justify-between"><dt>Jenis</dt><dd>{{ $request->transaction->type->label() }}</dd></div>
                        <div class="flex justify-between"><dt>Jumlah</dt><dd><x-money :value="$request->transaction->amount" :signed="true" /></dd></div>
                        <div class="flex justify-between"><dt>Tarikh</dt><dd>{{ $request->transaction->created_at?->format('d/m/Y H:i') }}</dd></div>
                    </dl>
                </div>
            @endif

            {{-- Sejarah --}}
            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Sejarah</h3>
                @foreach ($request->histories as $h)
                    <div class="flex items-start gap-3 border-b border-gray-100 py-2 last:border-0 text-sm">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-navy-500"></span>
                        <div>
                            <p class="text-gray-900">{{ $h->from_status?->label() ?? 'Baharu' }} → <span class="font-medium">{{ $h->to_status->label() }}</span></p>
                            <p class="text-xs text-gray-500">{{ $h->created_at?->format('d/m/Y H:i') }} · {{ $h->changedBy?->name }} @if($h->remarks) · {{ $h->remarks }} @endif</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Panel kanan: kedudukan + tindakan --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Kedudukan Bajet ALP</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Peruntukan Semasa</dt><dd class="font-medium text-navy-700"><x-money :value="$position['summary']->allocation" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Committed</dt><dd class="text-amber-600"><x-money :value="$position['summary']->committed" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Spent</dt><dd class="text-purple-600"><x-money :value="$position['summary']->spent" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Pending Application</dt><dd class="text-orange-600"><x-money :value="$position['pending']" /></dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="text-gray-600 font-medium">Ledger Available</dt><dd class="font-semibold text-green-600"><x-money :value="$position['available']" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Projected Available</dt><dd class="{{ $position['projected']->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$position['projected']" /></dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="text-gray-500">Available Selepas</dt><dd class="font-semibold"><x-money :value="$position['available_after']" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Projected Selepas</dt><dd class="font-semibold {{ $position['projected_after']->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$position['projected_after']" /></dd></div>
                </dl>
                <p class="mt-2 text-[11px] text-gray-400">Nilai "Selepas" adalah anggaran jika cadangan diluluskan. Backend ialah sumber kebenaran.</p>
            </div>

            @can('update', $request)
                <div class="card p-5 space-y-2">
                    <a href="{{ route('budget-requests.edit', $request) }}" class="btn-white w-full">Kemaskini Draf</a>
                    <form method="POST" action="{{ route('budget-requests.submit', $request) }}"
                          onsubmit="return confirm('Hantar cadangan untuk kelulusan?')">
                        @csrf
                        <button class="btn-navy w-full">Hantar Untuk Kelulusan</button>
                    </form>
                </div>
            @elseif($request->status === \App\Enums\BudgetRequestStatus::PENDING_APPROVAL)
                <div class="card p-5 text-sm text-gray-500">Menunggu kelulusan checker. Anda tidak boleh meluluskan cadangan sendiri.</div>
            @endcan
        </div>
    </div>
@endsection
