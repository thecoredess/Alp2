@extends('layouts.app')
@section('title', 'Tahun Kewangan Baharu')
@section('heading', 'Tahun Kewangan Baharu')

@section('content')
    <div class="max-w-xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('financial-years.store') }}">
                @include('financial-years._form')
            </form>
        </div>
    </div>
@endsection
