<?php

namespace App\Support;

/** Penyuntingan teks notifikasi untuk paparan pengguna. */
final class NotificationMessage
{
    public static function display(?string $message): string
    {
        if ($message === null || $message === '') {
            return '';
        }

        return preg_replace('/\s*\(NT-003\)/', '', $message) ?? $message;
    }
}
