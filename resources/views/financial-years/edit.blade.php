@extends('layouts.app')
@section('title', 'Sunting Tahun Kewangan')
@section('heading', 'Sunting Tahun Kewangan '.$year->year)

@section('content')
    <div class="max-w-xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('financial-years.update', $year) }}">
                @include('financial-years._form')
            </form>
        </div>
    </div>
@endsection
