@extends('layouts.app')
@section('title', 'Manual Pengguna')
@section('heading', 'Manual Pengguna')
@section('subheading', 'Panduan mengikut peranan: '.$audienceLabel)

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2 no-print">
        <button type="button" onclick="window.print()" class="btn-white">Cetak ringkasan</button>
        @if ($hasRoleManualDocx && $roleManual)
            <a href="{{ route('manual.docx') }}" class="btn-primary">
                Muat turun Manual (Word)
            </a>
        @endif
        @if ($audience === 'alp')
            @can('applications.create')
                <a href="{{ route('association-guide.download') }}?v=3" class="btn-white">Panduan Dokumen Persatuan (PDF)</a>
            @endcan
        @endif
        @if ($hasOfficialPdf)
            <a href="{{ route('manual.download') }}" class="btn-white">Manual PDF rasmi (M11)</a>
        @endif
    </div>

    @if ($hasRoleManualDocx && $roleManual)
        <div class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-4 text-sm text-indigo-950 no-print">
            <p class="font-semibold text-indigo-900">{{ $roleManual->title }}</p>
            <p class="mt-1 text-indigo-800/90">
                Dokumen Word lengkap dengan tangkapan skrin sistem untuk peranan <strong>{{ $audienceLabel }}</strong>.
            </p>
            <a href="{{ route('manual.docx') }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 underline hover:text-indigo-900">
                Muat turun {{ $roleManual->downloadName }}
            </a>
        </div>
    @elseif ($roleManual !== null)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 no-print">
            Fail manual Word untuk peranan ini belum dipasang pada pelayan. Gunakan ringkasan di bawah atau hubungi pentadbir sistem.
        </div>
    @endif

    @if ($hasOfficialPdf)
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900 no-print">
            Manual PDF rasmi (M11) tersedia.
            <a href="{{ route('manual.download') }}" class="font-medium underline">Muat turun</a>
        </div>
    @endif

    @if ($canManageManual)
        <div class="mb-6 card space-y-3 p-5 no-print">
            <h3 class="text-sm font-semibold text-gray-900">Pentadbir — muat naik Manual PDF (M11)</h3>
            <form method="POST" action="{{ route('manual.upload') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Fail PDF (maks 10 MB)</label>
                    <input type="file" name="manual_pdf" accept="application/pdf" class="inp" required>
                    @error('manual_pdf')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-primary">Muat naik</button>
            </form>
            @if ($hasOfficialPdf)
                <form method="POST" action="{{ route('manual.destroy') }}" onsubmit="return confirm('Buang manual PDF rasmi?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-medium text-danger hover:underline">Buang PDF rasmi</button>
                </form>
            @endif
            <p class="text-xs text-gray-500">
                Manual Word peranan (ALP / Admin JP / Pengarah) dihoskan dalam <code class="rounded bg-gray-100 px-1">docs/manual-pengguna/</code> — kemas kini fail <code class="rounded bg-gray-100 px-1">.docx</code> melalui deploy.
            </p>
        </div>
    @endif

    <div class="mb-4 rounded-lg border border-royal-200 bg-royal-50 px-4 py-3 text-sm text-royal-900">
        Ringkasan dalam sistem untuk: <strong>{{ $audienceLabel }}</strong>.
        @if (! empty($manualFiguresByFile))
            Rajah di bawah setiap bahagian; klik imej untuk paparan lebih besar. Muat turun Word untuk versi cetak.
        @else
            Sila muat turun manual Word untuk langkah terperinci dan paparan skrin.
        @endif
    </div>

    @if ($roleManual?->key === 'alp')
        @include('manual.sections.alp', ['manualFiguresByFile' => $manualFiguresByFile ?? []])
    @elseif ($roleManual?->key === 'admin_jp')
        @include('manual.sections.admin-jp', ['manualFiguresByFile' => $manualFiguresByFile ?? []])
    @elseif ($roleManual?->key === 'pengarah_jp')
        @include('manual.sections.pengarah-jp', ['manualFiguresByFile' => $manualFiguresByFile ?? []])
    @elseif ($audience === 'pepu')
        <div class="card space-y-3 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Tatacara PEPU / Pengurusan</h3>
            <p class="text-sm text-gray-600">
                Selepas kelulusan Pengarah JP, PEPU menerima <strong>e-mel makluman</strong> dan boleh semak status permohonan diluluskan dalam sistem.
                Tiada menu kelulusan dalam sistem untuk PEPU.
            </p>
            <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-700">
                <li>Log masuk → <strong>Semua Permohonan</strong> / <strong>Laporan</strong> untuk pemantauan.</li>
                <li>Notifikasi <em>approved_pepu_informed</em> apabila Pengarah meluluskan.</li>
            </ol>
        </div>
    @elseif ($audience === 'jp')
        <div class="card space-y-4 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Tatacara Pegawai Dalaman</h3>

            @can('applications.review.secretariat')
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ auth()->user()->canMakeFullJpReviewDecision() ? 'Admin JP' : 'Pegawai JP' }}
                    </p>
                    <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-gray-700">
                        @if (auth()->user()->canMakeFullJpReviewDecision())
                            <li>Semak permohonan di <strong>Semakan Admin JP</strong> (status Dihantar).</li>
                            <li>Lengkapkan senarai semak UR-M04-001 + ulasan JKEW → keputusan Disyorkan / Tidak Disyorkan / Kembalikan.</li>
                            <li>Selepas syor → giliran <strong>Kelulusan Pengarah JP</strong> (bukan Pegawai JP untuk permohonan baharu).</li>
                        @else
                            <li>Hanya rekod legacy: semakan <strong>Pegawai JP</strong> untuk permohonan lama dalam sistem.</li>
                            <li>Permohonan baharu: Admin JP → terus Pengarah JP.</li>
                        @endif
                    </ol>
                </div>
            @endcan

            @can('applications.approve')
                @if (auth()->user()->hasRole(\App\Enums\RoleName::PELULUS->value))
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Pengarah JP</p>
                        <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-gray-700">
                            <li>Menu <strong>Pengesyoran</strong> — lulus / tolak / kembalikan (satu aras kelulusan akhir).</li>
                            <li>Pastikan baki peruntukan ALP mencukupi sebelum lulus.</li>
                        </ol>
                    </div>
                @endif
            @endcan

            @can('payments.view')
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Kerani Kewangan / JKEW</p>
                    <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-gray-700">
                        <li>Kemas kini baucar, tarikh hantar JKEW, semakan silang, dan status bayaran.</li>
                    </ol>
                </div>
            @endcan

            @if (auth()->user()->can('applications.view_all') || auth()->user()->can('applications.review.secretariat'))
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Laporan Aktiviti</p>
                    <p class="mt-2 text-sm text-gray-700">Semak <strong>Laporan Aktiviti</strong> untuk report card tertunggak.</p>
                </div>
            @endif

            @if (! auth()->user()->can('applications.review.secretariat')
                && ! auth()->user()->can('applications.approve')
                && ! auth()->user()->can('payments.view')
                && ! auth()->user()->can('applications.view_all'))
                <p class="text-sm text-gray-600">Tiada tatacara khusus. Muat turun manual Word jika tersedia atau hubungi pentadbir.</p>
            @endif
        </div>
    @else
        <div class="card p-6">
            <p class="text-sm text-gray-600">Manual khusus peranan tidak tersedia. Sila hubungi pentadbir sistem.</p>
        </div>
    @endif

    <style>
        @media print {
            .no-print { display: none !important; }
            nav, aside, header { display: none !important; }
        }
    </style>
@endsection
