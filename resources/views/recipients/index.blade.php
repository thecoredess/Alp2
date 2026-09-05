@extends('layouts.app')
@section('title', 'Penerima / Persatuan')
@section('heading', 'Penerima Sumbangan')
@section('subheading', 'Entiti persatuan (ROS) — BR-008 / BR-009')

@section('content')
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Nama / ROS / alamat…" class="inp sm:w-72">
        <button type="submit" class="btn-primary">Cari</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">No. ROS</th>
                    <th class="px-4 py-3">Akaun bank</th>
                    <th class="px-4 py-3 text-right">Bil. permohonan</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($recipients as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $r->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $r->ros_number }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $r->bank_account ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ $r->applications_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('recipients.show', $r) }}" class="btn-primary !py-1 !px-3 text-xs">Buka</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Tiada rekod penerima.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $recipients->links() }}</div>
@endsection
