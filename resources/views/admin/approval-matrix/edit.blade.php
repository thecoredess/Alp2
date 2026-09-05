@extends('layouts.app')
@section('title', 'Sunting Aras Kelulusan')
@section('heading', 'Sunting: '.$level->name)

@section('content')
    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('approval-matrix.update', $level) }}">
                @include('admin.approval-matrix._form')
            </form>
        </div>
    </div>
@endsection
