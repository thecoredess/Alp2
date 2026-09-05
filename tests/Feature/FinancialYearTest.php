<?php

namespace Tests\Feature;

use App\Enums\FinancialYearStatus;
use App\Enums\RoleName;
use App\Models\FinancialYear;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialYearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
    }

    public function test_admin_can_create_financial_year_as_draft(): void
    {
        $this->actingAs($this->admin())
            ->post(route('financial-years.store'), ['year' => 2030])
            ->assertRedirect(route('financial-years.index'));

        $this->assertDatabaseHas('financial_years', [
            'year' => 2030,
            'status' => FinancialYearStatus::DRAFT->value,
            'is_active' => false,
        ]);
    }

    public function test_year_must_be_unique(): void
    {
        FinancialYear::factory()->create(['year' => 2031]);

        $this->actingAs($this->admin())
            ->post(route('financial-years.store'), ['year' => 2031])
            ->assertSessionHasErrors('year');
    }

    public function test_activating_a_year_deactivates_the_previously_active_year(): void
    {
        $old = FinancialYear::factory()->active()->create(['year' => 2026]);
        $new = FinancialYear::factory()->create(['year' => 2027, 'status' => FinancialYearStatus::OPEN]);

        $this->actingAs($this->admin())
            ->post(route('financial-years.activate', $new))
            ->assertRedirect();

        $this->assertFalse($old->fresh()->is_active);
        $this->assertTrue($new->fresh()->is_active);
        $this->assertEquals(FinancialYearStatus::ACTIVE, $new->fresh()->status);
        // Hanya satu tahun aktif.
        $this->assertEquals(1, FinancialYear::where('is_active', true)->count());
    }

    public function test_closed_year_cannot_be_activated(): void
    {
        $closed = FinancialYear::factory()->closed()->create(['year' => 2025]);

        $this->actingAs($this->admin())
            ->post(route('financial-years.activate', $closed))
            ->assertSessionHas('error');

        $this->assertFalse($closed->fresh()->is_active);
    }

    public function test_closed_year_cannot_be_edited(): void
    {
        $closed = FinancialYear::factory()->closed()->create(['year' => 2024]);

        $this->actingAs($this->admin())
            ->get(route('financial-years.edit', $closed))
            ->assertForbidden();
    }

    public function test_closing_a_year_protects_it(): void
    {
        $year = FinancialYear::factory()->active()->create(['year' => 2028]);

        $this->actingAs($this->admin())
            ->post(route('financial-years.close', $year))
            ->assertRedirect();

        $fresh = $year->fresh();
        $this->assertEquals(FinancialYearStatus::CLOSED, $fresh->status);
        $this->assertFalse($fresh->is_active);
        $this->assertNotNull($fresh->closed_at);
    }
}
