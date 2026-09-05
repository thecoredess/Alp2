<?php

namespace App\Support;

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
            'recipient' => 'Maklumat penerima / persatuan (nama, No. ROS, akaun bank, alamat) lengkap',
            'program_syarat' => 'Syarat program dipenuhi (kategori, lokasi, tarikh program, deklarasi BR-012)',
            'dokumen' => 'Dokumen sokongan wajib (TBL-9) lengkap',
            'bajet' => 'Pecahan bajet wujud dan jumlah dipohon konsisten',
            'baki' => 'Baki peruntukan ALP mencukupi (Projected Available tidak negatif)',
            'polisi' => 'Mematuhi polisi URS / kelayakan (semakan JP)',
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::items());
    }

    /**
     * Petunjuk sistem (bukan ganti keputusan JP).
     *
     * @return array<string, array{ok: bool, note: string}>
     */
    public static function hints(Application $application, Money $projectedAvailable): array
    {
        $application->loadMissing(['budgetItems', 'documents']);

        $recipientOk = filled($application->recipient_name)
            && filled($application->recipient_ros_number)
            && filled($application->recipient_bank_account)
            && filled($application->recipient_address);

        $programOk = filled($application->program_category)
            && filled($application->location)
            && filled($application->proposed_start_date)
            && filled($application->compliance_declared_at);

        $required = DocumentRequirement::requiredFor($application->application_type);
        $uploaded = $application->documents->pluck('document_type')->unique();
        $missingDocs = $required->reject(fn ($t) => $uploaded->contains($t))->count();
        $docsOk = $missingDocs === 0;

        $bajetOk = $application->budgetItems->isNotEmpty()
            && ! $application->requestedAmountMoney()->isZero();

        $bakiOk = ! $projectedAvailable->isNegative();

        return [
            'recipient' => [
                'ok' => $recipientOk,
                'note' => $recipientOk ? 'Medan penerima diisi' : 'Medan penerima belum lengkap',
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
                'note' => $bajetOk ? 'Item bajet wujud' : 'Tiada item bajet / jumlah sifar',
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
