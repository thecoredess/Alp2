<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Hab ketetapan: Ketetapan Pengguna + Ketetapan Sistem.
 */
class SettingsHubController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.hub', [
            'user' => $request->user(),
            'canSystem' => $this->canManageSystem($request),
        ]);
    }

    public function user(Request $request): View
    {
        return view('settings.user', [
            'user' => $request->user()->load('alp'),
        ]);
    }

    public function system(Request $request): View
    {
        abort_unless($this->canManageSystem($request), 403);

        return view('settings.system', [
            'user' => $request->user(),
        ]);
    }

    private function canManageSystem(Request $request): bool
    {
        $user = $request->user();

        return $user->can('users.view')
            || $user->can('financial_years.view')
            || $user->can('settings.manage')
            || $user->can('approval_matrix.view');
    }
}
