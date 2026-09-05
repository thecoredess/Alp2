<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memaksa pengguna menukar kata laluan jika ditanda `must_change_password`.
 * Semua halaman dilindungi kecuali laluan tukar kata laluan & log keluar.
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->must_change_password
            && ! $request->routeIs('password.change', 'password.change.update', 'logout')) {
            return redirect()->route('password.change')
                ->with('warning', 'Anda perlu menetapkan kata laluan baharu sebelum meneruskan.');
        }

        return $next($request);
    }
}
