@extends('layouts.app')
@section('title', 'Semakan — '.$application->application_number)
@section('heading', $reviewType->label())
@section('subheading', $application->application_number.' · '.$application->project_title)

@php
    $ledgerAvailable = $summary->available();
    $projected = $ledgerAvailable->minus($otherPending)->minus($thisRequest);
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ route('reviews.'.$reviewType->value) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke giliran
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            {{-- Ringkasan --}}
            <div class="card p-6">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Maklumat Permohonan</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">ALP</dt><dd class="text-gray-900">{{ $application->alp->ref_code }} — {{ $application->alp->name }}</dd></div>
                    <div><dt class="text-gray-500">Jenis</dt><dd class="text-gray-900">{{ $application->application_type->label() }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Objektif</dt><dd class="text-gray-900 whitespace-pre-line">{{ $application->objectives ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Skop</dt><dd class="text-gray-900 whitespace-pre-line">{{ $application->scope ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Lokasi</dt><dd class="text-gray-900">{{ $application->location ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Jumlah Dipohon</dt><dd class="font-semibold text-navy-700"><x-money :value="$application->requested_amount" /></dd></div>
                </dl>
            </div>

            {{-- Bajet --}}
            <div class="card overflow-x-auto">
                <div class="px-4 pt-4 text-sm font-semibold text-gray-900">Pecahan Bajet</div>
                <table class="mt-2 min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-2">Item</th><th class="px-4 py-2 text-right">Qty</th><th class="px-4 py-2 text-right">Harga</th><th class="px-4 py-2 text-right">Jumlah</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($application->budgetItems as $item)
                            <tr><td class="px-4 py-2 text-gray-900">{{ $item->description }}</td>
                            <td class="px-4 py-2 text-right text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right text-gray-600"><x-money :value="$item->unit_cost" /></td>
                            <td class="px-4 py-2 text-right font-medium"><x-money :value="$item->total" /></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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

            {{-- Sejarah semakan --}}
            @if ($application->reviews->isNotEmpty())
                <div class="card p-5">
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Sejarah Semakan</h3>
                    @foreach ($application->reviews as $r)
                        <div class="border-b border-gray-100 py-2 text-sm last:border-0">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-800">{{ $r->review_type->label() }}</span>
                                <x-status-badge :label="$r->decision->label()" :classes="$r->decision->badgeClasses()" />
                            </div>
                            <p class="text-xs text-gray-500">{{ $r->reviewed_at?->format('d/m/Y H:i') }} · {{ $r->reviewer?->name }} · Pusingan {{ $r->revision_number }}</p>
                            @if ($r->comments)<p class="mt-1 text-gray-600">{{ $r->comments }}</p>@endif
                            @if (is_array($r->checklist) && $r->checklist !== [])
                                <ul class="mt-2 space-y-0.5 text-xs text-gray-500">
                                    @foreach (\App\Support\JpReviewChecklist::items() as $ck => $clabel)
                                        @if (isset($r->checklist[$ck]))
                                            <li>
                                                {{ $r->checklist[$ck] === 'lengkap' ? '✓' : '✗' }}
                                                {{ $clabel }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Panel kanan: kedudukan kewangan + borang --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Kedudukan Kewangan</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Peruntukan</dt><dd class="font-medium text-navy-700"><x-money :value="$summary->allocation" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Pending Lain</dt><dd class="text-orange-600"><x-money :value="$otherPending" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Permohonan Ini</dt><dd class="text-gray-900"><x-money :value="$thisRequest" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Committed</dt><dd class="text-amber-600"><x-money :value="$summary->committed" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Spent</dt><dd class="text-purple-600"><x-money :value="$summary->spent" /></dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="font-medium text-gray-600">Ledger Available</dt><dd class="font-semibold text-green-600"><x-money :value="$ledgerAvailable" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Projected Available</dt><dd class="font-semibold {{ $projected->isNegative() ? 'text-danger' : 'text-green-600' }}"><x-money :value="$projected" /></dd></div>
                </dl>
                <p class="mt-2 text-[11px] text-gray-400">Ledger Available = Peruntukan − Committed − Spent. Projected = Ledger Available − Pending − Permohonan Ini.</p>
            </div>

            <div class="card p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Keputusan {{ $reviewType->label() }}</h3>
                <form method="POST" action="{{ route('reviews.store', [$application, $reviewType->value]) }}" class="space-y-4">
                    @csrf

                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-800">Senarai Semak JP <span class="text-danger">*</span></p>
                        <p class="mb-3 text-[11px] text-gray-500">UR-M04-001: tandakan setiap item. Hantar ke perakuan hanya jika semua <strong>Lengkap</strong>.</p>
                        @error('checklist')
                            <p class="mb-2 text-xs text-danger">{{ $message }}</p>
                        @enderror
                        <ul class="space-y-3">
                            @foreach ($checklistItems as $key => $label)
                                @php
                                    $hint = $checklistHints[$key] ?? null;
                                    $oldVal = old('checklist.'.$key, ($hint['ok'] ?? false) ? 'lengkap' : '');
                                @endphp
                                <li class="rounded-lg border border-gray-100 bg-gray-50/80 p-3">
                                    <p class="text-sm text-gray-800">{{ $label }}</p>
                                    @if ($hint)
                                        <p class="mt-1 text-[11px] {{ $hint['ok'] ? 'text-green-700' : 'text-amber-700' }}">
                                            Petunjuk sistem: {{ $hint['note'] }}
                                        </p>
                                    @endif
                                    <div class="mt-2 flex gap-4 text-sm">
                                        <label class="inline-flex items-center gap-1.5">
                                            <input type="radio" name="checklist[{{ $key }}]" value="lengkap" class="text-royal-600" @checked($oldVal === 'lengkap') required>
                                            Lengkap
                                        </label>
                                        <label class="inline-flex items-center gap-1.5">
                                            <input type="radio" name="checklist[{{ $key }}]" value="tidak_lengkap" class="text-royal-600" @checked($oldVal === 'tidak_lengkap')>
                                            Tidak lengkap
                                        </label>
                                    </div>
                                    @error('checklist.'.$key)
                                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                    @enderror
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <x-field label="Keputusan" name="decision" :required="true">
                        <select name="decision" class="inp" required>
                            @foreach (\App\Enums\ReviewDecision::cases() as $d)
                                <option value="{{ $d->value }}" @selected(old('decision') === $d->value)>{{ $d->label() }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    <x-field label="Ulasan / Catatan" name="comments" hint="Wajib jika bukan 'Disyorkan'.">
                        <textarea name="comments" rows="4" class="inp">{{ old('comments') }}</textarea>
                    </x-field>
                    <button class="btn-navy w-full">Hantar Keputusan</button>
                </form>
                <p class="mt-2 text-[11px] text-gray-400">Semakan Pegawai JP adalah nasihat — kelulusan formal oleh Peraku / PEPU. Semakan ini TIDAK mencipta komitmen bajet.</p>
            </div>
        </div>
    </div>
@endsection
