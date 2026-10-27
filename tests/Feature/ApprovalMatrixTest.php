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
 * Matriks (ApprovalLevelSeeder): satu aras — Kelulusan Pengarah JP untuk semua jumlah.
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

    public function test_all_amounts_require_single_pengarah_level(): void
    {
        foreach (['2500', '5000', '150000'] as $amount) {
            $required = $this->matrix()->requiredLevels(Money::of($amount), null);
            $this->assertCount(1, $required, "Failed for amount {$amount}");
            $this->assertSame([1], $required->pluck('sequence')->all());
            $this->assertSame(RoleName::PELULUS->value, $required->first()->required_role);
        }
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

    public function test_non_overlapping_range_is_accepted_for_inactive_legacy_level(): void
    {
        $legacyId = ApprovalLevel::where('sequence', 2)->value('id');
        if ($legacyId === null) {
            $this->markTestSkipped('Tiada aras legacy sequence 2.');
        }

        $this->matrix()->assertNoOverlap(null, Money::of('999999999.99'), null, $legacyId);
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
