<?php

namespace App\Support;

use App\Enums\RoleName;
use App\Models\Application;
use App\Models\SystemSetting;
use App\Models\User;

/** Templat e-mel & notifikasi mengikut peranan — diselenggara Admin JP. */
final class NotificationTemplates
{
    public const KEY_MAIL_GLOBALLY_ENABLED = 'notification.mail_globally_enabled';

    /** @return array<string, string> event => label */
    public static function events(): array
    {
        return [
            'submission_acknowledged' => 'Pengesahan hantar permohonan (ALP)',
            'submitted' => 'Permohonan dihantar (Admin JP)',
            'awaiting_pegawai_jp' => 'Menunggu pengesyoran Pegawai JP',
            'awaiting_peraku' => 'Menunggu perakuan TP/Pengarah',
            'awaiting_pepu' => 'Menunggu kelulusan PEPU',
            'revision_required' => 'Permohonan dikembalikan',
            'approved' => 'Permohonan diluluskan',
            'awaiting_payment' => 'Sedia proses bayaran / baucar',
            'rejected' => 'Permohonan ditolak',
            'payment_voucher' => 'Baucar disedia',
            'payment_paid' => 'Pembayaran selesai',
            'report_card_reminder_upcoming' => 'Peringatan laporan (7 hari lagi)',
            'report_card_reminder_overdue' => 'Peringatan laporan (tertunggak)',
            'report_card_submitted' => 'Laporan aktiviti dihantar',
            'report_card_awaiting_pegawai_jp' => 'Laporan menunggu Pegawai JP',
            'report_card_approved' => 'Laporan aktiviti disahkan',
            'report_card_returned' => 'Laporan aktiviti dikembalikan',
        ];
    }

    /** Peranan yang menerima notifikasi aliran kerja. */
    public static function roles(): array
    {
        return [
            RoleName::ALP->value => RoleName::ALP->label(),
            RoleName::SYSTEM_ADMIN->value => RoleName::SYSTEM_ADMIN->label(),
            RoleName::PEGAWAI_URUSSETIA->value => RoleName::PEGAWAI_URUSSETIA->label(),
            RoleName::PELULUS->value => RoleName::PELULUS->label(),
            RoleName::PENGURUSAN->value => RoleName::PENGURUSAN->label(),
            RoleName::PEGAWAI_KEWANGAN->value => RoleName::PEGAWAI_KEWANGAN->label(),
        ];
    }

    /** Peristiwa relevan bagi setiap peranan (paparan UI). */
    public static function eventsForRole(string $role): array
    {
        $map = [
            RoleName::ALP->value => [
                'submission_acknowledged',
                'revision_required', 'approved', 'rejected', 'payment_voucher', 'payment_paid',
                'report_card_reminder_upcoming', 'report_card_reminder_overdue',
                'report_card_approved', 'report_card_returned',
            ],
            RoleName::SYSTEM_ADMIN->value => ['submitted', 'report_card_submitted'],
            RoleName::PEGAWAI_URUSSETIA->value => ['awaiting_pegawai_jp', 'report_card_awaiting_pegawai_jp'],
            RoleName::PELULUS->value => ['awaiting_peraku'],
            RoleName::PENGURUSAN->value => ['awaiting_pepu'],
            RoleName::PEGAWAI_KEWANGAN->value => ['awaiting_payment'],
        ];

        return $map[$role] ?? [];
    }

    public static function mailGloballyEnabled(): bool
    {
        return SystemSetting::getBool(self::KEY_MAIL_GLOBALLY_ENABLED, true);
    }

    public static function roleMailEnabled(string $role): bool
    {
        return SystemSetting::getBool(self::roleMailKey($role), true);
    }

    public static function eventRoleMailEnabled(string $event, string $role): bool
    {
        return SystemSetting::getBool(self::eventRoleMailKey($event, $role), true);
    }

