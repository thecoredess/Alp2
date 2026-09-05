@extends('layouts.app')
@section('title', 'Sunting Perbelanjaan')
@section('heading', 'Sunting Perbelanjaan')

@section('content')
    <div class="max-w-2xl">
        @if ($expense->status === \App\Enums\ProjectExpenseStatus::REVISION_REQUIRED && $expense->return_reason)
            <div class="mb-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                <p class="font-semibold">Pembetulan Diperlukan</p><p>Sebab: {{ $expense->return_reason }}</p>
            </div>
        @endif
        <div class="card p-6">
            <form method="POST" action="{{ route('expenses.update', $expense) }}">
                @include('expenses._form')
            </form>
        </div>
    </div>
@endsection
