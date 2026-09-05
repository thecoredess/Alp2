<?php

namespace Tests\Feature;

use App\Enums\AlpStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlpManagementTest extends TestCase
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

    public function test_admin_can_create_alp(): void
    {
        $this->actingAs($this->admin())->post(route('alps.store'), [
            'name' => 'Dato Test',
            'ref_code' => 'ALP-99',
            'portfolio_zone' => 'Zon Ujian',
        ])->assertRedirect();

        $this->assertDatabaseHas('alps', ['ref_code' => 'ALP-99', 'status' => AlpStatus::ACTIVE->value]);
    }

    public function test_ref_code_must_be_unique(): void
    {
        Alp::factory()->create(['ref_code' => 'ALP-77']);

        $this->actingAs($this->admin())->post(route('alps.store'), [
            'name' => 'Duplicate',
            'ref_code' => 'ALP-77',
        ])->assertSessionHasErrors('ref_code');
    }

    public function test_appointment_end_must_be_after_start(): void
    {
        $this->actingAs($this->admin())->post(route('alps.store'), [
            'name' => 'Bad Dates',
            'ref_code' => 'ALP-78',
            'appointment_start' => '2026-12-31',
            'appointment_end' => '2026-01-01',
        ])->assertSessionHasErrors('appointment_end');
    }

    public function test_admin_can_deactivate_and_reactivate_alp(): void
    {
        $alp = Alp::factory()->create();

        $this->actingAs($this->admin())->post(route('alps.deactivate', $alp))->assertRedirect();
        $this->assertEquals(AlpStatus::INACTIVE, $alp->fresh()->status);

        $this->actingAs($this->admin())->post(route('alps.activate', $alp))->assertRedirect();
        $this->assertEquals(AlpStatus::ACTIVE, $alp->fresh()->status);
    }
}
