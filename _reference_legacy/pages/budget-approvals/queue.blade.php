@extends('layouts.app')
@section('title', 'Kelulusan Bajet')
@section('heading', 'Kelulusan Bajet (Menunggu)')

@section('content')
    <form method="GET" class="mb-5">
        <select name="jenis" onchange="this.form.submit()" class="inp w-auto">
            <option value="">Semua Jenis</option>
            @foreach (\App\Enums\BudgetRequestType::options() as $val => $label)
                <option value="{{ $val }}" @selected(request('jenis') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Rujukan</th><th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">ALP</th><th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3">Maker</th>
                    <th class="px-4 py-3">Dihantar</th><th class="px-4 py-3 text-right">Tindakan</th>
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
                        <td class="px-4 py-3 text-gray-600">{{ $req->maker?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $req->submitted_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('budget-approvals.show', $req) }}" class="btn-primary !py-1 !px-3 text-xs">Semak &amp; Putus</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Tiada cadangan menunggu kelulusan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
@endsection
