<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Tukar kata laluan sendiri (pengguna yang telah log masuk).
 * Borang dipaparkan dalam halaman profil — laluan GET dialih ke sana.
 */
class ChangePasswordController extends Controller
{
    public function edit(): RedirectResponse
    {
        return redirect()->to(route('profile.edit').'#kata-laluan');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', PasswordRule::defaults()],
        ], [
            'current_password.current_password' => 'Kata laluan semasa tidak betul.',
            'password.different' => 'Kata laluan baharu mestilah berbeza daripada kata laluan semasa.',
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        return redirect()
            ->to(route('profile.edit').'#kata-laluan')
            ->with('status', 'Kata laluan anda telah dikemas kini.');
    }
}
