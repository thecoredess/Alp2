@extends('layouts.app')
@section('title', 'Laporan')
@section('heading', 'Laporan')
@section('subheading', 'Peruntukan · Permohonan · Program · Audit')

@php $u = auth()->user(); @endphp

@section('content')
    @php
        $categories = [
            ['perm' => 'reports.financial', 'title' => 'Kewangan / Peruntukan', 'icon' => 'wallet', 'items' => [
                ['reports.allocation', 'Peruntukan Mengikut ALP'],
                ['reports.ledger', 'Lejar Bajet'],
            ]],
            ['perm' => 'reports.applications', 'title' => 'Permohonan', 'icon' => 'clipboard', 'items' => [
                ['reports.applications', 'Laporan Permohonan'],
            ]],
            ['perm' => 'reports.view', 'title' => 'Program', 'icon' => 'calendar', 'items' => [
                ['reports.programs', 'Laporan Program & Laporan Aktiviti'],
            ]],
            ['perm' => 'reports.audit', 'title' => 'Audit', 'icon' => 'document', 'items' => [
                ['reports.audit', 'Jejak Audit'],
            ]],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $cat)
            @if ($u->can($cat['perm']))
                <div class="card p-5">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="rounded-lg bg-navy-50 p-2 text-navy-700"><x-icon name="{{ $cat['icon'] }}" class="h-5 w-5" /></span>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $cat['title'] }}</h3>
                    </div>
                    <ul class="space-y-1.5">
                        @foreach ($cat['items'] as [$route, $label])
                            <li>
                                <a href="{{ route($route, ['fy' => $selectedYear?->id]) }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <span>{{ $label }}</span>
                                    <span class="text-gray-300">›</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </div>
@endsection
