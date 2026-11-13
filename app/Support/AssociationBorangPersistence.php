<?php

namespace App\Support;

use App\Models\Application;

/** Kemas kini rekod permohonan daripada borang persatuan / pautan awam. */
final class AssociationBorangPersistence
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function attributesFromValidated(array $data, bool $markSubmittedToAlp): array
    {
        $attrs = [
            ...KertasKerjaGuide::normalizedAttributes($data),
            'recipient_name' => $data['recipient_name'],
            'recipient_ros_number' => $data['recipient_ros_number'],
            'program_date' => $data['program_date'],
            'program_category' => $data['program_category'],
            'recipient_bank_account' => $data['recipient_bank_account'],
            'recipient_address' => $data['recipient_address'],
            'requested_amount' => number_format((float) $data['requested_amount'], 2, '.', ''),
        ];

        if ($markSubmittedToAlp) {
            $attrs['association_invite_filled_at'] = now();
            $attrs['association_pdpa_consented_at'] = now();
        }

        return $attrs;
    }

    public static function shouldAutosaveFromRequest(\Illuminate\Http\Request $request): bool
    {
        return $request->filled('recipient_name') && $request->filled('autosave_borang');
    }
}
