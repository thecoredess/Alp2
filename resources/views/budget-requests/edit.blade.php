@extends('layouts.app')
@section('title', 'Kemaskini Cadangan Bajet')
@section('heading', 'Kemaskini Cadangan Bajet')

@section('content')
    <div class="max-w-2xl">
        @if ($request->status === \App\Enums\BudgetRequestStatus::REVISION_REQUIRED && $request->return_reason)
            <div class="mb-4 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                <p class="font-semibold">Pembetulan Diperlukan</p>
                <p>Sebab: {{ $request->return_reason }}</p>
            </div>
        @endif
        <div class="card p-6">
            @php $types = \App\Enums\BudgetRequestType::options(); $alps = \App\Models\Alp::orderBy('ref_code')->get(); $years = \App\Models\FinancialYear::orderByDesc('year')->get(); @endphp
            <form method="POST" action="{{ route('budget-requests.update', $request) }}">
                @include('budget-requests._form')
            </form>
        </div>
    </div>
@endsection
