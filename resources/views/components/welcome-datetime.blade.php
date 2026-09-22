@php
    $now = now('Asia/Kuala_Lumpur')->locale('ms');
    $initialTime = $now->format('g:i A');
@endphp

<div {{ $attributes->merge(['class' => 'shrink-0 text-left sm:text-right']) }}>
    <p class="text-sm text-gray-600">
        {{ $now->translatedFormat('d F Y') }},
        <span class="font-medium capitalize text-gray-900">{{ $now->translatedFormat('l') }}</span>
    </p>
    <p
        class="mt-1.5 font-mono text-[1.35rem] font-semibold tabular-nums leading-none text-navy-800"
        x-data="{
            display: @js($initialTime),
            init() {
                const format = () => new Intl.DateTimeFormat('en-US', {
                    timeZone: 'Asia/Kuala_Lumpur',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true,
                }).format(new Date());
                this.display = format();
                this._timer = setInterval(() => { this.display = format(); }, 1000);
            },
            destroy() {
                clearInterval(this._timer);
            },
        }"
        x-text="display"
        aria-live="polite"
        aria-label="Masa semasa"
    >{{ $initialTime }}</p>
</div>
