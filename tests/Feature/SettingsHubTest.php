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

    public function test_tetapan_url_redirects_to_profile(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_profile_page_shows_user_settings(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Tetapan')
            ->assertSee('Tukar Kata Laluan')
            ->assertDontSee('Polisi URS');
    }

    public function test_admin_sees_system_settings_on_profile_page(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Tetapan Sistem')
            ->assertSee('Polisi URS')
            ->assertSee('Pengguna');
    }
}
