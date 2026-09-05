@props(['application', 'current'])

@php
    $steps = [
        1 => ['Maklumat Projek', 'applications.wizard.maklumat'],
        2 => ['Objektif & Skop', 'applications.wizard.objektif'],
        3 => ['Pecahan Bajet', 'applications.wizard.bajet'],
        4 => ['Dokumen', 'applications.wizard.dokumen'],
        5 => ['Semakan', 'applications.wizard.semakan'],
        6 => ['Hantar', null],
    ];
@endphp

<ol class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-2 text-sm">
    @foreach ($steps as $n => [$label, $route])
        @php
            $isCurrent = $n === $current;
            $isDone = $n < $current;
        @endphp
        <li class="flex items-center gap-2">
            @if ($route)
                <a href="{{ route($route, $application) }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-1.5 {{ $isCurrent ? 'bg-navy-700 text-white' : ($isDone ? 'text-navy-700 hover:bg-navy-50' : 'text-gray-400 hover:bg-gray-50') }}">
                    <span class="grid h-5 w-5 place-items-center rounded-full text-xs {{ $isCurrent ? 'bg-white text-navy-700' : ($isDone ? 'bg-navy-100 text-navy-700' : 'bg-gray-200 text-gray-500') }}">{{ $n }}</span>
                    {{ $label }}
                </a>
            @else
                <span class="flex items-center gap-2 rounded-lg px-3 py-1.5 {{ $isCurrent ? 'bg-navy-700 text-white' : 'text-gray-400' }}">
                    <span class="grid h-5 w-5 place-items-center rounded-full text-xs {{ $isCurrent ? 'bg-white text-navy-700' : 'bg-gray-200 text-gray-500' }}">{{ $n }}</span>
                    {{ $label }}
                </span>
            @endif
            @if (! $loop->last)<span class="text-gray-300">›</span>@endif
        </li>
    @endforeach
</ol>
