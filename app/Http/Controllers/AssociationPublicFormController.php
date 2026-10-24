<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\AssociationFillRequest;
use App\Http\Requests\AssociationPublicDocumentRequest;
use App\Models\ApplicationDocument;
use App\Models\DocumentRequirement;
use App\Services\Application\ApplicationAssociationInviteService;
use App\Services\Documents\ApplicationDocumentStorage;
use App\Services\Application\RecipientRegistry;
use App\Services\Audit\AuditService;
use App\Services\Notification\ApplicationNotifier;
use App\Support\ApplicationAmountValidator;
use App\Support\UrsContributionPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssociationPublicFormController extends Controller
{
    public function __construct(
        private readonly ApplicationAssociationInviteService $invites,
        private readonly RecipientRegistry $recipients,
        private readonly AuditService $audit,
        private readonly ApplicationNotifier $notifier,
        private readonly ApplicationDocumentStorage $documentStorage,
    ) {}

    public function show(string $token): View
    {
        $application = $this->invites->findActiveByPlainToken($token);

        if (! $application) {
            return view('association-form.unavailable');
        }

        $application->load(['alp', 'financialYear', 'documents']);

        $order = collect(DocumentType::contributionAttachments())
            ->map(fn (DocumentType $t) => $t->value)
            ->flip();

        $requirements = DocumentRequirement::activeFor()
            ->sortBy(fn (DocumentRequirement $r) => $order[$r->document_type->value] ?? 99)
            ->values();

        return view('association-form.show', [
            'application' => $application,
            'token' => $token,
            'requirements' => $requirements,
            'amountLimits' => ApplicationAmountValidator::limitsFor(
                $application->alp,
                $application->financialYear,
                $application->id,
            ),
            'programDateMin' => UrsContributionPolicy::minimumProgramDate()->format('Y-m-d'),
            'expiresAt' => $application->association_invite_expires_at,
        ]);
    }

    public function update(AssociationFillRequest $request, string $token): RedirectResponse
    {
        $application = $request->inviteApplication();
        abort_if($application === null, 404);

        $data = $request->validated();

        $application->update([
            ...\App\Support\KertasKerjaGuide::normalizedAttributes($data),
            'recipient_name' => $data['recipient_name'],
            'recipient_ros_number' => $data['recipient_ros_number'],
            'program_date' => $data['program_date'],
            'program_category' => $data['program_category'],
            'recipient_bank_account' => $data['recipient_bank_account'],
            'recipient_address' => $data['recipient_address'],
            'requested_amount' => number_format((float) $data['requested_amount'], 2, '.', ''),
            'association_invite_filled_at' => now(),
            'association_pdpa_consented_at' => now(),
        ]);

        $this->recipients->syncFromApplication($application->fresh());

        $this->audit->log('APPLICATION_ASSOCIATION_FORM_FILLED', $application, null, [
            'via' => 'public_link',
        ]);

        $this->notifier->associationFormFilled($application->fresh());

        return redirect()
            ->route('association-form.show', ['token' => $token])
            ->with('status', 'Maklumat borang berjaya dihantar. ALP akan semak borang dan lampiran, kemudian hantar kepada Admin JP.');
    }

    public function storeDocument(AssociationPublicDocumentRequest $request, string $token): RedirectResponse
    {
        $application = $request->inviteApplication();
        abort_if($application === null, 404);

        $file = $request->file('file');
        $type = DocumentType::from($request->validated('document_type'));

        $this->documentStorage->store($application, $file, $type, null);

        $this->audit->log('APPLICATION_DOCUMENT_UPLOADED', $application, null, [
            'document_type' => $type->value,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'via' => 'public_link',
        ]);

        return redirect()
            ->route('association-form.show', ['token' => $token])
            ->with('status', 'Lampiran dimuat naik.');
    }

    public function destroyDocument(string $token, ApplicationDocument $document): RedirectResponse
    {
        $application = $this->invites->findActiveByPlainToken($token);
        abort_if($application === null, 404);
        abort_unless($document->application_id === $application->id, 404);
        abort_unless(
            in_array($document->document_type, DocumentType::contributionAttachments(), true),
            403,
        );

        $type = $document->document_type->value;
        $filename = $document->original_filename;
        $this->documentStorage->delete($document);

        $this->audit->log('APPLICATION_DOCUMENT_REMOVED', $application, null, [
            'document_type' => $type,
            'original_filename' => $filename,
            'via' => 'public_link',
        ]);

        return redirect()
            ->route('association-form.show', ['token' => $token])
            ->with('status', 'Lampiran dibuang.');
    }

    public function suggestRecipients(Request $request, string $token): JsonResponse
    {
        $application = $this->invites->findActiveByPlainToken($token);
        abort_if($application === null, 404);

        $q = $request->string('q')->trim()->toString();
        if (mb_strlen($q) > 100) {
            $q = mb_substr($q, 0, 100);
        }

        // Pautan awam: elak skrap senarai penuh — minimum 2 aksara carian.
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => $this->recipients->suggestionsForAlp(
                (int) $application->alp_id,
                $q,
            ),
        ]);
    }

    public function checkPersatuanRos(Request $request, string $token): JsonResponse
    {
        $application = $this->invites->findActiveByPlainToken($token);
        abort_if($application === null, 404);

        $name = $request->string('name')->trim()->toString();
        $ros = $request->string('ros')->trim()->toString();
        if (mb_strlen($name) > 200) {
            $name = mb_substr($name, 0, 200);
        }
        if (mb_strlen($ros) > 80) {
            $ros = mb_substr($ros, 0, 80);
        }

        return response()->json(
            $this->recipients->rosMismatchForPersatuan((int) $application->alp_id, $name, $ros),
        );
    }
}
