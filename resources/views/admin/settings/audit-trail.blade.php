@extends('layouts.app')
@section('title', 'Jejak Audit')
@section('heading', 'Jejak Audit Sistem')
@section('subheading', 'Rekod tindakan penting — Super Admin sahaja')

@section('content')
    <div class="page-shell">
        <div class="mb-4">
            <a href="{{ route('settings.hub') }}" class="text-sm font-medium text-royal-600 hover:text-royal-700">← Kembali ke Tetapan Sistem</a>
        </div>

        <form method="GET" action="{{ route('settings.audit-trail') }}" class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500">Pengguna</label>
                <select name="user" class="inp sm:w-48">
                    <option value="">Semua</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? null) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Tindakan</label>
                <select name="action" class="inp sm:w-56">
                    <option value="">Semua</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" @selected(($filters['action'] ?? null) === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Entiti (jenis)</label>
                <input type="text" name="entiti" value="{{ $filters['entity_type'] ?? '' }}" class="inp sm:w-40" placeholder="cth: Application">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Dari</label>
                <input type="date" name="dari" value="{{ $filters['date_from'] ?? '' }}" class="inp">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Hingga</label>
                <input type="date" name="hingga" value="{{ $filters['date_to'] ?? '' }}" class="inp">
            </div>
            <button type="submit" class="btn-primary">Tapis</button>
            <x-filter-reset :href="route('settings.audit-trail')" />
        </form>

        <p class="mb-3 text-xs text-gray-500">
            {{ $rows->total() }} rekod dijumpai
            @if (collect($filters)->filter()->isNotEmpty())
                · tapisan aktif
            @endif
        </p>

        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-3 py-3">Masa</th>
                        <th class="px-3 py-3">Pengguna</th>
                        <th class="px-3 py-3">Peranan</th>
                        <th class="px-3 py-3">Tindakan</th>
                        <th class="px-3 py-3">Entiti</th>
                        <th class="px-3 py-3">Butiran</th>
                        <th class="px-3 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ $r['timestamp']?->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $r['user'] }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $r['role'] }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded bg-navy-50 px-2 py-0.5 font-mono text-xs text-navy-700">{{ $r['action'] }}</span>
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ $r['entity'] }}</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($r['description'], 120) }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-400">{{ $r['ip'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">Tiada rekod audit untuk tapisan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $rows->links() }}</div>
        <p class="mt-2 text-xs text-gray-400">Nilai sensitif (kata laluan, token) ditapis daripada paparan.</p>
    </div>
@endsection
