<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationInfoRequest;
use App\Http\Requests\ApplicationScopeRequest;
use App\Models\Application;
use App\Models\DocumentRequirement;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationSubmissionService;
use App\Services\Audit\AuditService;
use App\Services\Budget\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationWizardController extends Controller
{
    public function __construct(
        private readonly ApplicationBudgetService $appBudget,
        private readonly BudgetService $budget,
        private readonly AuditService $audit,
        private readonly \App\Services\Application\RecipientRegistry $recipients,
    ) {}

    // ── Langkah 1 — Maklumat Projek ─────────────────────────────

    public function maklumat(Application $application): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        return view('applications.wizard.maklumat', ['application' => $application, 'step' => 1]);
    }

    public function updateMaklumat(ApplicationInfoRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $data = collect($request->validated())->except('compliance_declaration')->all();
        $data['compliance_declared_at'] = now();
        $data['updated_by'] = $request->user()->id;

        $application->update($data);
        $this->recipients->syncFromApplication($application->fresh());
        $this->audit->log('APPLICATION_UPDATED', $application, null, ['step' => 'maklumat']);

        $shortNotice = \App\Support\UrsContributionPolicy::isShortNotice($application->fresh()->proposed_start_date);
        $status = 'Maklumat projek disimpan.';
        if ($shortNotice) {
            $status .= ' Amaran BR-014: kurang 2 bulan sebelum program — masih boleh diproses (BR-015).';
        }

        return redirect()->route('applications.wizard.objektif', $application)
            ->with('status', $status);
    }

    // ── Langkah 2 — Objektif & Skop ─────────────────────────────

    public function objektif(Application $application): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        return view('applications.wizard.objektif', ['application' => $application, 'step' => 2]);
    }

    public function updateObjektif(ApplicationScopeRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $application->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
        ]);
        $this->audit->log('APPLICATION_UPDATED', $application, null, ['step' => 'objektif']);

        return redirect()->route('applications.wizard.bajet', $application)
            ->with('status', 'Objektif & skop disimpan.');
    }

    // ── Langkah 3 — Pecahan Bajet ───────────────────────────────

    public function bajet(Application $application): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        $application->load('budgetItems');

        return view('applications.wizard.bajet', [
            'application' => $application,
            'step' => 3,
            'position' => $this->budgetPosition($application),
        ]);
    }

    // ── Langkah 4 — Dokumen ─────────────────────────────────────

    public function dokumen(Application $application): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        $application->load('documents.uploader');

        return view('applications.wizard.dokumen', [
            'application' => $application,
            'step' => 4,
            'requirements' => DocumentRequirement::activeFor($application->application_type),
        ]);
    }

    // ── Langkah 5 — Semakan ─────────────────────────────────────

    public function semakan(Application $application, ApplicationSubmissionService $submission): View|RedirectResponse
    {
        if ($redirect = $this->ensureDraft($application, 'view')) {
            return $redirect;
        }
        $this->authorize('update', $application);

        $application->load(['budgetItems', 'documents', 'financialYear', 'alp']);

        return view('applications.wizard.semakan', [
            'application' => $application,
            'step' => 5,
            'position' => $this->budgetPosition($application),
            'missingDocuments' => $submission->missingRequiredDocuments($application),
        ]);
    }

    // ── Langkah 6 — Hantar ──────────────────────────────────────

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
            ->with('status', 'Permohonan '.$application->application_number.($isRevision ? ' berjaya dihantar semula.' : ' berjaya dihantar.'));
    }

    // ── Bantuan ─────────────────────────────────────────────────

    /** Jika bukan draf, alihkan ke halaman butiran (baca sahaja). */
    private function ensureDraft(Application $application, string $ability): ?RedirectResponse
    {
        $this->authorize($ability, $application);

        if (! $application->isEditableByOwner()) {
            return redirect()->route('applications.show', $application)
                ->with('warning', 'Permohonan telah dihantar dan tidak boleh disunting.');
        }

        return null;
    }

    /** Kedudukan bajet untuk paparan (semua nilai Money, tepat). */
    private function budgetPosition(Application $application): array
    {
        $summary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $ledgerAvailable = $summary->available();
        $otherPending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id, $application->id);
        $thisRequest = $application->budgetItemsTotal();
        $availableForNew = $ledgerAvailable->minus($otherPending);
        $balanceAfter = $availableForNew->minus($thisRequest);

        return [
            'summary' => $summary,               // BudgetSummary: allocation/committed/spent
            'ledger_available' => $ledgerAvailable,
            'other_pending' => $otherPending,
            'this_request' => $thisRequest,
            'available_for_new' => $availableForNew,
            'balance_after' => $balanceAfter,
            'sufficient' => ! $thisRequest->greaterThan($availableForNew),
        ];
    }
}
