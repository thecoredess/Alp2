@extends('layouts.app')
@section('title', 'Kemaskini Pengguna')
@section('heading', 'Kemaskini Pengguna — '.$user->name)

@section('content')
    <div class="max-w-3xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @include('users._form')
            </form>
        </div>
    </div>
@endsection
