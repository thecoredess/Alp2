<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Borang Persatuan') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-dbkl.png') }}?v={{ @filemtime(public_path('images/logo-dbkl.png')) ?: time() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 antialiased text-gray-800">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-3xl items-center gap-3 px-4 py-4 sm:px-6">
            <img src="{{ asset('images/logo-alp-dbkl.png') }}" alt="ALP DBKL" class="h-10 w-auto object-contain">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-royal-600">Borang Persatuan</p>
                <p class="text-sm text-gray-600">Isi borang &amp; muat naik lampiran untuk permohonan sumbangan ALP</p>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        @include('partials.flash')
        @yield('content')
    </main>
    <footer class="border-t border-gray-200 py-4 text-center text-xs text-gray-500">
        Sistem Sumbangan ALP &copy; {{ date('Y') }} DBKL
    </footer>
</body>
</html>
