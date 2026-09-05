<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\BudgetTransactionType;
use App\Enums\RoleName;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class ApprovalIntegrityTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    private function approvals(): ApprovalService
    {
        return app(ApprovalService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    /** IDEMPOTENSI: kelulusan berganda (dua klik) tidak mencipta komitmen berganda. */
    public function test_double_final_approval_does_not_duplicate_commitment(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        try {
            $this->approvals()->approve($app->fresh(), $this->userWithRole(RoleName::PELULUS->value), null);
        } catch (ApplicationException $e) {
            // dijangka
        }

        $this->assertSame(1, BudgetTransaction::where('application_id', $app->id)->where('type', 'commitment')->count());
    }

    /** Kekangan DB menghalang dua komitmen bagi permohonan yang sama. */
    public function test_db_constraint_blocks_duplicate_commitment(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));
        $allocation = Allocation::where('alp_id', $alp->id)->first();

        BudgetTransaction::create([
            'allocation_id' => $allocation->id, 'alp_id' => $alp->id, 'financial_year_id' => $year->id,
            'application_id' => $app->id, 'type' => BudgetTransactionType::COMMITMENT, 'amount' => '2500.00', 'created_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        BudgetTransaction::create([
            'allocation_id' => $allocation->id, 'alp_id' => $alp->id, 'financial_year_id' => $year->id,
            'application_id' => $app->id, 'type' => BudgetTransactionType::COMMITMENT, 'amount' => '2500.00', 'created_at' => now(),
        ]);
    }

    /** ROLLBACK: kegagalan selepas rekod kelulusan mesti membatalkan semuanya. */
    public function test_failure_during_commit_rolls_back_approval_record(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));
        $allocation = Allocation::where('alp_id', $alp->id)->first();

        BudgetTransaction::create([
            'allocation_id' => $allocation->id, 'alp_id' => $alp->id, 'financial_year_id' => $year->id,
            'application_id' => $app->id, 'type' => BudgetTransactionType::COMMITMENT, 'amount' => '2500.00', 'created_at' => now(),
        ]);

        try {
            $this->approvals()->approve($app->fresh(), $this->userWithRole(RoleName::PELULUS->value), null);
        } catch (ApplicationException $e) {
            // dijangka
        }

        $this->assertSame(0, $app->approvals()->count());
        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
        $this->assertSame(1, BudgetTransaction::where('application_id', $app->id)->where('type', 'commitment')->count());
    }

    /** CONCURRENCY: dua kelulusan tidak boleh melebihi baki ledger yang sama. */
    public function test_two_approvals_cannot_overspend_allocation(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '4000.00');

        $a = $this->toPendingApproval($this->submittedDirect($alp, $year, '2500.00'));
        $b = $this->toPendingApproval($this->submittedDirect($alp, $year, '2500.00'));

        $this->approvals()->approve($a, $this->userWithRole(RoleName::PELULUS->value), null);
        try {
            $this->approvals()->approve($b, $this->userWithRole(RoleName::PELULUS->value), null);
        } catch (ApplicationException $e) {
            // dijangka — baki tidak mencukupi selepas A dikomit
        }

        $this->assertSame(1, BudgetTransaction::where('type', 'commitment')->count());
    }
}
