@props([
    'alpId' => null,
    'onBehalf' => false,
    'associationToken' => null,
])

@php
    $resolvedAlpId = (int) ($alpId ?? auth()->user()?->alp_id ?? 0);
    $publicLink = filled($associationToken);
    $checkUrl = $publicLink
        ? route('association-form.recipients.check_ros', $associationToken)
        : route('applications.recipients.check_ros');
@endphp

<div
    class="mt-2"
    x-data="{
        mismatch: false,
        recordedRos: '',
        loading: false,
        timer: null,
        checkUrl: @js($checkUrl),
        publicLink: @js($publicLink),
        onBehalf: @js((bool) $onBehalf),
        defaultAlpId: @js($resolvedAlpId),
        alpId() {
            if (this.onBehalf) {
                const sel = document.getElementById('alp_id');
                return sel && sel.value ? parseInt(sel.value, 10) : 0;
            }
            return this.defaultAlpId;
        },
        scheduleCheck() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.runCheck(), 320);
        },
        async runCheck() {
            const nameEl = document.getElementById('recipient_name');
            const rosEl = document.getElementById('recipient_ros_number');
            const name = (nameEl?.value || '').trim();
            const ros = (rosEl?.value || '').trim();
            if (! name || ! ros) {
                this.mismatch = false;
                this.recordedRos = '';
                return;
            }
            if (! this.publicLink) {
                const id = this.alpId();
                if (! id) {
                    this.mismatch = false;
                    return;
                }
            }
            this.loading = true;
            try {
                const url = new URL(this.checkUrl, window.location.origin);
                if (! this.publicLink) {
                    url.searchParams.set('alp_id', String(this.alpId()));
                }
                url.searchParams.set('name', name);
                url.searchParams.set('ros', ros);
                const res = await fetch(url.toString(), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (! res.ok) {
                    this.mismatch = false;
                    return;
                }
                const body = await res.json();
                this.mismatch = Boolean(body.mismatch);
                this.recordedRos = body.recorded_ros || '';
            } finally {
                this.loading = false;
            }
        },
        bindFields() {
            const nameEl = document.getElementById('recipient_name');
            const rosEl = document.getElementById('recipient_ros_number');
            nameEl?.addEventListener('input', () => this.scheduleCheck());
            rosEl?.addEventListener('input', () => this.scheduleCheck());
            rosEl?.addEventListener('blur', () => this.runCheck());
            window.addEventListener('recipient-persatuan-picked', () => this.scheduleCheck());
            this.runCheck();
        },
    }"
    x-init="bindFields()"
>
    <div
        x-show="mismatch"
        x-cloak
        class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2.5 text-sm text-amber-950"
        role="status"
    >
        <p class="font-semibold">No. ROS berbeza dengan rekod terdahulu</p>
        <p class="mt-1 text-amber-900">
            Persatuan ini pernah diisi dengan No. ROS
            <span class="font-mono font-semibold" x-text="recordedRos"></span>.
            Nombor yang anda masukkan sekarang berbeza — sila pastikan ROS betul dan selari dengan dokumen pendaftaran.
        </p>
    </div>
</div>