    public static function mailEnabledFor(string $event, User $user): bool
    {
        if (! MailSettings::isEnabled() || ! self::mailGloballyEnabled()) {
            return false;
        }

        $role = self::primaryRole($user);
        if ($role === null) {
            return false;
        }

        if (! self::roleMailEnabled($role)) {
            return false;
        }

        return self::eventRoleMailEnabled($event, $role);
    }

    public static function subject(string $event, User $user): string
    {
        $role = self::primaryRole($user) ?? RoleName::ALP->value;
        $custom = SystemSetting::get(self::eventRoleSubjectKey($event, $role));
        if (filled($custom)) {
            return (string) $custom;
        }

        $custom = SystemSetting::get(self::eventSubjectKey($event));
        if (filled($custom)) {
            return (string) $custom;
        }

        return self::defaultSubject($event);
    }

    public static function body(string $event, User $user, string $fallbackMessage): string
    {
        $role = self::primaryRole($user) ?? RoleName::ALP->value;
        $custom = SystemSetting::get(self::eventRoleBodyKey($event, $role));
        if (filled($custom)) {
            return (string) $custom;
        }

        $custom = SystemSetting::get(self::eventBodyKey($event));
        if (filled($custom)) {
            return (string) $custom;
        }

        return $fallbackMessage;
    }

    /** Peristiwa yang memerlukan tindakan penerima (bukan sekadar makluman). */
    public static function isActionEvent(string $event): bool
    {
        return in_array($event, [
            'submitted',
            'awaiting_pegawai_jp',
            'awaiting_peraku',
            'awaiting_pepu',
            'awaiting_payment',
            'revision_required',
            'report_card_reminder_upcoming',
            'report_card_reminder_overdue',
            'report_card_submitted',
            'report_card_awaiting_pegawai_jp',
        ], true);
    }

