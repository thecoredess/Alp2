@extends('layouts.app')
@section('title', 'Pengesahan Refund')
@section('heading', 'Refund Menunggu Pengesahan')

@section('content')
    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="px-4 py-3">Projek</th><th class="px-4 py-3">Perbelanjaan</th><th class="px-4 py-3">ALP</th>
                <th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3">Maker</th><th class="px-4 py-3 text-right">Tindakan</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($refunds as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $r->project->project_number }}</td>
                        <td class="px-4 py-3 text-gray-900">{{ $r->expense->reference_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->project->alp->ref_code }}</td>
                        <td class="px-4 py-3 text-right text-gray-900"><x-money :value="$r->amount" /></td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->maker?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('refunds.show', $r) }}" class="btn-primary !py-1 !px-3 text-xs">Semak</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Tiada refund menunggu pengesahan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $refunds->links() }}</div>
@endsection
