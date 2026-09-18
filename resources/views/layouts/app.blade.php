<!DOCTYPE html>
<html lang="ms" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-dbkl.png') }}?v={{ @filemtime(public_path('images/logo-dbkl.png')) ?: time() }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-dbkl.png') }}?v={{ @filemtime(public_path('images/logo-dbkl.png')) ?: time() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-gray-800">
<div x-data="{ sidebarOpen: false }" class="min-h-full">

    {{-- Overlay mudah alih --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-navy-900/50 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 transform bg-navy-700 text-navy-50 transition-transform duration-200 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        @include('partials.sidebar')
    </aside>

    {{-- Kandungan utama --}}
    <div class="lg:pl-64 flex flex-col min-h-full">
        {{-- Bar atas --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
            <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700" aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <div class="flex-1">
                <h1 class="text-base font-semibold text-gray-900">@yield('heading', 'Dashboard')</h1>
                @hasSection('subheading')
                    <p class="text-xs text-gray-500">@yield('subheading')</p>
                @endif
            </div>

            <a href="{{ route('notifications.index') }}" class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Notifikasi">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
                @php $unreadHeader = auth()->user()->unreadNotifications->count(); @endphp
                @if($unreadHeader > 0)
                    <span class="absolute right-1 top-1 grid h-4 min-w-4 place-items-center rounded-full bg-danger px-1 text-[10px] font-semibold text-white">{{ $unreadHeader > 9 ? '9+' : $unreadHeader }}</span>
                @endif
            </a>

            {{-- Menu pengguna --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-gray-100">
                    <x-user-avatar :user="auth()->user()" size="sm" />
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm font-medium text-gray-900 leading-tight">{{ auth()->user()->name }}</span>
                        <span class="block text-xs text-gray-500 leading-tight">{{ auth()->user()->roles->first()?->name ? \App\Enums\RoleName::from(auth()->user()->roles->first()->name)->label() : '—' }}</span>
                    </span>
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false"
                     x-transition.opacity
                     class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->email }}</p>
                        @if(auth()->user()->unit)
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->unit }}</p>
                        @endif
                    </div>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-danger hover:bg-red-50">Log Keluar</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-4 sm:px-5 lg:px-6">
            @include('partials.flash')
            @yield('content')
        </main>

        <footer class="border-t border-gray-200 px-6 py-4 text-center text-xs text-gray-400">
            Sistem Sumbangan ALP &copy; {{ date('Y') }} DBKL
        </footer>
    </div>
</div>
</body>
</html>
