<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/** Pautan awam (7 hari) untuk persatuan isi Borang Penyaluran. */
class ApplicationAssociationInviteService
{
    public const VALID_DAYS = 7;

    public function issue(Application $application, User $issuer): string
    {
        abort_unless($application->isDraft(), 422, 'Pautan persatuan hanya untuk permohonan draf.');

        $plain = Str::random(48);

        $application->forceFill([
            'association_invite_token' => $this->hashToken($plain),
            'association_invite_expires_at' => now()->addDays(self::VALID_DAYS),
            'association_invite_filled_at' => null,
            'association_pdpa_consented_at' => null,
            'association_invite_issued_by' => $issuer->id,
            'association_invite_plain_encrypted' => Crypt::encryptString($plain),
        ])->save();

        return URL::route('association-form.show', ['token' => $plain]);
    }

    public function publicUrlFor(Application $application): ?string
    {
        if (! $this->isInviteActive($application)) {
            return null;
        }

        if (blank($application->association_invite_plain_encrypted)) {
            return null;
        }

        try {
            $plain = Crypt::decryptString($application->association_invite_plain_encrypted);

            return URL::route('association-form.show', ['token' => $plain]);
        } catch (\Throwable) {
            return null;
        }
    }

    public function findByPlainToken(string $plain): ?Application
    {
        $plain = trim($plain);
        if (strlen($plain) < 32) {
            return null;
        }

        return Application::query()
            ->where('association_invite_token', $this->hashToken($plain))
            ->first();
    }

    public function findActiveByPlainToken(string $plain): ?Application
    {
        $application = $this->findByPlainToken($plain);

        return $application && $this->isInviteActive($application) ? $application : null;
    }

    public function isInviteActive(Application $application): bool
    {
        if (! $application->isDraft()) {
            return false;
        }

        if (blank($application->association_invite_token)) {
            return false;
        }

        $expires = $application->association_invite_expires_at;

        return $expires !== null && $expires->isFuture();
    }

    public function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
