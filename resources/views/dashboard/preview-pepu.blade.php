@extends('layouts.app')
@section('title', 'Pratonton Score Card PEPU')
@section('heading', 'Pratonton Dashboard — PEPU')
@section('subheading', $activeYear ? 'Tahun Kewangan '.$activeYear->year.' · Pengurusan / PEPU' : 'Data contoh')

@section('content')
    <div class="mb-6 space-y-3">
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <strong>Pratonton reka bentuk.</strong>
            @if ($useLive ?? false)
                Angka di bawah adalah data sebenar sistem (skop tahun kewangan aktif).
            @else
                Angka di bawah adalah data contoh. Log masuk sebagai PEPU dan buka
                <a href="{{ route('dashboard.preview-pepu', ['live' => 1]) }}" class="font-medium underline">?live=1</a>
                untuk data sebenar.
            @endif
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            <a href="{{ route('dashboard') }}" class="text-royal-600 hover:underline">← Dashboard utama</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('dashboard.preview-alp') }}" class="text-royal-600 hover:underline">Pratonton ALP</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('dashboard.templates') }}" class="text-royal-600 hover:underline">Templat analisa</a>
        </div>
    </div>

    <p class="mb-4 text-sm text-gray-500">
        Pada dashboard sebenar, baris <strong>Tindakan Diperlukan</strong> (giliran kelulusan) kekal di atas score card ini.
    </p>

    @include('dashboard.partials.scorecards.pepu')
@endsection
