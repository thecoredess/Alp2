@extends('layouts.app')
@section('title', 'Kelulusan — '.$application->application_number)
@section('heading', 'Kelulusan Permohonan')
@section('subheading', $application->application_number.' · '.$application->project_title)

@php
    $required = $progress['required'];
    $approvedCount = $progress['approvedCount'];
    $nextLevel = $progress['nextLevel'];
    $isFinalNext = $nextLevel && ($approvedCount + 1) === $required->count();
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ route('approvals.queue') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke giliran
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            {{-- Ringkasan --}}
            <div class="card p-6 space-y-4">
                <x-urs-tbl10-summary
                    :application="$application"
                    :snapshot="$tbl10Snapshot"
                />
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">No. Permohonan</dt><dd class="font-mono text-gray-900">{{ $application->application_number }}</dd></div>
                    <div><dt class="text-gray-500">ALP</dt><dd class="text-gray-900">{{ $application->alp->ref_code }} — {{ $application->alp->name }}</dd></div>
                    <div><dt class="text-gray-500">Jenis</dt><dd class="text-gray-900">{{ $application->application_type->label() }}</dd></div>
                    <div><dt class="text-gray-500">Jumlah Dipohon</dt><dd class="font-semibold text-navy-700"><x-money :value="$application->requested_amount" /></dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Projek</dt><dd class="text-gray-900">{{ $application->project_title }}</dd></div>
                </dl>
            </div>

            {{-- Semakan --}}
            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Semakan</h3>
                @forelse ($application->reviews->where('revision_number', $application->revision_number) as $r)
                    <div class="flex items-center justify-between border-b border-gray-100 py-2 text-sm last:border-0">
                        <span class="text-gray-700">{{ $r->review_type->label() }} · {{ $r->reviewer?->name }}</span>
                        <x-status-badge :label="$r->decision->label()" :classes="$r->decision->badgeClasses()" />
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Tiada rekod semakan.</p>
                @endforelse
            </div>

            {{-- Sejarah kelulusan --}}
            @if ($application->approvals->isNotEmpty())
                <div class="card p-5">
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Sejarah Kelulusan</h3>
                    @foreach ($application->approvals as $a)
                        <div class="flex items-center justify-between border-b border-gray-100 py-2 text-sm last:border-0">
                            <span class="text-gray-700">{{ $a->approvalLevel?->name ?? '—' }} · {{ $a->approver?->name }}</span>
                            <x-status-badge :label="$a->decision->label()" :classes="$a->decision->badgeClasses()" />
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Dokumen --}}
            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Dokumen</h3>
                @forelse ($application->documents as $doc)
                    <div class="flex justify-between py-1 text-sm">
                        <span class="text-gray-700">{{ $doc->document_type->label() }} — {{ $doc->original_filename }}</span>
                        <a href="{{ route('applications.documents.download', [$application, $doc]) }}" class="text-royal-600 hover:text-royal-700 text-xs font-medium">Muat Turun</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Tiada dokumen.</p>
                @endforelse
            </div>
        </div>

        {{-- Panel keputusan --}}
        <div class="space-y-4" x-data="{ act: 'approve' }">
            {{-- Aras kelulusan --}}
            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Aras Kelulusan</h3>
                <ol class="space-y-1 text-sm">
                    @foreach ($required as $i => $lvl)
                        @php $done = $i < $approvedCount; $isNext = $i === $approvedCount; @endphp
                        <li class="flex items-center gap-2">
                            <span class="{{ $done ? 'text-green-600' : ($isNext ? 'text-royal-600' : 'text-gray-300') }}">{{ $done ? '✓' : ($isNext ? '●' : '○') }}</span>
                            <span class="{{ $isNext ? 'font-medium text-gray-900' : 'text-gray-600' }}">{{ $lvl->name }}</span>
                            <span class="ml-auto text-xs text-gray-400">{{ \App\Enums\RoleName::from($lvl->required_role)->label() }}</span>
                        </li>
                    @endforeach
                </ol>
                <p class="mt-2 text-[11px] text-gray-400">Aras {{ $approvedCount }} / {{ $required->count() }} diluluskan. Komitmen dicipta pada aras terakhir sahaja.</p>
            </div>

            {{-- Kesan kewangan --}}
            <div class="card p-5">
                <h3 class="mb-2 text-sm font-semibold text-gray-900">Kesan Kelulusan</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Jumlah</dt><dd class="font-semibold text-navy-700"><x-money :value="$thisRequest" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Ledger Available</dt><dd class="text-green-600"><x-money :value="$ledgerAvailable" /></dd></div>
                    @if ($isFinalNext)
                        <div class="flex justify-between"><dt class="text-gray-500">Pending Request</dt><dd class="text-orange-600">−<x-money :value="$thisRequest" /></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Committed</dt><dd class="text-amber-600">+<x-money :value="$thisRequest" /></dd></div>
                        <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="font-medium text-gray-600">Ledger Available Selepas</dt><dd class="font-semibold {{ $afterCommitAvailable->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$afterCommitAvailable" /></dd></div>
                    @else
                        <p class="text-xs text-gray-500">Ini bukan aras terakhir — komitmen belum dicipta.</p>
                    @endif
                </dl>
            </div>

            {{-- Tindakan --}}
            <div class="card p-5">
                <div class="mb-3 flex gap-1 text-sm">
                    <button @click="act='approve'" :class="act==='approve' ? 'bg-navy-700 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Luluskan</button>
                    <button @click="act='return'" :class="act==='return' ? 'bg-orange-500 text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Kembalikan</button>
                    <button @click="act='reject'" :class="act==='reject' ? 'bg-danger text-white' : 'text-gray-600'" class="flex-1 rounded-lg px-2 py-1.5">Tolak</button>
                </div>

                {{-- Luluskan --}}
                <form x-show="act==='approve'" method="POST" action="{{ route('approvals.approve', $application) }}"
                      onsubmit="return confirm('Sahkan kelulusan aras ini?')" class="space-y-3">
                    @csrf
                    <textarea name="comments" rows="3" class="inp" placeholder="Ulasan (pilihan)">{{ old('comments') }}</textarea>
                    <button class="btn-navy w-full">
                        @if ($isFinalNext) Luluskan &amp; Cipta Komitmen @else Luluskan Aras Ini @endif
                    </button>
                </form>

                {{-- Kembalikan --}}
                <form x-show="act==='return'" x-cloak method="POST" action="{{ route('approvals.return', $application) }}" class="space-y-3">
                    @csrf
                    <textarea name="comments" rows="3" required class="inp" placeholder="Sebab pembetulan (wajib)">{{ old('comments') }}</textarea>
                    <button class="btn-white w-full">Kembalikan Untuk Pembetulan</button>
                </form>

                {{-- Tolak --}}
                <form x-show="act==='reject'" x-cloak method="POST" action="{{ route('approvals.reject', $application) }}"
                      onsubmit="return confirm('Sahkan penolakan permohonan ini?')" class="space-y-3">
                    @csrf
                    <textarea name="comments" rows="3" required class="inp" placeholder="Sebab penolakan (wajib)">{{ old('comments') }}</textarea>
                    <button class="btn-danger w-full">Tolak Permohonan</button>
                </form>
            </div>
        </div>
    </div>
@endsection
