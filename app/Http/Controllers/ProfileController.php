<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\ProfileAvatarIcons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Profil kendiri — kemaskini nama / e-mel / avatar / unit,
 * serta maklumat hubungan ALP (jika akaun dikaitkan).
 */
class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user()->load('alp');

        return view('profile.edit', [
            'user' => $user,
            'avatarIcons' => ProfileAvatarIcons::options(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data, $request) {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'unit' => $data['unit'] ?? $user->unit,
            ];

            if ($request->boolean('remove_avatar')) {
                $this->deleteAvatarFile($user->avatar_path);
                $payload['avatar_path'] = null;
                $payload['avatar_icon'] = null;
            } elseif ($request->hasFile('avatar')) {
                $this->deleteAvatarFile($user->avatar_path);
                $payload['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
                $payload['avatar_icon'] = null;
            } elseif (array_key_exists('avatar_icon', $data)) {
                $icon = $data['avatar_icon'] ?: null;
                if ($icon !== null) {
                    $this->deleteAvatarFile($user->avatar_path);
                    $payload['avatar_path'] = null;
                    $payload['avatar_icon'] = $icon;
                } elseif (! $request->hasFile('avatar') && ! $request->boolean('remove_avatar')) {
                    // kekalkan avatar sedia ada jika ikon kosong & tiada muat naik
                }
            }

            $user->forceFill($payload)->save();

            if ($user->alp_id && $user->alp) {
                $user->alp->forceFill([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['contact_email'] ?? null,
                    'address' => $data['address'] ?? null,
                ])->save();
            }
        });

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Profil anda telah dikemas kini.');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->deleteAvatarFile($user->avatar_path);
        $user->forceFill([
            'avatar_path' => null,
            'avatar_icon' => null,
        ])->save();

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Gambar / ikon profil telah dibuang.');
    }

    private function deleteAvatarFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
