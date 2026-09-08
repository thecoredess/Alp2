@props(['aside' => false])

<div {{ $attributes->merge(['class' => 'page-shell']) }}>
    <div @class(['grid gap-6', 'lg:grid-cols-12' => $aside])>
        <div @class($aside ? 'min-w-0 space-y-6 lg:col-span-8 xl:col-span-8' : 'min-w-0 space-y-6')>
            {{ $slot }}
        </div>
        @if ($aside)
            <aside class="page-aside lg:col-span-4 xl:col-span-4">
                {{ $aside }}
            </aside>
        @endif
    </div>
</div>
