@props([
    'application' => null,
    'alpId' => null,
    'onBehalf' => false,
    'fieldWrapClass' => '',
    'associationToken' => null,
])

@php
    $name = old('recipient_name', $application?->recipient_name);
    $resolvedAlpId = (int) ($alpId ?? $application?->alp_id ?? auth()->user()?->alp_id ?? 0);
    $publicLink = filled($associationToken);
    $suggestUrl = $publicLink
        ? route('association-form.recipients.suggest', $associationToken)
        : route('applications.recipients.suggest');
@endphp

<div class="{{ $fieldWrapClass }}">
    <x-field label="b) Nama Persatuan" name="recipient_name" :required="true"
             hint="Taip nama — cadangan persatuan yang pernah diisi untuk ALP ini. Pilih untuk isi ROS, no. akaun & alamat.">
        <div
            class="relative"
            x-data="{
                open: false,
                loading: false,
                items: [],
                timer: null,
                suggestUrl: @js($suggestUrl),
                publicLink: @js($publicLink),
                onBehalf: @js((bool) $onBehalf),
                defaultAlpId: @js($resolvedAlpId),
                activeIndex: -1,
                alpId() {
                    if (this.onBehalf) {
                        const sel = document.getElementById('alp_id');
                        return sel && sel.value ? parseInt(sel.value, 10) : 0;
                    }
                    return this.defaultAlpId;
                },
                scheduleSearch() {
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => this.fetchSuggestions(), 280);
                },
                async fetchSuggestions() {
                    if (! this.publicLink) {
                        const id = this.alpId();
                        if (! id) {
                            this.items = [];
                            this.open = false;
                            return;
                        }
                    }
                    const input = this.$refs.nameInput;
                    const q = (input?.value || '').trim();
                    if (this.publicLink && q.length < 2) {
                        this.items = [];
                        this.open = false;
                        return;
                    }
                    this.loading = true;
                    try {
                        const url = new URL(this.suggestUrl, window.location.origin);
                        if (! this.publicLink) {
                            url.searchParams.set('alp_id', String(this.alpId()));
                        }
                        if (q.length >= 1) {
                            url.searchParams.set('q', q);
                        }
                        const res = await fetch(url.toString(), {
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (! res.ok) {
                            this.items = [];
                            this.open = false;
                            return;
                        }
                        const body = await res.json();
                        this.items = Array.isArray(body.data) ? body.data : [];
                        this.open = this.items.length > 0;
                        this.activeIndex = -1;
                    } finally {
                        this.loading = false;
                    }
                },
                pick(item) {
                    this.$refs.nameInput.value = item.name || '';
                    const ros = document.getElementById('recipient_ros_number');
                    const bank = document.getElementById('recipient_bank_account');
                    const address = document.getElementById('recipient_address');
                    if (ros) {
                        ros.value = item.ros_number || '';
                    }
                    if (bank) {
                        bank.value = item.bank_account || '';
                    }
                    if (address) {
                        address.value = item.address || '';
                    }
                    this.open = false;
                    this.items = [];
                    window.dispatchEvent(new CustomEvent('recipient-persatuan-picked'));
                },
                onKeydown(e) {
                    if (! this.open || ! this.items.length) {
                        return;
                    }
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.activeIndex = Math.min(this.activeIndex + 1, this.items.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.activeIndex = Math.max(this.activeIndex - 1, 0);
                    } else if (e.key === 'Enter' && this.activeIndex >= 0) {
                        e.preventDefault();
                        this.pick(this.items[this.activeIndex]);
                    } else if (e.key === 'Escape') {
                        this.open = false;
                    }
                },
            }"
            @click.outside="open = false"
        >
            <input
                x-ref="nameInput"
                id="recipient_name"
                name="recipient_name"
                type="text"
                value="{{ $name }}"
                required
                class="inp"
                autocomplete="off"
                @input="scheduleSearch()"
                @focus="if (! publicLink || ($refs.nameInput.value.trim().length >= 2)) fetchSuggestions()"
                @keydown="onKeydown($event)"
            >
            <div
                x-show="open"
                x-cloak
                class="absolute z-30 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
                role="listbox"
            >
                <template x-for="(item, index) in items" :key="index">
                    <button
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-royal-50"
                        :class="index === activeIndex ? 'bg-royal-50 text-navy-800' : 'text-gray-900'"
                        @mousedown.prevent="pick(item)"
                        role="option"
                    >
                        <span class="font-medium" x-text="item.name"></span>
                        <span class="mt-0.5 block font-mono text-xs text-gray-500" x-show="item.ros_number" x-text="item.ros_number"></span>
                    </button>
                </template>
            </div>
            <p x-show="loading" x-cloak class="mt-1 text-xs text-gray-400">Mencari cadangan…</p>
        </div>
    </x-field>
</div>
