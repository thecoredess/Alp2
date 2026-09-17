@extends('layouts.app')
@section('title', 'Kualiti Data')
@section('heading', 'Kualiti Data / Kawalan Dalaman')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <x-filter-reset />
        <div class="ml-auto flex items-center gap-2">@include('reports.partials.export-buttons')</div>
    </form>

    <div class="mb-5 card p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Ringkasan Pengecualian</h3>
            @if ($total === 0)
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">✓ 0 pengecualian</span>
            @else
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">{{ $total }} pengecualian</span>
            @endif
        </div>
        <p class="mt-1 text-xs text-gray-500">Alat kawalan dalaman — LAPOR SAHAJA, tiada pembaikan automatik.</p>
    </div>

    <div class="space-y-4">
        @foreach ($checks as $key => $check)
            <div class="card p-5">
                <div class="mb-2 flex items-center justify-between">
                    <h4 class="text-sm font-medium text-gray-900">{{ $check['label'] }}</h4>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $check['items']->isEmpty() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $check['items']->count() }}
                    </span>
                </div>
                @if ($check['items']->isNotEmpty())
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($check['items'] as $item)
                            <li class="flex items-start justify-between gap-4 py-2">
                                <span class="font-mono text-xs text-gray-700">{{ $item['ref'] }}</span>
                                <span class="text-right text-gray-600">{{ $item['detail'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-400">Tiada pengecualian.</p>
                @endif
            </div>
        @endforeach
    </div>
@endsection
