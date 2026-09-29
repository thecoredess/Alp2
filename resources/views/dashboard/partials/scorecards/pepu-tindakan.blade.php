@php
    $executive = $pepuScorecards['executive'] ?? [];
    $links = $pepuScorecards['links'] ?? [];
    $pendingApproval = (int) ($executive['pending_approval'] ?? 0);
@endphp

<div class="mb-8">
    <h3 class="dashboard-section-title">Tindakan Eksekutif</h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div @class([
            'rounded-xl',
            'ring-2 ring-amber-400/70 ring-offset-2' => $pendingApproval > 0,
        ])>
            <x-stat-card
                label="Menunggu Kelulusan"
                icon="shield-check"
                :tone="$pendingApproval > 0 ? 'amber' : 'gray'"
                :href="$links['pending_approval'] ?? null"
                :hint="$pendingApproval > 0 ? 'Perlu tindakan PEPU' : null"
            >{{ $pendingApproval }}</x-stat-card>
        </div>

        <x-stat-card
            label="Dalam Semakan JP"
            icon="search"
            tone="blue"
            :href="$links['under_review'] ?? null"
        >{{ $executive['under_review'] ?? 0 }}</x-stat-card>

        <x-stat-card
            label="Diluluskan"
            icon="check"
            tone="green"
            :href="$links['approved'] ?? null"
        >{{ $executive['approved'] ?? 0 }}</x-stat-card>

        <x-stat-card
            label="Jumlah Permohonan"
            icon="clipboard"
            tone="navy"
            :href="$links['total'] ?? null"
        >{{ $executive['total'] ?? 0 }}</x-stat-card>
    </div>
</div>
