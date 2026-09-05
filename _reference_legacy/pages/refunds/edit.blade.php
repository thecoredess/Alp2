@extends('layouts.app')
@section('title', 'Sunting Refund')
@section('heading', 'Sunting Refund')
@section('subheading', $refund->project->project_number.' · '.$refund->expense->reference_number)

@section('content')
    <div class="max-w-2xl">
        <div class="card p-6">
            <form method="POST" action="{{ route('refunds.update', $refund) }}">
                @include('refunds._form', ['expense' => $refund->expense])
            </form>
        </div>
    </div>
@endsection
