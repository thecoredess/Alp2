@extends('layouts.app')
@section('title', 'Refund')
@section('heading', 'Refund / Pemulihan Dana')
@section('subheading', $refund->project->project_number.' · Perbelanjaan '.$refund->expense->reference_number)

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('expenses.show', $refund->expense) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke perbelanjaan
        </a>
        <x-status-badge :label="$refund->status->label()" :classes="$refund->status->badgeClasses()" class="text-sm px-3 py-1" />
    </div>

    @if ($refund->status === \App\Enums\RefundStatus::REJECTED && $refund->rejection_reason)
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><p class="font-semibold">Ditolak</p><p>Sebab: {{ $refund->rejection_reason }}</p></div>
    @endif
    @if ($refund->status === \App\Enums\RefundStatus::REVISION_REQUIRED && $refund->return_reason)
        <div class="mb-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800"><p class="font-semibold">Perlu Pembetulan</p><p>Sebab: {{ $refund->return_reason }}</p></div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-6">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Jumlah Refund</dt><dd class="font-semibold text-teal-700"><x-money :value="$refund->amount" /></dd></div>
                    <div><dt class="text-gray-500">Tarikh</dt><dd class="text-gray-900">{{ $refund->refund_date?->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500">Rujukan</dt><dd class="font-mono text-gray-900">{{ $refund->reference_number ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Perbelanjaan Asal</dt><dd class="text-gray-900"><x-money :value="$refund->expense->amount" /></dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Sebab</dt><dd class="text-gray-900">{{ $refund->reason }}</dd></div>
                    <div><dt class="text-gray-500">Maker</dt><dd class="text-gray-900">{{ $refund->maker?->name ?? '—' }}</dd></div>
                    @if($refund->verifier)<div><dt class="text-gray-500">Disahkan Oleh</dt><dd class="text-gray-900">{{ $refund->verifier->name }}</dd></div>@endif
                </dl>
            </div>

            @if ($refund->transaction)
                <div class="card p-5 border-teal-200 bg-teal-50">
                    <h3 class="text-sm font-semibold text-teal-800">Transaksi Ledger (REFUND)</h3>
                    <p class="mt-1 text-sm text-teal-900"><x-money :value="$refund->transaction->amount" /> · {{ $refund->transaction->created_at?->format('d/m/Y H:i') }}</p>
                    <p class="mt-1 text-xs text-teal-700">Transaksi baharu yang membalikkan sebahagian perbelanjaan — ledger asal kekal utuh.</p>
                </div>
            @endif

            @include('projects._documents', [
                'documents' => $refund->documents,
                'uploadRoute' => route('refunds.documents.store', $refund),
                'documentTypes' => $documentTypes,
                'canManage' => auth()->user()->can('manageDocuments', $refund),
                'title' => 'Dokumen Bukti Refund',
                'emptyText' => 'Belum ada bukti dimuat naik. Sekurang-kurangnya satu diperlukan sebelum penghantaran.',
            ])

            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Sejarah</h3>
                @foreach ($refund->histories as $h)
                    <div class="flex items-start gap-3 border-b border-gray-100 py-2 last:border-0 text-sm">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-navy-500"></span>
                        <div><p class="text-gray-900">{{ $h->from_status?->label() ?? 'Baharu' }} → <span class="font-medium">{{ $h->to_status->label() }}</span></p>
                        <p class="text-xs text-gray-500">{{ $h->created_at?->format('d/m/Y H:i') }} · {{ $h->changedBy?->name }} @if($h->remarks) · {{ $h->remarks }} @endif</p></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            {{-- Tindakan maker --}}
            @can('update', $refund)
                <div class="card p-5 space-y-2">
                    <a href="{{ route('refunds.edit', $refund) }}" class="btn-white w-full">Kemaskini</a>
                    <form method="POST" action="{{ route('refunds.submit', $refund) }}" onsubmit="return confirm('Hantar untuk pengesahan?')">@csrf<button class="btn-navy w-full">Hantar Untuk Pengesahan</button></form>
                </div>
            @endcan

            {{-- Tindakan checker --}}
            @if ($refund->status === \App\Enums\RefundStatus::PENDING_VERIFICATION)
                @if ($isMaker)
                    <div class="card p-5 text-sm text-red-700 bg-red-50 border-red-200">MAKER ≠ CHECKER: anda tidak boleh mengesahkan refund sendiri.</div>
                @else
                    @can('verify', $refund)
                        <div class="card p-5" x-data="{ act: 'verify' }">
                            <div class="mb-3 flex gap-1 text-sm">
                                <button @click="act='verify'" :class="act==='verify' ? 'bg-navy-700 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Sahkan</button>
                                <button @click="act='return'" :class="act==='return' ? 'bg-orange-500 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Kembali</button>
                                <button @click="act='reject'" :class="act==='reject' ? 'bg-danger text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Tolak</button>
                            </div>
                            <form x-show="act==='verify'" method="POST" action="{{ route('refunds.verify', $refund) }}" onsubmit="return confirm('Sahkan & poskan REFUND ke ledger?')">@csrf<button class="btn-navy w-full">Sahkan &amp; Poskan Ledger</button></form>
                            <form x-show="act==='return'" x-cloak method="POST" action="{{ route('refunds.return', $refund) }}" class="space-y-2">@csrf<textarea name="comments" rows="3" required class="inp" placeholder="Sebab pembetulan (wajib)"></textarea><button class="btn-white w-full">Kembalikan</button></form>
                            <form x-show="act==='reject'" x-cloak method="POST" action="{{ route('refunds.reject', $refund) }}" class="space-y-2" onsubmit="return confirm('Tolak refund?')">@csrf<textarea name="comments" rows="3" required class="inp" placeholder="Sebab penolakan (wajib)"></textarea><button class="btn-danger w-full">Tolak</button></form>
                        </div>
                    @endcan
                @endif
            @endif
        </div>
    </div>
@endsection
