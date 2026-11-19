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

    public function test_login_page_has_no_uat_account_helper(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('UAT ·')
            ->assertDontSee('dev-account')
            ->assertDontSee('superadmin@dbkl.test');
    }

    public function test_super_admin_accesses_settings_without_gate_before_bypass(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $this->actingAs($super)
            ->get(route('settings.mail.edit'))
            ->assertOk();
    }
}