    public static function render(
        string $template,
        Application $application,
        User $user,
        string $message,
        array $extraReplacements = [],
    ): string {
        $replacements = array_merge([
            '{user_name}' => $user->name,
            '{application_number}' => $application->application_number,
            '{project_title}' => (string) $application->project_title,
            '{recipient_name}' => (string) ($application->recipient_name ?? ''),
            '{message}' => $message,
            '{action_url}' => route('applications.show', $application),
            '{action_label}' => 'Buka permohonan',
            '{amount}' => $application->requestedAmountMoney()->format(),
        ], $extraReplacements);

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /** @return array<string, array<string, array{subject: string, body: string, mail_enabled: bool}>> */
    public static function matrix(): array
    {
        $matrix = [];
        foreach (self::roles() as $role => $label) {
            $matrix[$role] = [];
            foreach (self::eventsForRole($role) as $event) {
                $matrix[$role][$event] = [
                    'subject' => (string) SystemSetting::get(
                        self::eventRoleSubjectKey($event, $role),
                        self::defaultSubject($event),
                    ),
                    'body' => (string) SystemSetting::get(
                        self::eventRoleBodyKey($event, $role),
                        self::defaultBody($event),
                    ),
                    'mail_enabled' => self::eventRoleMailEnabled($event, $role),
                ];
            }
        }

        return $matrix;
    }

    public static function seedDefaults(): void
    {
        if (SystemSetting::get(self::KEY_MAIL_GLOBALLY_ENABLED) === null) {
            SystemSetting::set(self::KEY_MAIL_GLOBALLY_ENABLED, true);
        }

        foreach (self::roles() as $role => $_) {
            if (SystemSetting::get(self::roleMailKey($role)) === null) {
                SystemSetting::set(self::roleMailKey($role), true);
            }

            foreach (self::eventsForRole($role) as $event) {
                if (SystemSetting::get(self::eventRoleSubjectKey($event, $role)) === null) {
                    SystemSetting::set(self::eventRoleSubjectKey($event, $role), self::defaultSubject($event));
                }
                if (SystemSetting::get(self::eventRoleBodyKey($event, $role)) === null) {
                    SystemSetting::set(self::eventRoleBodyKey($event, $role), self::defaultBody($event));
                }
                if (SystemSetting::get(self::eventRoleMailKey($event, $role)) === null) {
                    SystemSetting::set(self::eventRoleMailKey($event, $role), true);
                }
            }
        }
    }

    public static function defaultSubject(string $event): string
    {
        if ($event === 'submission_acknowledged') {
            return 'Permohonan {application_number} berjaya dihantar';
        }

        $label = self::events()[$event] ?? 'Kemaskini permohonan ALP';

        return self::isActionEvent($event)
            ? 'Tindakan diperlukan — '.$label
            : $label;
    }

    public static function defaultBody(string $event): string
    {
        return match ($event) {
            'submission_acknowledged' => 'Permohonan anda {application_number} telah berjaya dihantar dan akan diproses oleh Jabatan Pentadbiran.',
            'submitted' => 'Anda mempunyai tindakan yang perlu diselesaikan: semak permohonan baharu {application_number} ({project_title}).',
            'awaiting_pegawai_jp' => 'Anda mempunyai tindakan yang perlu diselesaikan: permohonan {application_number} telah disemak Admin JP dan menunggu pengesyoran Pegawai JP.',
            'awaiting_peraku' => 'Anda mempunyai tindakan yang perlu diselesaikan: berikan perakuan untuk permohonan {application_number}.',
            'awaiting_pepu' => 'Anda mempunyai tindakan yang perlu diselesaikan: berikan kelulusan PEPU untuk permohonan {application_number}.',
            'revision_required' => 'Anda mempunyai tindakan yang perlu diselesaikan: permohonan {application_number} dikembalikan untuk pembetulan.',
            'approved' => 'Permohonan {application_number} telah diluluskan.',
            'awaiting_payment' => 'Anda mempunyai tindakan yang perlu diselesaikan: sediakan baucar bagi permohonan {application_number} (RM {amount}).',
            'rejected' => 'Permohonan {application_number} telah ditolak.',
            'payment_voucher' => 'Baucar permohonan {application_number} telah disedia. Sila muat naik laporan aktiviti.',
            'payment_paid' => 'Pembayaran permohonan {application_number} telah direkod.',
            'report_card_reminder_upcoming' => 'Anda mempunyai tindakan yang perlu diselesaikan: muat naik laporan aktiviti untuk {application_number} sebelum tarikh akhir.',
            'report_card_reminder_overdue' => 'Anda mempunyai tindakan yang perlu diselesaikan: laporan aktiviti {application_number} masih belum dimuat naik (tertunggak).',
            'report_card_submitted' => 'Anda mempunyai tindakan yang perlu diselesaikan: semak laporan aktiviti {application_number} yang dimuat naik ALP.',
            'report_card_awaiting_pegawai_jp' => 'Anda mempunyai tindakan yang perlu diselesaikan: sahkan laporan aktiviti {application_number} selepas semakan Admin JP.',
            'report_card_approved' => 'Laporan aktiviti {application_number} telah disahkan.',
            'report_card_returned' => 'Laporan aktiviti {application_number} dikembalikan untuk pembetulan.',
            default => 'Terdapat kemaskini bagi permohonan {application_number}.',
        };
    }

    public static function roleMailKey(string $role): string
    {
        return 'notification.role.'.$role.'.mail_enabled';
    }

    public static function eventSubjectKey(string $event): string
    {
        return 'notification.'.$event.'.subject';
    }

    public static function eventBodyKey(string $event): string
    {
        return 'notification.'.$event.'.body';
    }

    public static function eventRoleMailKey(string $event, string $role): string
    {
        return 'notification.'.$event.'.role.'.$role.'.mail_enabled';
    }

    public static function eventRoleSubjectKey(string $event, string $role): string
    {
        return 'notification.'.$event.'.role.'.$role.'.subject';
    }

    public static function eventRoleBodyKey(string $event, string $role): string
    {
        return 'notification.'.$event.'.role.'.$role.'.body';
    }

    private static function primaryRole(User $user): ?string
    {
        $role = $user->roles->first()?->name;

        return is_string($role) ? $role : null;
    }
}
