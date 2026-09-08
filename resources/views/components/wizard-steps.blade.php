@props(['application' => null, 'current'])

@php
    $steps = [
        1 => ['Borang Penyaluran', 'applications.wizard.maklumat'],
        2 => ['Muat Naik Dokumen', 'applications.wizard.dokumen'],
        3 => ['Hantar kepada JP', 'applications.wizard.semakan'],
    ];
@endphp

<div class="card mb-6 overflow-hidden">
    <div class="grid grid-cols-1 divide-y divide-gray-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
        @foreach ($steps as $n => [$label, $route])
            @php
                $isCurrent = $n === $current;
                $isDone = $application !== null && $n < $current;
                $isDisabled = ! $isCurrent && ! $isDone;
                $useLink = $application !== null && ! $isDisabled;
                $wrapperClass = \Illuminate\Support\Arr::toCssClasses([
                    'group flex items-center gap-3 px-4 py-4 transition sm:px-5',
                    'bg-navy-700 text-white' => $isCurrent,
                    'bg-white hover:bg-navy-50' => ! $isCurrent && $isDone,
                    'bg-gray-50/50 text-gray-400 pointer-events-none' => $isDisabled,
                ]);
            @endphp

            @if ($useLink)
                <a href="{{ route($route, $application) }}" class="{{ $wrapperClass }}">
            @else
                <div class="{{ $wrapperClass }}" @if ($isCurrent) aria-current="step" @endif>
            @endif
                <span @class([
                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                    'bg-white text-navy-700' => $isCurrent,
                    'bg-green-100 text-green-700' => $isDone && ! $isCurrent,
                    'bg-gray-200 text-gray-500' => $isDisabled,
                ])>
                    @if ($isDone && ! $isCurrent)
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @else
                        {{ $n }}
                    @endif
                </span>
                <span class="min-w-0">
                    <span @class(['block text-xs uppercase tracking-wide', $isCurrent ? 'text-navy-200' : 'text-gray-400'])>Langkah {{ $n }}</span>
                    <span @class(['block truncate text-sm font-semibold', $isCurrent ? 'text-white' : ($isDone ? 'text-navy-800' : 'text-gray-500')])>{{ $label }}</span>
                </span>
            @if ($useLink)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</div>
