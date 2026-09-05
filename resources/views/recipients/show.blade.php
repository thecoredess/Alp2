@extends('layouts.app')
@section('title', $recipient->name)
@section('heading', $recipient->name)
@section('subheading', 'No. ROS: '.$recipient->ros_number)

@section('content')
    <div class="mb-5">
        <a href="{{ route('recipients.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
        </a>
    </div>

    <div class="mb-6 card p-5">
        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-gray-500">Nama</dt><dd class="text-gray-900">{{ $recipient->name }}</dd></div>
            <div><dt class="text-gray-500">No. ROS</dt><dd class="font-mono text-gray-900">{{ $recipient->ros_number }}</dd></div>
            <div><dt class="text-gray-500">Akaun bank</dt><dd class="font-mono text-gray-900">{{ $recipient->bank_account ?? '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-500">Alamat</dt><dd class="text-gray-900">{{ $recipient->address ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="card overflow-x-auto">
        <div class="px-4 pt-4 text-sm font-semibold text-gray-900">Sejarah permohonan</div>
        <table class="mt-2 min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-2">No.</th>
                    <th class="px-4 py-2">ALP</th>
                    <th class="px-4 py-2">Tahun</th>
                    <th class="px-4 py-2 text-right">Jumlah</th>
                    <th class="px-4 py-2">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($recipient->applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <a href="{{ route('applications.show', $app) }}" class="font-mono text-xs text-royal-700 hover:underline">{{ $app->application_number }}</a>
                        </td>
                        <td class="px-4 py-2 text-gray-700">{{ $app->alp?->ref_code }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $app->financialYear?->year }}</td>
                        <td class="px-4 py-2 text-right"><x-money :value="$app->requested_amount" /></td>
                        <td class="px-4 py-2">
                            <x-status-badge :label="$app->status->label()" :classes="$app->status->badgeClasses()" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tiada permohonan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
