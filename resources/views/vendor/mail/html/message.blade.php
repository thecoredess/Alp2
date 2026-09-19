<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
**{{ config('app.name') }}** — Jabatan Pentadbiran, Dewan Bandaraya Kuala Lumpur

E-mel ini dihantar secara automatik. Sila jangan balas e-mel ini.

© {{ date('Y') }} Dewan Bandaraya Kuala Lumpur. Hak cipta terpelihara.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
