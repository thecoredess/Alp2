<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationInfoRequest;
use App\Enums\DocumentType;
use App\Models\Application;
use App\Models\DocumentRequirement;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationSubmissionService;
use App\Services\Application\RecipientRegistry;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use App\Support\ApplicationAmountValidator;
use App\Support\UrsContributionPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationWizardController extends Controller
{
    public function __construct(
        private readonly ApplicationBudgetService $appBudget,
        private readonly BudgetService $budget,
        private readonly AuditService $audit,
        private readonly RecipientRegistry $recipients,
    ) {}

    /** Langkah 1 — Borang Penyaluran (kemas kini draf). */
    public function maklumat(Application $application): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        return view('applications.wizard.maklumat', [
            'application' => $application,
            'alp' => $application->alp,
            'step' => 1,
            'amountLimits' => ApplicationAmountValidator::limitsFor(
                $application->alp,
                $application->financialYear,
                $application->id,
                forceMaxPerApplication: $request->user()->canCreateApplicationOnBehalf(),
            ),
            'jpIncomplete' => \App\Support\JpReviewChecklist::latestIncompleteFor($application),
            ...$this->programDateContext($application),
        ]);
    }

    public function updateMaklumat(ApplicationInfoRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $data = $request->validated();
        $application->update([
            'purpose' => $data['purpose'],
            'recipient_name' => $data['recipient_name'],
            'recipient_ros_number' => $data['recipient_ros_number'],
            'program_date' => $data['program_date'],
            'program_category' => $data['program_category'],
            'recipient_bank_account' => $data['recipient_bank_account'],
            'recipient_address' => $data['recipient_address'],
            'requested_amount' => number_format((float) $data['requested_amount'], 2, '.', ''),
            'updated_by' => $request->user()->id,
        ]);
        $this->recipients->syncFromApplication($application->fresh());
        $this->audit->log('APPLICATION_UPDATED', $application, null, ['step' => 'borang']);

        return redirect()->route('applications.wizard.dokumen', $application)
            ->with('status', 'Borang Penyaluran disimpan.');
    }

    /** Langkah 2 — Lampiran senarai semak. */
    public function dokumen(Application $application): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        $application->load('documents.uploader');

        $order = collect(DocumentType::contributionAttachments())
            ->map(fn (DocumentType $t) => $t->value)
            ->flip();

        $requirements = DocumentRequirement::activeFor()
            ->sortBy(fn (DocumentRequirement $r) => $order[$r->document_type->value] ?? 99)
            ->values();

        return view('applications.wizard.dokumen', [
            'application' => $application,
            'step' => 2,
            'requirements' => $requirements,
            'jpIncomplete' => \App\Support\JpReviewChecklist::latestIncompleteFor($application),
            ...$this->programDateContext($application),
        ]);
    }

    /** Langkah 3 — Semak & hantar kepada JP. */
    public function semakan(Application $application, ApplicationSubmissionService $submission): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        $application->load(['documents', 'financialYear', 'alp', 'reviews']);

        return view('applications.wizard.semakan', [
            'application' => $application,
            'step' => 3,
            'position' => $this->budgetPosition($application),
            'missingDocuments' => $submission->missingRequiredDocuments($application),
            'jpIncomplete' => \App\Support\JpReviewChecklist::latestIncompleteFor($application),
            ...$this->programDateContext($application),
        ]);
    }

    public function hantar(Application $application, ApplicationSubmissionService $submission): RedirectResponse
    {
        $this->authorize('submit', $application);

        $isRevision = $application->status === \App\Enums\ApplicationStatus::REVISION_REQUIRED;

        try {
            $isRevision
                ? $submission->resubmit($application, request()->user())
                : $submission->submit($application, request()->user());
        } catch (ApplicationException $e) {
            return redirect()->route('applications.wizard.semakan', $application)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('applications.show', $application)
            ->with('status', 'Permohonan '.$application->application_number.(
                $isRevision
                    ? ' berjaya dihantar semula kepada Jabatan Pentadbiran.'
                    : (request()->user()->canCreateApplicationOnBehalf()
                        ? ' berjaya dihantar kepada Pegawai JP.'
                        : ' berjaya dihantar kepada Jabatan Pentadbiran.')
            ));
    }

    private function ensureDraft(Application $application, string $ability): ?RedirectResponse
    {
        $this->authorize($ability, $application);

        if (! $application->isEditableByOwner()) {
            return redirect()->route('applications.show', $application)
                ->with('warning', 'Permohonan telah dihantar dan tidak boleh disunting.');
        }

        return null;
    }

    private function budgetPosition(Application $application): array
    {
        $summary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $ledgerAvailable = $summary->available();
        $otherPending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id, $application->id);
        $thisRequest = $application->requestedAmountMoney();
        $availableForNew = $ledgerAvailable->minus($otherPending);
        $balanceAfter = $availableForNew->minus($thisRequest);

        return [
            'summary' => $summary,
            'ledger_available' => $ledgerAvailable,
            'other_pending' => $otherPending,
            'this_request' => $thisRequest,
            'available_for_new' => $availableForNew,
            'balance_after' => $balanceAfter,
            'sufficient' => ! $thisRequest->greaterThan($availableForNew),
        ];
    }

    /** @return array{programDateMin: string, programDateSubmitErrors: list<string>, waiveProgramLeadTime: bool} */
    private function programDateContext(Application $application): array
    {
        $waive = request()->user()?->canWaiveProgramLeadTime() ?? false;

        return [
            'programDateMin' => $waive
                ? now()->toDateString()
                : UrsContributionPolicy::minimumProgramDate()->format('Y-m-d'),
            'programDateSubmitErrors' => $waive
                ? []
                : UrsContributionPolicy::programDateSubmitErrors($application->program_date),
            'waiveProgramLeadTime' => $waive,
        ];
    }
}
