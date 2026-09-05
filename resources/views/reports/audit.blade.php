@extends('layouts.app')
@section('title', 'Jejak Audit')
@section('heading', 'Laporan Jejak Audit')

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500">Tindakan</label>
            <select name="action" class="inp" onchange="this.form.requestSubmit()">
                <option value="">Semua</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected(request('action')===$a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-xs text-gray-500">Entiti (jenis)</label><input type="text" name="entiti" value="{{ request('entiti') }}" class="inp" placeholder="cth: Project"></div>
        <div><label class="block text-xs text-gray-500">Dari</label><input type="date" name="dari" value="{{ request('dari') }}" class="inp"></div>
        <div><label class="block text-xs text-gray-500">Hingga</label><input type="date" name="hingga" value="{{ request('hingga') }}" class="inp"></div>
        <button class="btn-white">Tapis</button>
        <div class="ml-auto">@include('reports.partials.export-buttons')</div>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-3 py-3">Masa</th><th class="px-3 py-3">Pengguna</th><th class="px-3 py-3">Peranan</th>
                <th class="px-3 py-3">Tindakan</th><th class="px-3 py-3">Entiti</th><th class="px-3 py-3">Butiran</th><th class="px-3 py-3">IP</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ $r['timestamp']?->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $r['user'] }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $r['role'] }}</td>
                        <td class="px-3 py-2"><span class="rounded bg-navy-50 px-2 py-0.5 font-mono text-xs text-navy-700">{{ $r['action'] }}</span></td>
                        <td class="px-3 py-2 text-gray-600">{{ $r['entity'] }}</td>
                        <td class="px-3 py-2 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($r['description'], 80) }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-gray-400">{{ $r['ip'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Tiada rekod audit untuk tapisan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="mt-2 text-xs text-gray-400">Memaparkan sehingga 500 rekod terkini. Nilai sensitif ditapis.</p>
@endsection
