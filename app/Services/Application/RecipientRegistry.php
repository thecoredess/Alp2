<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Recipient;
/** Upsert penerima dari medan Borang Penyaluran (nama + akaun bank). */
class RecipientRegistry
{
    /**
     * Persatuan yang pernah diisi dalam permohonan ALP (terkini mengikut ROS / nama).
     *
     * @return list<array{name: string, ros_number: string, bank_account: string, address: string}>
     */
    public function suggestionsForAlp(int $alpId, ?string $query = null, int $limit = 8): array
    {
        $term = trim((string) $query);

        $applications = Application::query()
            ->where('alp_id', $alpId)
            ->whereNotNull('recipient_name')
            ->where('recipient_name', '!=', '')
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where('recipient_name', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit(80)
            ->get([
                'recipient_name',
                'recipient_ros_number',
                'recipient_bank_account',
                'recipient_address',
            ]);

        $seen = [];
        $out = [];

        foreach ($applications as $application) {
            $key = self::suggestionDedupeKey(
                (string) $application->recipient_name,
                $application->recipient_ros_number,
            );

            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $ros = trim((string) $application->recipient_ros_number);

            $out[] = [
                'name' => trim((string) $application->recipient_name),
                'ros_number' => $ros,
                'bank_account' => trim((string) $application->recipient_bank_account),
                'address' => trim((string) $application->recipient_address),
            ];

            if (count($out) >= $limit) {
                break;
            }
        }

        return $out;
    }

    /** Satu baris cadangan setiap nama persatuan (permohonan terkini menang). */
    public static function suggestionDedupeKey(string $name, ?string $ros): string
    {
        $nameKey = self::normalizeNameKey($name);
        if ($nameKey !== '') {
            return 'name:'.$nameKey;
        }

        $rosKey = self::normalizeRosKey($ros);

        return $rosKey !== '' ? 'ros:'.$rosKey : '';
    }

    public static function normalizeNameKey(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return mb_strtolower($name);
    }

    public static function normalizeRosKey(?string $ros): string
    {
        $ros = mb_strtolower(trim((string) $ros));

        return preg_replace('/[^a-z0-9]/', '', $ros) ?? '';
    }

    /** No. ROS dari permohonan terkini ALP yang sama nama persatuan (normal). */
    public function recordedRosForPersatuanName(int $alpId, string $name): ?string
    {
        $nameKey = self::normalizeNameKey($name);
        if ($nameKey === '') {
            return null;
        }

        $applications = Application::query()
            ->where('alp_id', $alpId)
            ->whereNotNull('recipient_name')
            ->where('recipient_name', '!=', '')
            ->whereNotNull('recipient_ros_number')
            ->where('recipient_ros_number', '!=', '')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['recipient_name', 'recipient_ros_number']);

        foreach ($applications as $application) {
            if (self::normalizeNameKey($application->recipient_name) === $nameKey) {
                return trim((string) $application->recipient_ros_number);
            }
        }

        return null;
    }

    /**
     * @return array{mismatch: bool, recorded_ros?: string}
     */
    public function rosMismatchForPersatuan(int $alpId, string $name, string $ros): array
    {
        $recorded = $this->recordedRosForPersatuanName($alpId, $name);
        if ($recorded === null || trim($ros) === '') {
            return ['mismatch' => false];
        }

        if (self::normalizeRosKey($recorded) === self::normalizeRosKey($ros)) {
            return ['mismatch' => false];
        }

        return [
            'mismatch' => true,
            'recorded_ros' => $recorded,
        ];
    }

    public function syncFromApplication(Application $application): ?Recipient
    {
        if (blank($application->recipient_name) || blank($application->recipient_bank_account)) {
            return null;
        }

        $account = trim((string) $application->recipient_bank_account);

        $recipient = Recipient::query()
            ->whereRaw('REPLACE(TRIM(bank_account), " ", "") = ?', [preg_replace('/\s+/', '', $account)])
            ->first();

        if (! $recipient && filled($application->recipient_ros_number)) {
            $recipient = Recipient::query()
                ->where('ros_number', $application->recipient_ros_number)
                ->first();
        }

        $payload = [
            'name' => $application->recipient_name,
            'bank_account' => $account,
        ];

        if (filled($application->recipient_ros_number)) {
            $payload['ros_number'] = $application->recipient_ros_number;
        }

        if (filled($application->recipient_address)) {
            $payload['address'] = $application->recipient_address;
        }

        if ($recipient) {
            $recipient->update($payload);
        } else {
            $recipient = Recipient::create($payload + [
                'ros_number' => $application->recipient_ros_number ?: ('AKAUN-'.$account),
            ]);
        }

        if ($application->recipient_id !== $recipient->id) {
            $application->forceFill(['recipient_id' => $recipient->id])->save();
        }

        return $recipient;
    }
}
