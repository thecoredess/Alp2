<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationBudgetItemTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $year = FinancialYear::factory()->active()->create();
        $alp = Alp::factory()->create();
        $this->user = User::factory()->create(['alp_id' => $alp->id])->assignRole(RoleName::ALP->value);
        $this->application = Application::factory()->create(['alp_id' => $alp->id, 'financial_year_id' => $year->id]);
    }

    public function test_backend_calculates_exact_item_total(): void
    {
        $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
            'description' => 'Khemah',
            'quantity' => 10,
            'unit' => 'unit',
            'unit_cost' => '500.00',
        ])->assertRedirect();

        // 10 × 500.00 = 5000.00 (tepat).
        $this->assertDatabaseHas('application_budget_items', [
            'application_id' => $this->application->id,
            'total' => '5000.00',
        ]);
        // requested_amount permohonan dikemas kini tepat.
        $this->assertSame('5000.00', $this->application->fresh()->requested_amount);
    }

    public function test_item_total_precision_with_cents(): void
    {
        $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
            'description' => 'Barang',
            'quantity' => 3,
            'unit_cost' => '0.10',
        ])->assertRedirect();

        // 3 × 0.10 = 0.30 (tepat, bukan 0.30000000004).
        $this->assertDatabaseHas('application_budget_items', ['total' => '0.30']);
        $this->assertSame('0.30', $this->application->fresh()->requested_amount);
    }

    public function test_application_total_is_exact_sum_of_items(): void
    {
        foreach ([['A', 1, '1000000.10'], ['B', 1, '0.20']] as [$d, $q, $c]) {
            $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
                'description' => $d, 'quantity' => $q, 'unit_cost' => $c,
            ]);
        }

        // 1000000.10 + 0.20 = 1000000.30 (tepat).
        $this->assertSame('1000000.30', $this->application->fresh()->requested_amount);
    }

    public function test_invalid_quantity_is_rejected(): void
    {
        $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
            'description' => 'X', 'quantity' => 0, 'unit_cost' => '100.00',
        ])->assertSessionHasErrors('quantity');

        $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
            'description' => 'X', 'quantity' => -5, 'unit_cost' => '100.00',
        ])->assertSessionHasErrors('quantity');
    }

    public function test_invalid_unit_cost_is_rejected(): void
    {
        // Negatif.
        $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
            'description' => 'X', 'quantity' => 1, 'unit_cost' => '-10.00',
        ])->assertSessionHasErrors('unit_cost');

        // Lebih 2 titik perpuluhan.
        $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
            'description' => 'X', 'quantity' => 1, 'unit_cost' => '10.123',
        ])->assertSessionHasErrors('unit_cost');
    }

    public function test_cannot_add_item_to_submitted_application(): void
    {
        $this->application->update(['status' => \App\Enums\ApplicationStatus::SUBMITTED]);

        $this->actingAs($this->user)->post(route('applications.items.store', $this->application), [
            'description' => 'X', 'quantity' => 1, 'unit_cost' => '100.00',
        ])->assertForbidden();
    }
}
