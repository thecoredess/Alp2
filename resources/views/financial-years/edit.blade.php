@extends('layouts.app')
@section('title', 'Kemaskini Tahun Kewangan')
@section('heading', 'Kemaskini Tahun Kewangan '.$year->year)

@section('content')
    <div class="max-w-xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('financial-years.update', $year) }}">
                @include('financial-years._form')
            </form>
        </div>
    </div>
@endsection
