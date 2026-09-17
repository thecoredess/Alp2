<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Allocation;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAllocationManageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_jp_can_set_initial_allocation(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2026]);

        $this->actingAs($admin)
            ->post(route('allocations.store'), [
                'alp_id' => $alp->id,
                'financial_year_id' => $year->id,
                'amount' => '25000.00',
                'reference_no' => 'ADM/2026/001',
                'remarks' => 'Set terus Admin JP',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('allocations', [
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
        ]);
        $this->assertDatabaseHas('budget_transactions', [
            'alp_id' => $alp->id,
            'type' => 'initial_allocation',
            'amount' => '25000.00',
        ]);
    }

    public function test_admin_jp_can_adjust_allocation(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2026]);

        $allocation = app(\App\Services\Budget\BudgetService::class)
            ->allocate($alp, $year, '20000.00', 'REF');

        $this->actingAs($admin)
            ->post(route('allocations.adjust.store', $allocation), [
                'direction' => 'increase',
                'amount' => '5,000.00',
                'remarks' => 'Tambah',
            ])
            ->assertRedirect(route('allocations.show', $allocation));

        $summary = app(\App\Services\Budget\BudgetService::class)->summaryFor($alp->id, $year->id);
        $this->assertSame('25000.00', $summary->allocation->value());
    }

    public function test_alp_user_cannot_manage_allocations(): void
    {
        $alp = Alp::factory()->create();
        $user = User::factory()->create(['alp_id' => $alp->id])->assignRole(RoleName::ALP->value);
        $year = FinancialYear::factory()->active()->create();

        $this->actingAs($user)
            ->get(route('allocations.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('allocations.store'), [
                'alp_id' => $alp->id,
                'financial_year_id' => $year->id,
                'amount' => '1000',
            ])
            ->assertForbidden();
    }

    public function test_create_form_shows_for_admin(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        FinancialYear::factory()->active()->create();

        $this->actingAs($admin)
            ->get(route('allocations.create'))
            ->assertOk()
            ->assertSee('Set Peruntukan');
    }
}
