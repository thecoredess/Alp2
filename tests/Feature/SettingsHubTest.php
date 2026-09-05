<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_any_user_can_open_settings_hub_and_user_settings(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Ketetapan Pengguna')
            ->assertSee('Ketetapan Sistem');

        $this->actingAs($user)
            ->get(route('settings.user'))
            ->assertOk()
            ->assertSee('Profil Saya');
    }

    public function test_alp_cannot_open_system_settings(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($user)
            ->get(route('settings.system'))
            ->assertForbidden();
    }

    public function test_admin_can_open_system_settings(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('settings.system'))
            ->assertOk()
            ->assertSee('Polisi URS');
    }
}
