@extends('layouts.app')
@section('title', 'Semak Laporan — '.$application->application_number)
@section('heading', 'Semakan Laporan Aktiviti')
@section('subheading', $application->application_number.' · '.($application->recipient_name ?? $application->purpose))

@section('content')
    <x-page-shell>
        <div class="mb-5">
            <a href="{{ route('report-cards.review.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke giliran
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <x-page-card title="Maklumat Program" icon="clipboard">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">ALP</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $application->alp->ref_code }} — {{ $application->alp->name }}</dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Persatuan</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $application->recipient_name ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tarikh Program</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $application->program_date?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-royal-100 bg-gradient-to-br from-royal-50 to-navy-50 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-royal-600">Jumlah Sumbangan</dt>
                            <dd class="mt-1 text-xl font-bold text-navy-700"><x-money :value="$application->requested_amount" /></dd>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4 sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tujuan</dt>
                            <dd class="mt-1 whitespace-pre-line text-gray-900">{{ $application->purpose ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-page-card>

                @php
                    $reportDocs = $application->documents->filter(fn ($d) => in_array($d->document_type, [
                        \App\Enums\DocumentType::REPORT_CARD,
                        \App\Enums\DocumentType::LAPORAN_AKTIVITI,
                    ], true));
                @endphp

                <x-page-card title="Dokumen Laporan Aktiviti" icon="paper-clip">
                    @if ($reportDocs->isEmpty())
                        <p class="text-sm text-gray-500">Tiada dokumen laporan.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($reportDocs as $doc)
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $doc->document_type->label() }}</p>
                                        <p class="text-xs text-gray-500">{{ $doc->original_filename }} · {{ $doc->created_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <a href="{{ route('applications.documents.view', [$application, $doc]) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn-primary !py-1 !px-3 text-xs">
                                        Lihat
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-page-card>

                @if ($application->reportCardReviews->isNotEmpty())
                    <x-page-card title="Sejarah Semakan Laporan" icon="clock">
                        <div class="space-y-3">
                            @foreach ($application->reportCardReviews as $r)
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-medium text-gray-900">
                                            {{ $r->stage === 'admin_jp' ? 'Admin JP' : 'Pegawai JP' }}
                                        </span>
                                        <x-status-badge :label="$r->decision->label()" :classes="$r->decision->badgeClasses()" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $r->reviewed_at?->format('d/m/Y H:i') }} · {{ $r->reviewer?->name }}
                                    </p>
                                    @if ($r->comments)
                                        <p class="mt-2 text-gray-600">{{ $r->comments }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-page-card>
                @endif
            </div>

            <div>
                <x-page-card
                    title="{{ $fullJpDecision ? 'Keputusan Admin JP' : 'Pengesahan Pegawai JP' }}"
                    description="{{ $fullJpDecision ? 'Semak laporan aktiviti, kemudian hantar kepada Pegawai JP untuk pengesahan.' : 'Sahkan laporan aktiviti selepas semakan Admin JP.' }}"
                >
                    <div x-data="{ act: '{{ old('decision', $fullJpDecision ? '' : 'recommend') }}' }" class="space-y-5">
                        <form method="POST" action="{{ route('report-cards.review.store', $application) }}" class="space-y-5">
                            @csrf

                            @if ($fullJpDecision)
                                <x-field label="Keputusan" name="decision" :required="true">
                                    <select name="decision" class="inp" required x-model="act">
                                        <option value="">— Pilih —</option>
                                        @foreach (\App\Enums\ReviewDecision::cases() as $d)
                                            <option value="{{ $d->value }}" @selected(old('decision') === $d->value)>{{ $d->label() }}</option>
                                        @endforeach
                                    </select>
                                </x-field>

                                <x-field label="Ulasan / Catatan" name="comments" hint="Wajib jika dikembalikan untuk pembetulan.">
                                    <textarea name="comments" rows="4" class="inp">{{ old('comments') }}</textarea>
                                </x-field>

                                <button type="submit" class="btn-navy w-full">Hantar Keputusan</button>
                            @else
                                <input type="hidden" name="decision" :value="act">

                                <div class="flex gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1 text-sm">
                                    <button type="button" @click="act='recommend'" :class="act==='recommend' ? 'bg-navy-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="flex-1 rounded-lg px-2 py-2 font-medium transition">Sahkan Laporan</button>
                                    <button type="button" @click="act='return_for_revision'" :class="act==='return_for_revision' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="flex-1 rounded-lg px-2 py-2 font-medium transition">Kembalikan</button>
                                </div>

                                <x-field label="Ulasan" name="comments" hint="Wajib jika dikembalikan kepada ALP.">
                                    <textarea name="comments" rows="4" class="inp" placeholder="Ulasan pengesahan atau sebab pemulangan">{{ old('comments') }}</textarea>
                                </x-field>

                                <button type="submit" class="btn-navy w-full" x-text="act === 'return_for_revision' ? 'Kembalikan Untuk Pembetulan' : 'Sahkan Laporan Aktiviti'"></button>
                            @endif
                        </form>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">
                        @if ($fullJpDecision)
                            Selepas hantar, laporan masuk giliran Pegawai JP untuk pengesahan muktamad.
                        @else
                            Pengesahan Pegawai JP akan menandakan laporan sebagai diterima dan dimaklumkan kepada ALP.
                        @endif
                    </p>
                </x-page-card>
            </div>
        </div>
    </x-page-shell>
@endsection
