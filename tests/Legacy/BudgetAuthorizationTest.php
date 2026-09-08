<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Allocation;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Budget\BudgetService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(RoleName $role, ?Alp $alp = null): User
    {
        return User::factory()->create(['alp_id' => $alp?->id])->assignRole($role->value);
    }

    private function makeAllocation(Alp $alp): Allocation
    {
        $year = FinancialYear::factory()->active()->create();

        return app(BudgetService::class)->allocate($alp, $year, 500000);
    }

    public function test_alp_cannot_view_another_alps_allocation(): void
    {
        $alpA = Alp::factory()->create();
        $alpB = Alp::factory()->create();
        $allocationB = $this->makeAllocation($alpB);

        $userA = $this->userWithRole(RoleName::ALP, $alpA);

        $this->actingAs($userA)->get(route('allocations.show', $allocationB))->assertForbidden();
    }

    public function test_alp_can_view_their_own_allocation(): void
    {
        $alp = Alp::factory()->create();
        $allocation = $this->makeAllocation($alp);
        $user = $this->userWithRole(RoleName::ALP, $alp);

        $this->actingAs($user)->get(route('allocations.show', $allocation))->assertOk();
    }

    public function test_alp_can_view_own_budget_page(): void
    {
        $alp = Alp::factory()->create();
        $this->makeAllocation($alp);
        $user = $this->userWithRole(RoleName::ALP, $alp);

        $this->actingAs($user)->get(route('budget.mine'))->assertOk();
    }

    public function test_alp_cannot_access_allocation_index(): void
    {
        $user = $this->userWithRole(RoleName::ALP, Alp::factory()->create());

        $this->actingAs($user)->get(route('allocations.index'))->assertForbidden();
    }

    public function test_alp_cannot_create_allocation_proposal(): void
    {
        // Fasa 4A: peruntukan kini melalui cadangan maker-checker. ALP tiada kebenaran.
        $user = $this->userWithRole(RoleName::ALP, Alp::factory()->create());

        $this->actingAs($user)->get(route('budget-requests.create'))->assertForbidden();
        $this->actingAs($user)->post(route('budget-requests.store'), [
            'request_type' => \App\Enums\BudgetRequestType::INITIAL_ALLOCATION->value,
            'alp_id' => Alp::factory()->create()->id,
            'financial_year_id' => FinancialYear::factory()->active()->create()->id,
            'amount' => '1000.00',
        ])->assertForbidden();
    }

    public function test_finance_officer_can_view_budget_overview(): void
    {
        $user = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN);

        $this->actingAs($user)->get(route('allocations.index'))->assertOk();
    }

    public function test_pegawai_jp_can_view_allocation_summary(): void
    {
        $alp = Alp::factory()->create();
        $allocation = $this->makeAllocation($alp);
        $user = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA);

        $this->actingAs($user)
            ->get(route('allocations.index'))
            ->assertOk()
            ->assertSee('Peruntukan (Ringkasan)')   // pautan sidebar
            ->assertDontSee('Set Peruntukan');

        $this->actingAs($user)->get(route('allocations.show', $allocation))->assertOk();
    }

    public function test_pegawai_jp_cannot_set_or_adjust_allocation(): void
    {
        $allocation = $this->makeAllocation(Alp::factory()->create());
        $user = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA);

        $this->actingAs($user)->get(route('allocations.create'))->assertForbidden();
        $this->actingAs($user)->get(route('allocations.adjust', $allocation))->assertForbidden();
        $this->actingAs($user)->post(route('allocations.adjust.store', $allocation), [
            'direction' => 'increase',
            'amount' => '1000.00',
        ])->assertForbidden();
    }
}
