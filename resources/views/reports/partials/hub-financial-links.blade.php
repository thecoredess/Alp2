@php
    $u = auth()->user();
    $yearId = ($selectedYear ?? $activeYear ?? null)?->id;
@endphp
@if ($u->canAccessReport('reports.financial'))
    <div class="mt-5 max-w-md">
        <div class="card p-5">
            <div class="mb-3 flex items-center gap-2">
                <span class="rounded-lg bg-navy-50 p-2 text-navy-700"><x-icon name="wallet" class="h-5 w-5" /></span>
                <h3 class="text-sm font-semibold text-gray-900">Kewangan / Peruntukan</h3>
            </div>
            <ul class="space-y-1.5">
                <li>
                    <a href="{{ route('reports.allocation', ['fy' => $yearId]) }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span>Peruntukan Mengikut ALP</span>
                        <span class="text-gray-300">›</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.ledger', ['fy' => $yearId]) }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span>Lejar Bajet</span>
                        <span class="text-gray-300">›</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endif
