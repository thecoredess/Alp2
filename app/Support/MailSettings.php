<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;

/** Konfigurasi SMTP — diselenggara Super Admin melalui tetapan sistem. */
final class MailSettings
{
    public const KEY_ENABLED = 'mail.enabled';

    public const KEY_MAILER = 'mail.mailer';

    public const KEY_HOST = 'mail.host';

    public const KEY_PORT = 'mail.port';

    public const KEY_USERNAME = 'mail.username';

    public const KEY_PASSWORD = 'mail.password';

    public const KEY_ENCRYPTION = 'mail.encryption';

    public const KEY_FROM_ADDRESS = 'mail.from_address';

    public const KEY_FROM_NAME = 'mail.from_name';

    public const KEY_VERIFY_PEER = 'mail.verify_peer';

    public static function isEnabled(): bool
    {
        return SystemSetting::getBool(self::KEY_ENABLED, false);
    }

    /** @return array<string, mixed> */
    public static function all(): array
    {
        $defaults = self::defaults();

        return [
            'enabled' => self::isEnabled(),
            'mailer' => (string) SystemSetting::get(self::KEY_MAILER, $defaults[self::KEY_MAILER]),
            'host' => (string) SystemSetting::get(self::KEY_HOST, $defaults[self::KEY_HOST]),
            'port' => (int) SystemSetting::get(self::KEY_PORT, $defaults[self::KEY_PORT]),
            'username' => (string) SystemSetting::get(self::KEY_USERNAME, $defaults[self::KEY_USERNAME]),
            'encryption' => (string) SystemSetting::get(self::KEY_ENCRYPTION, $defaults[self::KEY_ENCRYPTION]),
            'from_address' => (string) SystemSetting::get(self::KEY_FROM_ADDRESS, $defaults[self::KEY_FROM_ADDRESS]),
            'from_name' => (string) SystemSetting::get(self::KEY_FROM_NAME, $defaults[self::KEY_FROM_NAME]),
            'verify_peer' => self::verifyPeer(),
            'has_password' => self::hasPassword(),
        ];
    }

    public static function verifyPeer(): bool
    {
        $stored = SystemSetting::cached();

        if (array_key_exists(self::KEY_VERIFY_PEER, $stored)) {
            return SystemSetting::getBool(self::KEY_VERIFY_PEER, true);
        }

        return (bool) config('mail.mailers.smtp.verify_peer', true);
    }

    public static function hasPassword(): bool
    {
        return filled(SystemSetting::get(self::KEY_PASSWORD));
    }

    public static function storePassword(?string $plain): void
    {
        if ($plain === null || $plain === '') {
            return;
        }

        SystemSetting::set(self::KEY_PASSWORD, Crypt::encryptString($plain));
    }

    public static function password(): ?string
    {
        $encrypted = SystemSetting::get(self::KEY_PASSWORD);

        if (! filled($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Guna tetapan DB jika diaktifkan; jika tidak kekal pada .env. */
    public static function applyToConfig(): void
    {
        if (! self::isEnabled()) {
            return;
        }

        $defaults = self::defaults();
        $encryption = self::encryptionValue();
        $port = (int) SystemSetting::get(self::KEY_PORT, $defaults[self::KEY_PORT]);

        config([
            'mail.default' => (string) SystemSetting::get(self::KEY_MAILER, $defaults[self::KEY_MAILER]),
            'mail.mailers.smtp.host' => (string) SystemSetting::get(self::KEY_HOST, $defaults[self::KEY_HOST]),
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => (string) SystemSetting::get(self::KEY_USERNAME, $defaults[self::KEY_USERNAME]),
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.auto_tls' => $encryption !== null,
            'mail.mailers.smtp.verify_peer' => self::verifyPeer(),
            'mail.from.address' => (string) SystemSetting::get(self::KEY_FROM_ADDRESS, $defaults[self::KEY_FROM_ADDRESS]),
            'mail.from.name' => (string) SystemSetting::get(self::KEY_FROM_NAME, $defaults[self::KEY_FROM_NAME]),
        ]);

        $password = self::password();
        if ($password !== null) {
            config(['mail.mailers.smtp.password' => $password]);
        }
    }

    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            self::KEY_ENABLED => '0',
            self::KEY_MAILER => (string) config('mail.default', 'smtp'),
            self::KEY_HOST => (string) config('mail.mailers.smtp.host', '127.0.0.1'),
            self::KEY_PORT => (string) config('mail.mailers.smtp.port', 587),
            self::KEY_USERNAME => (string) config('mail.mailers.smtp.username', ''),
            self::KEY_ENCRYPTION => (string) (config('mail.mailers.smtp.encryption') ?? 'tls'),
            self::KEY_FROM_ADDRESS => (string) config('mail.from.address', 'noreply@dbkl.gov.my'),
            self::KEY_FROM_NAME => (string) config('mail.from.name', 'Sistem ALP DBKL'),
            self::KEY_VERIFY_PEER => config('mail.mailers.smtp.verify_peer', true) ? '1' : '0',
        ];
    }

    private static function encryptionValue(): ?string
    {
        $value = (string) SystemSetting::get(self::KEY_ENCRYPTION, self::defaults()[self::KEY_ENCRYPTION]);

        return in_array($value, ['tls', 'ssl'], true) ? $value : null;
    }
}
