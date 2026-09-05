@extends('layouts.app')
@section('title', 'Dashboard Kewangan')
@section('heading', 'Dashboard Kewangan')
@section('subheading', $year ? 'Tahun Kewangan '.$year->year : 'Tiada tahun kewangan')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500">Tahun Kewangan</label>
            <select name="fy" class="inp" onchange="this.form.requestSubmit()">
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected($year?->id === $y->id)>{{ $y->year }} @if($y->is_active) (Aktif) @endif</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Giliran operasi kewangan (drill-down terus ke halaman berkaitan) --}}
    <h3 class="mb-3 text-sm font-semibold text-gray-900">Giliran Menunggu Tindakan</h3>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <a href="{{ route('budget-approvals.queue') }}" class="card p-5 hover:ring-2 hover:ring-navy-100">
            <p class="text-sm text-gray-500">Kelulusan Peruntukan</p><p class="mt-2 text-2xl font-semibold text-amber-600">{{ $queues['allocation'] }}</p>
        </a>
        <a href="{{ route('budget-approvals.queue') }}" class="card p-5 hover:ring-2 hover:ring-navy-100">
            <p class="text-sm text-gray-500">Kelulusan Pelarasan</p><p class="mt-2 text-2xl font-semibold text-amber-600">{{ $queues['adjustment'] }}</p>
        </a>
        <a href="{{ route('expenses.queue') }}" class="card p-5 hover:ring-2 hover:ring-navy-100">
            <p class="text-sm text-gray-500">Pengesahan Perbelanjaan</p><p class="mt-2 text-2xl font-semibold text-purple-600">{{ $queues['expense'] }}</p>
        </a>
        <a href="{{ route('refunds.queue') }}" class="card p-5 hover:ring-2 hover:ring-navy-100">
            <p class="text-sm text-gray-500">Pengesahan Refund</p><p class="mt-2 text-2xl font-semibold text-teal-600">{{ $queues['refund'] }}</p>
        </a>
    </div>

    {{-- Ringkasan kewangan --}}
    <h3 class="mb-3 mt-6 text-sm font-semibold text-gray-900">Ringkasan Kewangan</h3>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5"><p class="text-sm text-gray-500">Baki Komitmen</p><p class="mt-2 text-xl font-semibold text-amber-600"><x-money :value="$totals->committed" /></p></div>
        <div class="card p-5"><p class="text-sm text-gray-500">Belanja Kasar</p><p class="mt-2 text-xl font-semibold text-gray-700"><x-money :value="$totals->grossSpent" /></p></div>
        <div class="card p-5"><p class="text-sm text-gray-500">Refund</p><p class="mt-2 text-xl font-semibold text-teal-600"><x-money :value="$totals->refunded" /></p></div>
        <div class="card p-5"><p class="text-sm text-gray-500">Belanja Bersih</p><p class="mt-2 text-xl font-semibold text-purple-700"><x-money :value="$totals->netSpent()" /></p></div>
    </div>

    @can('reports.financial')
        <div class="mt-6 flex flex-wrap gap-2">
            <a href="{{ route('reports.allocation', ['fy'=>$year?->id]) }}" class="btn-white">Laporan Peruntukan</a>
            <a href="{{ route('reports.ledger', ['fy'=>$year?->id]) }}" class="btn-white">Ledger Bajet</a>
            <a href="{{ route('reports.reconciliation', ['fy'=>$year?->id]) }}" class="btn-white">Rekonsiliasi</a>
        </div>
    @endcan
@endsection
