@php
    $actions = $alpScorecards['actions'] ?? [];
    $links = $alpScorecards['links'] ?? [];
@endphp

<div class="mb-8">
    <h3 class="dashboard-section-title">Tindakan Saya</h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            label="Draf"
            icon="document"
            tone="gray"
            :href="$links['draft'] ?? null"
        >{{ $actions['draft'] ?? 0 }}</x-stat-card>

        <x-stat-card
            label="Perlu Pembetulan"
            icon="pencil-square"
            tone="orange"
            :href="$links['revision'] ?? null"
        >{{ $actions['revision'] ?? 0 }}</x-stat-card>

        <x-stat-card
            label="Dalam Proses"
            icon="arrow-path"
            tone="blue"
            :href="$links['in_process'] ?? null"
        >{{ $actions['in_process'] ?? 0 }}</x-stat-card>

        <x-stat-card
            label="Laporan Aktiviti Tertunggak"
            icon="clock"
            :tone="($actions['report_overdue'] ?? 0) > 0 ? 'red' : 'green'"
            :href="$links['report_overdue'] ?? null"
        >{{ $actions['report_overdue'] ?? 0 }}</x-stat-card>
    </div>
</div>
