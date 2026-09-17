@extends('layouts.app')
@section('title', 'Cadangan Bajet Saya')
@section('heading', 'Cadangan Bajet Saya')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="inp w-auto">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <x-filter-reset />
        </form>
        <a href="{{ route('budget-requests.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Cadangan Baharu</a>
    </div>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Rujukan</th><th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">ALP</th><th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $req->reference_number ?? '—' }}</td>
                        <td class="px-4 py-3"><x-status-badge :label="$req->request_type->label()" :classes="$req->request_type->badgeClasses()" /></td>
                        <td class="px-4 py-3 text-gray-600">{{ $req->alp->ref_code }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $req->financialYear->year }}</td>
                        <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$req->amount" /></td>
                        <td class="px-4 py-3"><x-status-badge :label="$req->status->label()" :classes="$req->status->badgeClasses()" /></td>
                        <td class="px-4 py-3 text-right">
                            <x-table-actions>
                                <x-table-action href="{{ route('budget-requests.show', $req) }}" icon="eye" label="Lihat" variant="primary" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Tiada cadangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
@endsection
