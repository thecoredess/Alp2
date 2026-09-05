@extends('layouts.app')
@section('title', 'Aras Kelulusan Baharu')
@section('heading', 'Aras Kelulusan Baharu')

@section('content')
    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('approval-matrix.store') }}">
                @include('admin.approval-matrix._form')
            </form>
        </div>
    </div>
@endsection
