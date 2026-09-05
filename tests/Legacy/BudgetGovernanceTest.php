<?php

namespace Tests\Feature;

use App\Enums\BudgetRequestStatus;
use App\Enums\BudgetRequestType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\BudgetRequest;
use App\Models\BudgetTransaction;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Budget\BudgetException;
use App\Services\Budget\BudgetRequestApprovalService;
use App\Services\Budget\BudgetRequestService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function maker(): User
    {
        return User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);
    }

    private function checker(): User
    {
        return User::factory()->create()->assignRole(RoleName::PELULUS->value);
    }

    private function requests(): BudgetRequestService
    {
        return app(BudgetRequestService::class);
    }

    private function approvals(): BudgetRequestApprovalService
    {
        return app(BudgetRequestApprovalService::class);
    }

    private function draftInitial(Alp $alp, FinancialYear $year, User $maker, string $amount = '500000.00'): BudgetRequest
    {
        return $this->requests()->createDraft($maker, [
            'request_type' => BudgetRequestType::INITIAL_ALLOCATION,
            'alp_id' => $alp->id, 'financial_year_id' => $year->id, 'amount' => $amount, 'reason' => 'x',
        ]);
    }

    // ── Initial allocation flow ─────────────────────────────────

    public function test_maker_creates_draft_with_no_ledger_effect(): void
    {
        $req = $this->draftInitial(Alp::factory()->create(), FinancialYear::factory()->active()->create(), $this->maker());

        $this->assertSame(BudgetRequestStatus::DRAFT, $req->status);
        $this->assertSame(0, BudgetTransaction::count());
    }

    public function test_no_ledger_transaction_before_approval(): void
    {
        $maker = $this->maker();
        $req = $this->draftInitial(Alp::factory()->create(), FinancialYear::factory()->active()->create(), $maker);
        $this->requests()->submit($req, $maker);

        $this->assertSame(BudgetRequestStatus::PENDING_APPROVAL, $req->fresh()->status);
        $this->assertSame(0, BudgetTransaction::count()); // masih tiada kesan ledger
    }

    public function test_checker_approval_creates_exactly_one_initial_allocation(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $maker = $this->maker();
        $req = $this->draftInitial($alp, $year, $maker);
        $this->requests()->submit($req, $maker);

        $this->approvals()->approve($req->fresh(), $this->checker());

        $this->assertSame(BudgetRequestStatus::APPROVED, $req->fresh()->status);
        $this->assertDatabaseHas('allocations', ['alp_id' => $alp->id, 'financial_year_id' => $year->id]);
        $this->assertSame(1, BudgetTransaction::where('budget_request_id', $req->id)->where('type', 'initial_allocation')->count());
    }

    // ── Maker ≠ Checker ─────────────────────────────────────────

    public function test_maker_cannot_approve_own_request_service(): void
    {
        $maker = $this->maker();
        $req = $this->draftInitial(Alp::factory()->create(), FinancialYear::factory()->active()->create(), $maker);
        $this->requests()->submit($req, $maker);

        $this->expectException(BudgetException::class);
        $this->approvals()->approve($req->fresh(), $maker); // maker == checker
    }

    public function test_maker_cannot_approve_own_request_via_http(): void
    {
        // Beri maker juga kebenaran approve untuk membuktikan sekatan IDENTITI (bukan hanya permission).
        $maker = $this->maker();
        $maker->givePermissionTo('allocations.approve');
        $req = BudgetRequest::factory()->pending()->create(['created_by' => $maker->id, 'submitted_by' => $maker->id]);

        $this->actingAs($maker)->post(route('budget-approvals.approve', $req))->assertForbidden();
    }

    public function test_another_checker_can_approve(): void
    {
        $maker = $this->maker();
        $req = $this->draftInitial(Alp::factory()->create(), FinancialYear::factory()->active()->create(), $maker);
        $this->requests()->submit($req, $maker);

        $this->approvals()->approve($req->fresh(), $this->checker());
        $this->assertSame(BudgetRequestStatus::APPROVED, $req->fresh()->status);
    }

    public function test_unauthorized_role_cannot_approve(): void
    {
        $req = BudgetRequest::factory()->pending()->create();
        $user = User::factory()->create()->assignRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($user)->post(route('budget-approvals.approve', $req))->assertForbidden();
    }

    // ── Permissions ─────────────────────────────────────────────

    public function test_system_admin_cannot_create_allocation_proposal(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)->get(route('budget-requests.create'))->assertForbidden();
    }

    public function test_finance_maker_cannot_approve(): void
    {
        $maker = $this->maker();
        $req = BudgetRequest::factory()->pending()->create();

        $this->actingAs($maker)->post(route('budget-approvals.approve', $req))->assertForbidden();
    }

    // ── Adjustment flow ─────────────────────────────────────────

    public function test_approved_increase_updates_ledger_once(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $maker = $this->maker();
        $checker = $this->checker();

        // Peruntukan awal 500k.
        $init = $this->draftInitial($alp, $year, $maker);
        $this->requests()->submit($init, $maker);
        $this->approvals()->approve($init->fresh(), $checker);

        // Tambah 50k.
        $inc = $this->requests()->createDraft($maker, [
            'request_type' => BudgetRequestType::ALLOCATION_INCREASE,
            'alp_id' => $alp->id, 'financial_year_id' => $year->id, 'amount' => '50000.00', 'reason' => 'tambah',
        ]);
        $this->requests()->submit($inc, $maker);
        $this->approvals()->approve($inc->fresh(), $checker);

        $this->assertSame(1, BudgetTransaction::where('budget_request_id', $inc->id)->count());
        $this->assertSame('550000.00', app(\App\Services\Budget\BudgetService::class)->summaryFor($alp->id, $year->id)->allocation->value());
    }

    public function test_adjustment_reason_is_required_via_http(): void
    {
        $maker = $this->maker();
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();

        $this->actingAs($maker)->post(route('budget-requests.store'), [
            'request_type' => BudgetRequestType::ALLOCATION_INCREASE->value,
            'alp_id' => $alp->id, 'financial_year_id' => $year->id, 'amount' => '1000.00',
            // reason ditinggalkan
        ])->assertSessionHasErrors('reason');
    }

    // ── Rejection ───────────────────────────────────────────────

    public function test_rejected_request_creates_no_ledger_and_is_recorded(): void
    {
        $maker = $this->maker();
        $req = $this->draftInitial(Alp::factory()->create(), FinancialYear::factory()->active()->create(), $maker);
        $this->requests()->submit($req, $maker);

        $this->approvals()->reject($req->fresh(), $this->checker(), 'Tidak wajar');

        $this->assertSame(BudgetRequestStatus::REJECTED, $req->fresh()->status);
        $this->assertSame(0, BudgetTransaction::count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'ALLOCATION_REQUEST_REJECTED']);
    }

    // ── Revision ────────────────────────────────────────────────

    public function test_returned_request_can_be_edited_and_resubmitted_preserving_history(): void
    {
        $maker = $this->maker();
        $req = $this->draftInitial(Alp::factory()->create(), FinancialYear::factory()->active()->create(), $maker);
        $this->requests()->submit($req, $maker);
        $this->approvals()->returnForRevision($req->fresh(), $this->checker(), 'Betulkan jumlah');

        $this->assertSame(BudgetRequestStatus::REVISION_REQUIRED, $req->fresh()->status);

        $this->requests()->update($req->fresh(), $maker, ['amount' => '600000.00', 'reason' => 'y']);
        $this->requests()->submit($req->fresh(), $maker);

        $req->refresh();
        $this->assertSame(BudgetRequestStatus::PENDING_APPROVAL, $req->status);
        $this->assertSame(1, $req->revision_number);
        $this->assertSame('600000.00', $req->amount);
        // Sejarah kekal (draft→pending, pending→revision, revision→pending).
        $this->assertGreaterThanOrEqual(3, $req->histories()->count());
    }

    // ── Idempotency & atomicity ─────────────────────────────────

    public function test_double_approval_produces_one_ledger_transaction(): void
    {
        $maker = $this->maker();
        $req = $this->draftInitial(Alp::factory()->create(), FinancialYear::factory()->active()->create(), $maker);
        $this->requests()->submit($req, $maker);
        $checker = $this->checker();

        $this->approvals()->approve($req->fresh(), $checker);
        try {
            $this->approvals()->approve($req->fresh(), $checker); // ulangan
        } catch (BudgetException $e) {
        }

        $this->assertSame(1, BudgetTransaction::where('budget_request_id', $req->id)->count());
    }

    public function test_failure_before_posting_rolls_back_status(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $maker = $this->maker();
        $req = $this->draftInitial($alp, $year, $maker);
        $this->requests()->submit($req, $maker);

        // Suntik transaksi berkaitan untuk memaksa kegagalan "telah diposkan".
        $alloc = \App\Models\Allocation::create(['alp_id' => $alp->id, 'financial_year_id' => $year->id]);
        BudgetTransaction::create([
            'allocation_id' => $alloc->id, 'alp_id' => $alp->id, 'financial_year_id' => $year->id,
            'budget_request_id' => $req->id, 'type' => 'initial_allocation', 'amount' => '500000.00', 'created_at' => now(),
        ]);

        try {
            $this->approvals()->approve($req->fresh(), $this->checker());
        } catch (BudgetException $e) {
        }

        // Status kekal PENDING_APPROVAL (tiada kelulusan separa).
        $this->assertSame(BudgetRequestStatus::PENDING_APPROVAL, $req->fresh()->status);
    }

    // ── Money precision ─────────────────────────────────────────

    public function test_money_precision_exact(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $maker = $this->maker();
        $req = $this->draftInitial($alp, $year, $maker, '0.01');
        $this->requests()->submit($req, $maker);
        $this->approvals()->approve($req->fresh(), $this->checker());

        $this->assertDatabaseHas('budget_transactions', ['budget_request_id' => $req->id, 'amount' => '0.01']);
    }
}
