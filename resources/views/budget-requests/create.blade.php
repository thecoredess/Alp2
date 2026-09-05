@extends('layouts.app')
@section('title', 'Cadangan Bajet Baharu')
@section('heading', 'Cadangan Bajet Baharu')

@section('content')
    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('budget-requests.store') }}">
                @include('budget-requests._form')
            </form>
        </div>
    </div>
@endsection
