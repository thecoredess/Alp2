<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Recipient;

/** Upsert penerima dari medan Borang Penyaluran (nama + akaun bank). */
class RecipientRegistry
{
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
