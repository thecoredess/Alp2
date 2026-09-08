<?php

namespace App\Http\Controllers;

use App\Support\NotificationTarget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        $actions = $notifications->mapWithKeys(fn ($n) => [
            $n->id => NotificationTarget::for($n->data ?? [], $request->user())->action,
        ]);

        return view('notifications.index', compact('notifications', 'actions'));
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return redirect(
            NotificationTarget::for($notification->data ?? [], $request->user())->url
        );
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Semua notifikasi ditanda sebagai dibaca.');
    }
}
