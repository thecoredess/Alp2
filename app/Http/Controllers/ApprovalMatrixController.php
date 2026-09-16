<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Http\Requests\ApprovalLevelRequest;
use App\Models\ApplicationApproval;
use App\Models\ApprovalLevel;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApprovalMatrixService;
use App\Services\Audit\AuditService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalMatrixController extends Controller
{
    public function __construct(
        private readonly ApprovalMatrixService $matrix,
        private readonly AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('approval_matrix.view'), 403);

        $levels = ApprovalLevel::orderBy('sequence')->get();

        return view('admin.approval-matrix.index', compact('levels'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('approval_matrix.manage'), 403);

        return view('admin.approval-matrix.create', $this->formData());
    }

    public function store(ApprovalLevelRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('approval_matrix.manage'), 403);

        try {
            $this->matrix->assertNoOverlap(
                $request->validated('financial_year_id'),
                Money::of((string) $request->validated('min_amount')),
                $request->validated('max_amount') !== null ? Money::of((string) $request->validated('max_amount')) : null,
            );
        } catch (ApplicationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $level = ApprovalLevel::create([...$request->validated(), 'active' => $request->boolean('active', true)]);

        $this->audit->log('APPROVAL_LEVEL_CREATED', $level, null, [
            'sequence' => $level->sequence,
            'role' => $level->role,
        ]);

        return redirect()->route('approval-matrix.index')->with('status', 'Aras kelulusan dicipta.');
    }

    public function edit(Request $request, ApprovalLevel $approvalLevel): View
    {
        abort_unless($request->user()->can('approval_matrix.manage'), 403);

        return view('admin.approval-matrix.edit', ['level' => $approvalLevel] + $this->formData());
    }

    public function update(ApprovalLevelRequest $request, ApprovalLevel $approvalLevel): RedirectResponse
    {
        abort_unless($request->user()->can('approval_matrix.manage'), 403);

        try {
            $this->matrix->assertNoOverlap(
                $request->validated('financial_year_id'),
                Money::of((string) $request->validated('min_amount')),
                $request->validated('max_amount') !== null ? Money::of((string) $request->validated('max_amount')) : null,
                $approvalLevel->id,
            );
        } catch (ApplicationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $approvalLevel->update([...$request->validated(), 'active' => $request->boolean('active', true)]);

        $this->audit->log('APPROVAL_LEVEL_UPDATED', $approvalLevel, null, [
            'sequence' => $approvalLevel->sequence,
            'role' => $approvalLevel->role,
        ]);

        return redirect()->route('approval-matrix.index')->with('status', 'Aras kelulusan dikemas kini.');
    }

    /** Aktif/nyahaktif (tidak dipadam jika telah dirujuk kelulusan). */
    public function toggle(Request $request, ApprovalLevel $approvalLevel): RedirectResponse
    {
        abort_unless($request->user()->can('approval_matrix.manage'), 403);

        $approvalLevel->update(['active' => ! $approvalLevel->active]);

        $this->audit->log('APPROVAL_LEVEL_TOGGLED', $approvalLevel, null, [
            'active' => $approvalLevel->active,
        ]);

        return back()->with('status', 'Status aras dikemas kini.');
    }

    private function formData(): array
    {
        return [
            'years' => FinancialYear::orderByDesc('year')->get(),
            'roles' => collect(RoleName::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all(),
        ];
    }
}
