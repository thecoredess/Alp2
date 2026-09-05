@extends('layouts.app')
@section('title', 'Laporan Permohonan')
@section('heading', 'Laporan Permohonan')
@section('subheading', 'Tahun Kewangan '.$year->year)

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        @include('reports.partials.year-filter')
        <div>
            <label class="block text-xs text-gray-500">Jenis</label>
            <select name="jenis" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                <option value="csr" @selected(request('jenis')==='csr')>CSR</option>
                <option value="development" @selected(request('jenis')==='development')>Pembangunan</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">Status</label>
            <select name="status" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                @foreach (\App\Enums\ApplicationStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-xs text-gray-500">Dari</label><input type="date" name="dari" value="{{ request('dari') }}" class="inp"></div>
        <div><label class="block text-xs text-gray-500">Hingga</label><input type="date" name="hingga" value="{{ request('hingga') }}" class="inp"></div>
        <button class="btn-white">Tapis</button>
        <div class="ml-auto">@include('reports.partials.export-buttons')</div>
    </form>

    {{-- Corong permohonan --}}
    <div class="mb-5 card p-5">
        <h3 class="mb-3 text-sm font-semibold text-gray-900">Corong Permohonan</h3>
        <div class="flex flex-wrap items-center gap-2 text-sm">
            @foreach ([['Draf', 'draft', 'reports.applications', ['status'=>'draft']], ['Semakan', 'review', null, null], ['Menunggu Lulus', 'pending_approval', 'approvals.queue', []], ['Diluluskan', 'approved', null, null], ['Projek', 'project', null, null]] as $i => [$label, $key, $route, $params])
                @if ($i > 0)<span class="text-gray-300">→</span>@endif
                <div class="rounded-lg border border-gray-200 px-4 py-2 text-center">
                    <p class="text-lg font-semibold text-navy-700">{{ $pipeline[$key] }}</p>
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-2 text-xs text-gray-400">Corong operasi (kiraan) — bukan komitmen kewangan.</p>
    </div>

    {{-- Kad status & amaun --}}
    <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-gray-500">Jumlah Permohonan</p><p class="mt-1 text-lg font-semibold text-navy-700">{{ $counts['total'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Jumlah Dipohon</p><p class="mt-1 text-lg font-semibold text-gray-700"><x-money :value="$amounts['total_requested']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Pending Request</p><p class="mt-1 text-lg font-semibold text-amber-600"><x-money :value="$amounts['pending_request']" /></p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Amaun Diluluskan</p><p class="mt-1 text-lg font-semibold text-green-700"><x-money :value="$amounts['approved_amount']" /></p></div>
    </div>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">No. Permohonan</th><th class="px-3 py-3">ALP</th><th class="px-3 py-3">Jenis</th>
                <th class="px-3 py-3">Tajuk</th><th class="px-3 py-3 text-right">Amaun</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Tarikh</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($listing as $a)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono text-xs text-gray-700">{{ $a->application_number }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $a->alp?->ref_code }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $a->application_type->label() }}</td>
                        <td class="px-3 py-2 text-gray-900">{{ \Illuminate\Support\Str::limit($a->project_title, 40) }}</td>
                        <td class="px-3 py-2 text-right"><x-money :value="$a->requested_amount" /></td>
                        <td class="px-3 py-2"><x-status-badge :label="$a->status->label()" :classes="$a->status->badgeClasses()" /></td>
                        <td class="px-3 py-2 text-gray-500">{{ $a->created_at?->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Tiada permohonan untuk tapisan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
