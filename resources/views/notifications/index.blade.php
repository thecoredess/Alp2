@extends('layouts.app')
@section('title', 'Notifikasi')
@section('heading', 'Notifikasi')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Makluman tindakan &amp; status permohonan (in-app + e-mel).</p>
        @if(auth()->user()->unreadNotifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="btn-white text-sm">Tanda semua dibaca</button>
            </form>
        @endif
    </div>

    <div class="card divide-y divide-gray-100 overflow-hidden">
        @forelse ($notifications as $n)
            <a href="{{ route('notifications.read', $n->id) }}"
               class="block px-5 py-4 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-royal-50/40' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                        <p class="mt-0.5 text-sm text-gray-600">{{ $n->data['message'] ?? '' }}</p>
                        @if(!empty($n->data['application_number']))
                            <p class="mt-1 font-mono text-xs text-gray-500">{{ $n->data['application_number'] }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <p class="px-5 py-10 text-center text-sm text-gray-400">Tiada notifikasi.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
