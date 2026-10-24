<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\Application\ApplicationAssociationInviteService;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationAssociationInviteController extends Controller
{
    public function __construct(
        private readonly ApplicationAssociationInviteService $invites,
        private readonly AuditService $audit,
    ) {}

    /** Jana / perbaharui pautan awam (7 hari) untuk persatuan isi borang. */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $url = $this->invites->issue($application, $request->user());

        $this->audit->log('APPLICATION_ASSOCIATION_INVITE_ISSUED', $application, null, [
            'expires_at' => $application->fresh()->association_invite_expires_at?->toIso8601String(),
        ]);

        return back()->with([
            'status' => 'Pautan untuk persatuan dijana. Sah selama '.ApplicationAssociationInviteService::VALID_DAYS.' hari.',
            'association_invite_url' => $url,
        ]);
    }
}
