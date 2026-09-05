@extends('layouts.app')
@section('title', 'ALP Baharu')
@section('heading', 'Ahli Lembaga Baharu')

@section('content')
    <div class="max-w-3xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('alps.store') }}">
                @include('alps._form')
            </form>
        </div>
    </div>
@endsection
