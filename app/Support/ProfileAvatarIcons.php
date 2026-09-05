<?php

namespace App\Support;

/**
 * Ikon avatar pratetap untuk profil pengguna.
 */
final class ProfileAvatarIcons
{
    /** @return array<string, string> kod => label */
    public static function options(): array
    {
        return [
            'user' => 'Pengguna',
            'briefcase' => 'Profesional',
            'building' => 'Organisasi',
            'star' => 'Bintang',
            'shield' => 'Pentadbir',
            'heart' => 'Komuniti',
        ];
    }

    public static function isValid(?string $icon): bool
    {
        return $icon !== null && array_key_exists($icon, self::options());
    }
}
