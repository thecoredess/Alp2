@extends('layouts.app')
@section('title', 'Perbelanjaan')
@section('heading', 'Perbelanjaan Projek')
@section('subheading', $expense->project->project_number.' · '.$expense->reference_number)

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('projects.show', $expense->project) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke projek
        </a>
        <x-status-badge :label="$expense->status->label()" :classes="$expense->status->badgeClasses()" class="text-sm px-3 py-1" />
    </div>

    @if ($expense->status === \App\Enums\ProjectExpenseStatus::REJECTED && $expense->rejection_reason)
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><p class="font-semibold">Ditolak</p><p>Sebab: {{ $expense->rejection_reason }}</p></div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-6">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Jumlah</dt><dd class="font-semibold text-navy-700"><x-money :value="$expense->amount" /></dd></div>
                    <div><dt class="text-gray-500">Tarikh</dt><dd class="text-gray-900">{{ $expense->expense_date?->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500">Rujukan</dt><dd class="font-mono text-gray-900">{{ $expense->reference_number }}</dd></div>
                    <div><dt class="text-gray-500">Penerima</dt><dd class="text-gray-900">{{ $expense->payee ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Keterangan</dt><dd class="text-gray-900">{{ $expense->description }}</dd></div>
                    <div><dt class="text-gray-500">Maker</dt><dd class="text-gray-900">{{ $expense->maker?->name ?? '—' }}</dd></div>
                    @if($expense->verifier)<div><dt class="text-gray-500">Disahkan Oleh</dt><dd class="text-gray-900">{{ $expense->verifier->name }}</dd></div>@endif
                </dl>
            </div>

            @if ($expense->transaction)
                <div class="card p-5 border-green-200 bg-green-50">
                    <h3 class="text-sm font-semibold text-green-800">Transaksi Lejar (EXPENDITURE)</h3>
                    <p class="mt-1 text-sm text-green-900"><x-money :value="$expense->transaction->amount" /> · {{ $expense->transaction->created_at?->format('d/m/Y H:i') }}</p>
                </div>
            @endif

            @include('projects._documents', [
                'documents' => $expense->documents,
                'uploadRoute' => route('expenses.documents.store', $expense),
                'documentTypes' => \App\Enums\ProjectDocumentType::forCategory('expense_evidence'),
                'canManage' => auth()->user()->can('manageDocuments', $expense),
                'title' => 'Dokumen Bukti Perbelanjaan',
                'emptyText' => 'Belum ada bukti dimuat naik. Sekurang-kurangnya satu diperlukan sebelum penghantaran.',
            ])

            {{-- Refund bagi perbelanjaan yang telah disahkan --}}
            @if ($expense->status === \App\Enums\ProjectExpenseStatus::VERIFIED && $expense->refunds->isNotEmpty())
                <div class="card p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Refund / Pemulihan Dana</h3>
                        <span class="text-xs text-gray-500">Disahkan: <x-money :value="$expense->verifiedRefundTotal()->value()" /></span>
                    </div>
                    @foreach ($expense->refunds as $rf)
                        <a href="{{ route('refunds.show', $rf) }}" class="flex items-center justify-between border-b border-gray-100 py-2 last:border-0 text-sm hover:bg-gray-50">
                            <span class="text-gray-900"><x-money :value="$rf->amount" /> · {{ $rf->refund_date?->format('d/m/Y') }}</span>
                            <x-status-badge :label="$rf->status->label()" :classes="$rf->status->badgeClasses()" />
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Sejarah</h3>
                @foreach ($expense->histories as $h)
                    <div class="flex items-start gap-3 border-b border-gray-100 py-2 last:border-0 text-sm">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-navy-500"></span>
                        <div><p class="text-gray-900">{{ $h->from_status?->label() ?? 'Baharu' }} → <span class="font-medium">{{ $h->to_status->label() }}</span></p>
                        <p class="text-xs text-gray-500">{{ $h->created_at?->format('d/m/Y H:i') }} · {{ $h->changedBy?->name }} @if($h->remarks) · {{ $h->remarks }} @endif</p></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Kedudukan Kewangan Projek</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Diluluskan</dt><dd class="text-navy-700"><x-money :value="$finance['approved']" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Belanja Kasar</dt><dd class="text-purple-600"><x-money :value="$finance['gross']" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Refund Disahkan</dt><dd class="text-teal-600">(<x-money :value="$finance['refunded']" />)</dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="text-gray-500">Belanja Bersih</dt><dd class="font-semibold text-purple-700"><x-money :value="$finance['spent']" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Baki Diluluskan</dt><dd class="font-semibold text-amber-600"><x-money :value="$finance['outstanding']" /></dd></div>
                </dl>
            </div>

            @if ($expense->status === \App\Enums\ProjectExpenseStatus::VERIFIED && auth()->user()->can('refunds.create') && ! $expense->project->isClosed() && $expense->refundableRemaining()->isPositive())
                <div class="card p-5">
                    <p class="mb-2 text-sm text-gray-600">Baki boleh dipulangkan: <span class="font-semibold text-navy-700"><x-money :value="$expense->refundableRemaining()->value()" /></span></p>
                    <a href="{{ route('refunds.create', $expense) }}" class="btn-white w-full">Buat Refund</a>
                </div>
            @endif

            {{-- Tindakan maker --}}
            @can('update', $expense)
                <div class="card p-5 space-y-2">
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn-white w-full">Kemaskini</a>
                    <form method="POST" action="{{ route('expenses.submit', $expense) }}" onsubmit="return confirm('Hantar untuk pengesahan?')">@csrf<button class="btn-navy w-full">Hantar Untuk Pengesahan</button></form>
                </div>
            @endcan

            {{-- Tindakan checker --}}
            @if ($expense->status === \App\Enums\ProjectExpenseStatus::PENDING_VERIFICATION)
                @if ($isMaker)
                    <div class="card p-5 text-sm text-red-700 bg-red-50 border-red-200">MAKER ≠ CHECKER: anda tidak boleh mengesahkan perbelanjaan sendiri.</div>
                @else
                    @can('verify', $expense)
                        <div class="card p-5" x-data="{ act: 'verify' }">
                            <div class="mb-3 flex gap-1 text-sm">
                                <button @click="act='verify'" :class="act==='verify' ? 'bg-navy-700 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Sahkan</button>
                                <button @click="act='return'" :class="act==='return' ? 'bg-orange-500 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Kembali</button>
                                <button @click="act='reject'" :class="act==='reject' ? 'bg-danger text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Tolak</button>
                            </div>
                            <form x-show="act==='verify'" method="POST" action="{{ route('expenses.verify', $expense) }}" onsubmit="return confirm('Sahkan & poskan ke lejar?')">@csrf<button class="btn-navy w-full">Sahkan &amp; Poskan Lejar</button></form>
                            <form x-show="act==='return'" x-cloak method="POST" action="{{ route('expenses.return', $expense) }}" class="space-y-2">@csrf<textarea name="comments" rows="3" required class="inp" placeholder="Sebab pembetulan (wajib)"></textarea><button class="btn-white w-full">Kembalikan</button></form>
                            <form x-show="act==='reject'" x-cloak method="POST" action="{{ route('expenses.reject', $expense) }}" class="space-y-2" onsubmit="return confirm('Tolak perbelanjaan?')">@csrf<textarea name="comments" rows="3" required class="inp" placeholder="Sebab penolakan (wajib)"></textarea><button class="btn-danger w-full">Tolak</button></form>
                        </div>
                    @endcan
                @endif
            @endif
        </div>
    </div>
@endsection
