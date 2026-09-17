<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_uat_login_helper_hidden_in_production(): void
    {
        config(['app.env' => 'production', 'app.show_uat_login_helper' => false]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('UAT ·');
    }

    public function test_uat_login_helper_can_show_in_local_when_enabled(): void
    {
        config(['app.env' => 'local', 'app.show_uat_login_helper' => true]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('UAT ·');
    }

    public function test_super_admin_accesses_settings_without_gate_before_bypass(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $this->actingAs($super)
            ->get(route('settings.mail.edit'))
            ->assertOk();
    }
}
