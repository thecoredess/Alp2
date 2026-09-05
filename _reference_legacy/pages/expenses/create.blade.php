@extends('layouts.app')
@section('title', 'Perbelanjaan Baharu')
@section('heading', 'Perbelanjaan Baharu — '.$project->project_number)

@section('content')
    <div class="max-w-2xl space-y-4">
        <div class="card p-5">
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div><p class="text-gray-500">Diluluskan</p><p class="font-semibold text-navy-700"><x-money :value="$finance['approved']" /></p></div>
                <div><p class="text-gray-500">Telah Dibelanja</p><p class="font-semibold text-purple-600"><x-money :value="$finance['spent']" /></p></div>
                <div><p class="text-gray-500">Baki Komitmen</p><p class="font-semibold text-amber-600"><x-money :value="$finance['outstanding']" /></p></div>
            </div>
        </div>
        <div class="card p-6">
            <form method="POST" action="{{ route('expenses.store', $project) }}">
                @include('expenses._form')
            </form>
        </div>
    </div>
@endsection
