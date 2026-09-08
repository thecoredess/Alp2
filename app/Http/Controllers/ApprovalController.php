<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApprovalService;
use App\Services\Budget\BudgetService;
use App\Support\UrsContributionPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly ApprovalService $approvals,
        private readonly ApplicationBudgetService $appBudget,
        private readonly BudgetService $budget,
    ) {}

    /** Giliran kelulusan. */
    public function queue(Request $request): View
    {
        abort_unless($request->user()->can('applications.approve'), 403);

        $applications = Application::query()
            ->where('status', ApplicationStatus::PENDING_APPROVAL->value)
            ->when($request->filled('tahun'), fn ($q) => $q->where('financial_year_id', $request->integer('tahun')))
            ->when($request->filled('alp'), fn ($q) => $q->where('alp_id', $request->integer('alp')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('application_type', $request->string('jenis')))
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($s) => $s
                ->where('application_number', 'like', '%'.$request->string('cari').'%')
                ->orWhere('purpose', 'like', '%'.$request->string('cari').'%')
                ->orWhere('recipient_name', 'like', '%'.$request->string('cari').'%')))
            ->with(['alp', 'financialYear'])
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('approvals.queue', [
            'applications' => $applications,
            'years' => FinancialYear::orderByDesc('year')->get(),
            'alps' => Alp::orderBy('ref_code')->get(),
            'typeOptions' => ApplicationType::options(),
        ]);
    }

    /** Halaman kelulusan + pengesahan (menunjukkan kesan sebelum tindakan). */
    public function show(Request $request, Application $application): View
    {
        abort_unless($request->user()->can('applications.approve'), 403);
        abort_if($application->status !== ApplicationStatus::PENDING_APPROVAL, 404, 'Permohonan tiada pada peringkat kelulusan.');

        $application->load(['alp', 'financialYear', 'documents', 'reviews.reviewer', 'approvals.approver', 'approvals.approvalLevel']);

        $summary = $this->budget->summaryFor($application->alp_id, $application->financial_year_id);
        $progress = $this->approvals->progress($application);
        $thisRequest = $application->requestedAmountMoney();
        $otherPending = $this->appBudget->pendingRequest($application->alp_id, $application->financial_year_id, $application->id);
        $approvedApplications = $this->appBudget->approvedApplications($application->alp_id, $application->financial_year_id);
        $pendingApplications = $this->appBudget->pendingApplications($application->alp_id, $application->financial_year_id, $application->id);

        $periodSummary = null;
        if (UrsContributionPolicy::enabled() && $application->financialYear) {
            $period = UrsContributionPolicy::periodFor(now(), (int) $application->financialYear->year);
            $used = UrsContributionPolicy::periodUsage(
                $application->alp_id,
                $application->financial_year_id,
                $period['start'],
                $period['end'],
            );
            $quota = UrsContributionPolicy::maxPeriodQuota();
            $remaining = $quota->minus($used);
            if ($remaining->isNegative()) {
                $remaining = \App\Support\Money::zero();
            }
            $periodSummary = [
                'label' => $period['label'],
                'quota' => $quota,
                'used' => $used,
                'remaining' => $remaining,
            ];
        }

        return view('approvals.show', [
            'application' => $application,
            'summary' => $summary,
            'ledgerAvailable' => $summary->available(),
            'otherPending' => $otherPending,
            'approvedApplications' => $approvedApplications,
            'pendingApplications' => $pendingApplications,
            'periodSummary' => $periodSummary,
            'afterCommitAvailable' => $summary->available()->minus($thisRequest),
            'progress' => $progress,
            'thisRequest' => $thisRequest,
        ]);
    }

    public function approve(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->can('applications.approve'), 403);
        $request->validate(['comments' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->approvals->approve($application, $request->user(), $request->input('comments'));
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('applications.show', $application)
            ->with('status', 'Keputusan kelulusan direkodkan.');
    }

    public function reject(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->can('applications.reject'), 403);
        $validated = $request->validate(['comments' => ['required', 'string', 'max:2000']], [
            'comments.required' => 'Sila nyatakan sebab penolakan.',
        ]);

        try {
            $this->approvals->reject($application, $request->user(), $validated['comments']);
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('applications.show', $application)
            ->with('status', 'Permohonan telah ditolak.');
    }

    public function returnForRevision(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->can('applications.approve'), 403);
        $validated = $request->validate(['comments' => ['required', 'string', 'max:2000']], [
            'comments.required' => 'Sila nyatakan sebab pembetulan.',
        ]);

        try {
            $this->approvals->returnForRevision($application, $request->user(), $validated['comments']);
        } catch (ApplicationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('applications.show', $application)
            ->with('status', 'Permohonan dikembalikan untuk pembetulan.');
    }
}
