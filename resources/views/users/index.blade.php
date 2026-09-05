@extends('layouts.app')
@section('title', 'Pengguna')
@section('heading', 'Pengurusan Pengguna')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative w-full max-w-xs">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                <x-icon name="search" class="h-4 w-4" />
            </span>
            <input type="text" name="cari" value="{{ request('cari') }}"
                   placeholder="Cari nama / e-mel…" class="inp pl-9">
        </form>
        @can('users.create')
            <a href="{{ route('users.create') }}" class="btn-primary shrink-0">
                <x-icon name="plus" class="h-4 w-4" /> Pengguna Baharu
            </a>
        @endcan
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">E-mel</th>
                    <th class="px-4 py-3">Peranan</th>
                    <th class="px-4 py-3">ALP</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $user->roles->first()?->name ? \App\Enums\RoleName::from($user->roles->first()->name)->label() : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->alp?->ref_code ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :label="$user->status->label()" :classes="$user->status->badgeClasses()" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                @can('users.update')
                                    <a href="{{ route('users.edit', $user) }}" class="text-royal-600 hover:text-royal-700 font-medium">Sunting</a>
                                    <form method="POST" action="{{ route('users.reset-password', $user) }}"
                                          onsubmit="return confirm('Jana kata laluan sementara baharu untuk {{ $user->name }}?')">
                                        @csrf
                                        <button class="text-amber-600 hover:text-amber-700 font-medium">Reset Kata Laluan</button>
                                    </form>
                                @endcan
                                @can('users.deactivate')
                                    @if($user->id !== auth()->id())
                                        @if($user->isActive())
                                            <form method="POST" action="{{ route('users.deactivate', $user) }}"
                                                  onsubmit="return confirm('Nyahaktifkan akaun ini?')">
                                                @csrf
                                                <button class="text-danger hover:text-red-700 font-medium">Nyahaktif</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('users.activate', $user) }}">
                                                @csrf
                                                <button class="text-green-600 hover:text-green-700 font-medium">Aktif</button>
                                            </form>
                                        @endif
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">Tiada pengguna dijumpai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
