@extends('layouts.app')
@section('title', 'Pengguna Baharu')
@section('heading', 'Pengguna Baharu')

@section('content')
    <div class="max-w-3xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('users.store') }}">
                @include('users._form')
            </form>
        </div>
    </div>
@endsection
