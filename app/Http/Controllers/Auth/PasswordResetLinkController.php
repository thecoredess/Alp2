<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /** Papar borang "lupa kata laluan". */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /** Hantar pautan set semula kata laluan. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Sentiasa pulangkan mesej berjaya (elak pendedahan e-mel wujud/tidak).
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Jika e-mel itu berdaftar, pautan set semula kata laluan telah dihantar.');
    }
}
