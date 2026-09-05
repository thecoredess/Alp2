@extends('layouts.app')
@section('title', 'Sunting ALP')
@section('heading', 'Sunting ALP — '.$alp->name)

@section('content')
    <div class="max-w-3xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('alps.update', $alp) }}">
                @include('alps._form')
            </form>
        </div>
    </div>
@endsection
