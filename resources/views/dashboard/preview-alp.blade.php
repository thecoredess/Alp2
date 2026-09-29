@extends('layouts.app')
@section('title', 'Pratonton Score Card ALP')
@section('heading', 'Pratonton Dashboard — ALP')
@section('subheading', $activeYear ? 'Tahun Kewangan '.$activeYear->year.' · peranan ALP / Pemohon' : 'Data contoh')

@section('content')
    <div class="mb-6 space-y-3">
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <strong>Pratonton reka bentuk.</strong>
            @if ($useLive ?? false)
                Angka di bawah adalah data sebenar akaun ALP anda.
            @else
                Angka di bawah adalah data contoh. Staf boleh lihat susun atur; log masuk sebagai ALP dan buka
                <a href="{{ route('dashboard.preview-alp', ['live' => 1]) }}" class="font-medium underline">?live=1</a>
                untuk data sebenar.
            @endif
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            <a href="{{ route('dashboard') }}" class="text-royal-600 hover:underline">← Dashboard utama</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('dashboard.preview-pepu') }}" class="text-royal-600 hover:underline">Pratonton PEPU</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('dashboard.templates') }}" class="text-royal-600 hover:underline">Templat analisa</a>
        </div>
    </div>

    @include('dashboard.partials.scorecards.alp')

    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">
        Bahagian <strong>Analisa Sumbangan</strong> kekal seperti dashboard semasa — dipaparkan di bawah score card pada dashboard ALP sebenar.
    </div>
@endsection
