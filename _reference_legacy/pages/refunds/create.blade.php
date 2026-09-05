@extends('layouts.app')
@section('title', 'Refund Baharu')
@section('heading', 'Refund Baharu — '.$expense->project->project_number)
@section('subheading', 'Perbelanjaan: '.$expense->reference_number.' · '.$expense->amountMoney()->format())

@section('content')
    <div class="max-w-2xl space-y-4">
        <div class="card p-5">
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div><p class="text-gray-500">Perbelanjaan</p><p class="font-semibold text-purple-600"><x-money :value="$expense->amount" /></p></div>
                <div><p class="text-gray-500">Refund Disahkan</p><p class="font-semibold text-teal-600"><x-money :value="$expense->verifiedRefundTotal()->value()" /></p></div>
                <div><p class="text-gray-500">Boleh Dipulangkan</p><p class="font-semibold text-navy-700"><x-money :value="$refundable->value()" /></p></div>
            </div>
        </div>
        <div class="card p-6">
            <form method="POST" action="{{ route('refunds.store', $expense) }}">
                @include('refunds._form')
            </form>
        </div>
    </div>
@endsection
