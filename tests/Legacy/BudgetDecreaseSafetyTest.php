<?php

namespace Tests\Feature;

use App\Enums\BudgetRequestStatus;
use App\Enums\BudgetRequestType;
use App\Enums\BudgetTransactionType;
use App\Enums\FinancialYearStatus;
use App\Enums\RoleName;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\Application;
use App\Models\BudgetRequest;
use App\Models\BudgetTransaction;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Budget\BudgetException;
use App\Services\Budget\BudgetRequestApprovalService;
use App\Services\Budget\BudgetRequestService;
use App\Services\Budget\BudgetService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetDecreaseSafetyTest extends TestCase
{
    use RefreshDatabase;

    private Alp $alp;
    private FinancialYear $year;
    private User $maker;
    private User $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->alp = Alp::factory()->create();
        $this->year = FinancialYear::factory()->active()->create();
        $this->maker = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);
        $this->checker = User::factory()->create()->assignRole(RoleName::PELULUS->value);

        // Peruntukan awal 500k (persediaan langsung).
        app(BudgetService::class)->allocate($this->alp, $this->year, '500000.00', 'REF');
    }

    private function commit(string $amount): void
    {
        $alloc = Allocation::where('alp_id', $this->alp->id)->first();
        BudgetTransaction::create([
            'allocation_id' => $alloc->id, 'alp_id' => $this->alp->id, 'financial_year_id' => $this->year->id,
            'type' => BudgetTransactionType::COMMITMENT, 'amount' => $amount, 'created_at' => now(),
        ]);
    }

    private function submitPending(string $amount): void
    {
        Application::factory()->submitted()->create([
            'alp_id' => $this->alp->id, 'financial_year_id' => $this->year->id, 'requested_amount' => $amount,
        ]);
    }

    private function decreaseRequest(string $amount): BudgetRequest
    {
        $req = app(BudgetRequestService::class)->createDraft($this->maker, [
            'request_type' => BudgetRequestType::ALLOCATION_DECREASE,
            'alp_id' => $this->alp->id, 'financial_year_id' => $this->year->id, 'amount' => $amount, 'reason' => 'kurang',
        ]);
        app(BudgetRequestService::class)->submit($req, $this->maker);

        return $req->fresh();
    }

    public function test_decrease_cannot_reduce_below_committed(): void
    {
        $this->commit('400000.00'); // committed 400k

        // Kurang 200k → baki 300k < committed 400k → ditolak.
        $this->expectException(BudgetException::class);
        app(BudgetRequestApprovalService::class)->approve($this->decreaseRequest('200000.00'), $this->checker);
    }

    public function test_decrease_cannot_make_projected_available_negative(): void
    {
        // Seksyen 16: Allocation 500k, Committed 100k, Pending 250k, kurang 200k.
        $this->commit('100000.00');
        $this->submitPending('250000.00');

        // available = 400k; selepas kurang 200k = 200k; projected = 200k - 250k = -50k → ditolak.
        $this->expectException(BudgetException::class);
        app(BudgetRequestApprovalService::class)->approve($this->decreaseRequest('200000.00'), $this->checker);
    }

    public function test_valid_decrease_is_approved_and_posts_ledger(): void
    {
        $req = $this->decreaseRequest('100000.00');

        app(BudgetRequestApprovalService::class)->approve($req, $this->checker);

        $this->assertSame(BudgetRequestStatus::APPROVED, $req->fresh()->status);
        $this->assertSame('400000.00', app(BudgetService::class)->summaryFor($this->alp->id, $this->year->id)->allocation->value());
        // Ledger direkod sebagai pelarasan negatif.
        $this->assertDatabaseHas('budget_transactions', ['budget_request_id' => $req->id, 'type' => 'allocation_adjustment', 'amount' => '-100000.00']);
    }

    public function test_closed_year_approval_is_rejected(): void
    {
        $req = $this->decreaseRequest('100000.00');
        $this->year->update(['status' => FinancialYearStatus::CLOSED, 'is_active' => false]);

        $this->expectException(BudgetException::class);
        app(BudgetRequestApprovalService::class)->approve($req->fresh(), $this->checker);
    }
}
