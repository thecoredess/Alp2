<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\ApprovalLevel;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApprovalMatrixService;
use App\Support\Money;
use Database\Seeders\ApprovalLevelSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Matriks URS v1.2 (ApprovalLevelSeeder):
 * Aras 1 ≤3000 (Peraku), Aras 2 >3000 (PEPU). Aras 3 dinyahaktif.
 */
class ApprovalMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function matrix(): ApprovalMatrixService
    {
        return app(ApprovalMatrixService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ApprovalLevelSeeder::class);
    }

    public function test_small_amount_requires_single_level(): void
    {
        $required = $this->matrix()->requiredLevels(Money::of('2500'), null);
        $this->assertCount(1, $required);
        $this->assertSame(1, $required->first()->sequence);
    }

    public function test_above_threshold_requires_two_levels_in_sequence(): void
    {
        $required = $this->matrix()->requiredLevels(Money::of('5000'), null);
        $this->assertCount(2, $required);
        $this->assertSame([1, 2], $required->pluck('sequence')->all());
    }

    public function test_large_amount_still_only_two_active_levels(): void
    {
        $required = $this->matrix()->requiredLevels(Money::of('150000'), null);
        $this->assertCount(2, $required);
        $this->assertSame([1, 2], $required->pluck('sequence')->all());
        $this->assertFalse(ApprovalLevel::query()->where('sequence', 3)->where('active', true)->exists());
    }

    public function test_missing_configuration_fails_safely(): void
    {
        ApprovalLevel::query()->delete();

        $this->expectException(ApplicationException::class);
        $this->matrix()->requiredLevels(Money::of('5000'), null);
    }

    public function test_overlapping_ranges_are_rejected(): void
    {
        $this->expectException(ApplicationException::class);
        $this->matrix()->assertNoOverlap(null, Money::of('1000'), Money::of('4000'));
    }

    public function test_non_overlapping_range_is_accepted_after_clearing_pepu_band(): void
    {
        ApprovalLevel::where('sequence', 2)->delete();

        $this->matrix()->assertNoOverlap(null, Money::of('3000.01'), Money::of('500000.00'));
        $this->assertTrue(true);
    }

    public function test_admin_can_view_matrix_ui(): void
    {
        $admin = \App\Models\User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        $this->actingAs($admin)->get(route('approval-matrix.index'))->assertOk();
    }

    public function test_store_rejects_overlap_via_controller(): void
    {
        $admin = \App\Models\User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)->post(route('approval-matrix.store'), [
            'name' => 'Bertindih',
            'min_amount' => '1000.00',
            'max_amount' => '4000.00',
            'required_role' => RoleName::PELULUS->value,
            'sequence' => 5,
        ])->assertSessionHas('error');
    }
}
