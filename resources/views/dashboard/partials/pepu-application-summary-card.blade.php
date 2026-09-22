{{-- Ringkasan permohonan PEPU — lima metrik dalam satu kad --}}
<div class="card overflow-hidden p-0">
    <div class="border-b border-gray-100 bg-gray-50/60 px-5 py-4">
        <h3 class="text-sm font-semibold text-gray-900">Ringkasan Permohonan</h3>
    </div>
    <div class="grid grid-cols-2 divide-x divide-y divide-gray-100 sm:grid-cols-3 lg:grid-cols-5 lg:divide-y-0">
        @foreach ([
            ['label' => 'Jumlah', 'value' => $appStats['total'], 'tone' => 'text-navy-700'],
            ['label' => 'Dalam Semakan', 'value' => $appStats['under_review'], 'tone' => 'text-blue-600'],
            ['label' => 'Menunggu Kelulusan', 'value' => $appStats['pending_approval'], 'tone' => 'text-amber-600'],
            ['label' => 'Diluluskan', 'value' => $appStats['approved'], 'tone' => 'text-green-600'],
            ['label' => 'Ditolak', 'value' => $appStats['rejected'], 'tone' => 'text-danger'],
        ] as $item)
            <div class="p-4 text-center sm:p-5 lg:text-left">
                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">{{ $item['label'] }}</p>
                <p @class(['mt-2 text-2xl font-bold tabular-nums sm:text-3xl', $item['tone']])>{{ $item['value'] }}</p>
            </div>
        @endforeach
    </div>
</div>
