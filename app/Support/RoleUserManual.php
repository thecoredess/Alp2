<?php

namespace App\Support;

use App\Enums\RoleName;
use App\Models\User;

/**
 * Manual pengguna Word mengikut peranan (docs/manual-pengguna).
 */
final class RoleUserManual
{
    private const RELATIVE_DIR = 'docs/manual-pengguna';

    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $filename,
        public readonly string $downloadName,
    ) {}

    /** Fail Word pada cakera (guna salinan `-terkini` jika lebih baharu daripada fail utama). */
    private static function storageFilename(string $canonicalDocx): string
    {
        $terkini = preg_replace('/\.docx$/', '-terkini.docx', $canonicalDocx);
        $dir = base_path(self::RELATIVE_DIR);
        $basePath = $dir.DIRECTORY_SEPARATOR.$canonicalDocx;
        $terkiniPath = $dir.DIRECTORY_SEPARATOR.$terkini;

        if (is_file($terkiniPath) && (! is_file($basePath) || filemtime($terkiniPath) >= filemtime($basePath))) {
            return $terkini;
        }

        return $canonicalDocx;
    }

    public function absolutePath(): string
    {
        return base_path(self::RELATIVE_DIR.DIRECTORY_SEPARATOR.$this->filename);
    }

    public function exists(): bool
    {
        return is_file($this->absolutePath());
    }

    public static function alp(): self
    {
        return new self(
            'alp',
            'Manual Pengguna — Ahli Lembaga Penasihat (ALP)',
            self::storageFilename('Manual-Pengguna-ALP.docx'),
            'Manual-Pengguna-ALP.docx',
        );
    }

    public static function adminJp(): self
    {
        return new self(
            'admin_jp',
            'Manual Pengguna — Admin Jabatan Pentadbiran (Admin JP)',
            self::storageFilename('Manual-Pengguna-Admin-JP.docx'),
            'Manual-Pengguna-Admin-JP.docx',
        );
    }

    public static function pengarahJp(): self
    {
        return new self(
            'pengarah_jp',
            'Manual Pengguna — Pengarah Jabatan Pentadbiran (Pengarah JP)',
            self::storageFilename('Manual-Pengguna-Pengarah-JP.docx'),
            'Manual-Pengguna-Pengarah-JP.docx',
        );
    }

    /** Manual Word yang sepadan dengan peranan pengguna (atau null). */
    public static function forUser(User $user): ?self
    {
        if ($user->hasAnyRole([RoleName::ALP->value, RoleName::URUSSETIA_ALP->value])) {
            return self::alp();
        }

        if ($user->canMakeFullJpReviewDecision()) {
            return self::adminJp();
        }

        if ($user->hasRole(RoleName::PELULUS->value) && $user->can('applications.approve')) {
            return self::pengarahJp();
        }

        return null;
    }

    /** Kunci audiens untuk paparan ringkasan dalam sistem (selari ManualController). */
    public static function audienceKey(User $user): string
    {
        if ($user->hasAnyRole([RoleName::ALP->value, RoleName::URUSSETIA_ALP->value])) {
            return 'alp';
        }

        if ($user->hasRole(RoleName::PENGURUSAN->value)) {
            return 'pepu';
        }

        if ($user->can('applications.review.secretariat')
            || $user->can('applications.approve')
            || $user->can('payments.view')
            || $user->can('applications.view_all')) {
            return 'jp';
        }

        return 'umum';
    }

    public static function audienceLabel(User $user): string
    {
        return match (self::audienceKey($user)) {
            'alp' => 'ALP / Urus Setia ALP',
            'pepu' => RoleName::PENGURUSAN->label(),
            'jp' => $user->canMakeFullJpReviewDecision()
                ? RoleName::SYSTEM_ADMIN->label()
                : ($user->hasRole(RoleName::PELULUS->value)
                    ? RoleName::PELULUS->label()
                    : ($user->hasRole(RoleName::PEGAWAI_URUSSETIA->value)
                        ? RoleName::PEGAWAI_URUSSETIA->label()
                        : 'Pegawai dalaman')),
            default => 'Umum',
        };
    }
}
