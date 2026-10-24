<?php

namespace App\Http\Requests;

use App\Models\Application;
use App\Services\Application\ApplicationAssociationInviteService;
use Illuminate\Foundation\Http\FormRequest;

/** Muat naik lampiran melalui pautan awam persatuan (token sah & draf). */
class AssociationPublicDocumentRequest extends FormRequest
{
    use Concerns\ValidatesContributionDocumentUpload;

    public function authorize(): bool
    {
        $token = (string) $this->route('token');
        $application = app(ApplicationAssociationInviteService::class)->findActiveByPlainToken($token);

        if ($application) {
            app()->instance('associationInviteApplication', $application);

            return true;
        }

        return false;
    }

    public function inviteApplication(): ?Application
    {
        $bound = app()->bound('associationInviteApplication')
            ? app('associationInviteApplication')
            : null;

        return $bound instanceof Application ? $bound : null;
    }
}
