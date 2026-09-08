<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Models\Application;
use App\Models\DocumentRequirement;

/**
 * Senarai semak Pegawai JP (UR-M04-001).
 * JP menanda setiap item lengkap / tidak_lengkap; hantar ke perakuan hanya jika semua lengkap.
 */
final class JpReviewChecklist
{
    public const LENGKAP = 'lengkap';

    public const TIDAK_LENGKAP = 'tidak_lengkap';

    /**
     * @return array<string, string> key => label
     */
    public static function items(): array
    {
        return [
            'recipient' => 'Nama persatuan, no. ROS, tarikh program, kategori program, no. akaun dan alamat KL lengkap (Borang Penyaluran)',
            'program_syarat' => 'Tujuan sumbangan dan jumlah diisi',
            'dokumen' => 'Lampiran senarai semak lengkap',
            'bajet' => 'Jumlah sumbangan lebih daripada RM0.00',
            'baki' => 'Baki peruntukan ALP mencukupi',
            'polisi' => 'Mematuhi syarat sumbangan (semakan JP)',
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::items());
    }

    /**
     * Item ditanda tidak lengkap (key => label).
     *
     * @param  array<string, string>|null  $checklist
     * @return array<string, string>
     */
    public static function incompleteFrom(?array $checklist): array
    {
        if (! is_array($checklist) || $checklist === []) {
            return [];
        }

        $out = [];
        foreach (self::items() as $key => $label) {
            if (($checklist[$key] ?? null) === self::TIDAK_LENGKAP) {
                $out[$key] = $label;
            }
        }

        return $out;
    }

    /**
     * Item tidak lengkap daripada semakan JP terkini yang memulangkan permohonan.
     *
     * @return array<string, string>
     */
    public static function latestIncompleteFor(Application $application): array
    {
        if ($application->status !== ApplicationStatus::REVISION_REQUIRED) {
            return [];
        }

        $application->loadMissing(['reviews']);

        $review = $application->reviews
            ->where('review_type', ReviewType::SECRETARIAT)
            ->where('decision', ReviewDecision::RETURN_FOR_REVISION)
            ->where('revision_number', $application->revision_number)
            ->sortByDesc('id')
            ->first();

        if (! $review) {
            $review = $application->reviews
                ->where('review_type', ReviewType::SECRETARIAT)
                ->where('decision', ReviewDecision::RETURN_FOR_REVISION)
                ->sortByDesc('id')
                ->first();
        }

        return self::incompleteFrom(is_array($review?->checklist) ? $review->checklist : null);
    }

    public static function hasIncomplete(array $incomplete, string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $incomplete)) {
                return true;
            }
        }

        return false;
    }

    /** Kelas card ringkasan/wizard bila item berkaitan ditanda tidak lengkap. */
    public static function cardClasses(array $incomplete, string ...$keys): string
    {
        if (self::hasIncomplete($incomplete, ...$keys)) {
            return 'rounded-xl border-2 border-red-400 bg-red-50 p-4 ring-1 ring-red-200';
        }

        return 'rounded-xl border border-gray-100 p-4';
    }

    /**
     * Petunjuk sistem (bukan ganti keputusan JP).
     *
     * @return array<string, array{ok: bool, note: string}>
     */
    public static function hints(Application $application, Money $projectedAvailable): array
    {
        $application->loadMissing(['documents']);

        $recipientOk = filled($application->recipient_name)
            && filled($application->recipient_ros_number)
            && filled($application->program_date)
            && filled($application->program_category)
            && filled($application->recipient_bank_account)
            && filled($application->recipient_address)
            && \App\Support\UrsContributionPolicy::isKualaLumpurAddress($application->recipient_address);

        $programOk = filled($application->purpose)
            && $application->requestedAmountMoney()->isPositive();

        $required = DocumentRequirement::requiredFor();
        $uploaded = $application->documents->pluck('document_type')->unique();
        $missingDocs = $required->reject(fn ($t) => $uploaded->contains($t))->count();
        $docsOk = $missingDocs === 0;

        $bajetOk = $application->requestedAmountMoney()->isPositive();

        $bakiOk = ! $projectedAvailable->isNegative();

        return [
            'recipient' => [
                'ok' => $recipientOk,
                'note' => $recipientOk ? 'Medan persatuan diisi' : 'Medan persatuan belum lengkap',
            ],
            'program_syarat' => [
                'ok' => $programOk,
                'note' => $programOk ? 'Medan program & deklarasi ada' : 'Medan program / deklarasi belum lengkap',
            ],
            'dokumen' => [
                'ok' => $docsOk,
                'note' => $docsOk ? 'Dokumen wajib lengkap' : $missingDocs.' dokumen wajib belum dimuat naik',
            ],
            'bajet' => [
                'ok' => $bajetOk,
                'note' => $bajetOk ? 'Jumlah sumbangan diisi' : 'Jumlah sumbangan sifar',
            ],
            'baki' => [
                'ok' => $bakiOk,
                'note' => $bakiOk
                    ? 'Projected Available: RM'.$projectedAvailable->format()
                    : 'Projected Available negatif (RM'.$projectedAvailable->format().')',
            ],
            'polisi' => [
                'ok' => true,
                'note' => 'Sahkan secara manual had permohonan, tempoh & kelayakan',
            ],
        ];
    }

    /**
     * @param  array<string, string>  $checklist
     */
    public static function allLengkap(array $checklist): bool
    {
        foreach (self::keys() as $key) {
            if (($checklist[$key] ?? null) !== self::LENGKAP) {
                return false;
            }
        }

        return true;
    }
}
