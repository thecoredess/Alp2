<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Recipient;

/**
 * Upsert penerima/persatuan dari medan TBL-10 (CRS-ready; AD-005 = ROS).
 */
class RecipientRegistry
{
    public function syncFromApplication(Application $application): ?Recipient
    {
        $ros = trim((string) $application->recipient_ros_number);
        if ($ros === '' || blank($application->recipient_name)) {
            return null;
        }

        $normalized = Recipient::normalizeRos($ros);

        $recipient = Recipient::query()
            ->whereRaw('LOWER(TRIM(ros_number)) = ?', [$normalized])
            ->first();

        if ($recipient) {
            $recipient->update([
                'name' => $application->recipient_name,
                'ros_number' => $ros,
                'bank_account' => $application->recipient_bank_account,
                'address' => $application->recipient_address,
            ]);
        } else {
            $recipient = Recipient::create([
                'name' => $application->recipient_name,
                'ros_number' => $ros,
                'bank_account' => $application->recipient_bank_account,
                'address' => $application->recipient_address,
            ]);
        }

        if ($application->recipient_id !== $recipient->id) {
            $application->forceFill(['recipient_id' => $recipient->id])->save();
        }

        return $recipient;
    }
}
