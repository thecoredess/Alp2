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

    public function test_tetapan_url_redirects_alp_to_profile(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_tetapan_url_redirects_admin_to_system_hub(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertRedirect(route('settings.hub'));
    }

    public function test_profile_page_shows_user_settings_only(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Profil')
            ->assertSee('Tukar Kata Laluan')
            ->assertDontSee('Polisi Sumbangan')
            ->assertDontSee('Tetapan Sistem');
    }

    public function test_admin_sees_system_settings_hub(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('settings.hub'))
            ->assertOk()
            ->assertSee('Tetapan Sistem')
            ->assertSee('Polisi Sumbangan')
            ->assertSee('Templat E-mel Notifikasi')
            ->assertSee('Pengguna')
            ->assertDontSee('Tetapan E-mel SMTP');
    }

    public function test_super_admin_sees_smtp_settings_on_system_hub(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $this->actingAs($super)
            ->get(route('settings.hub'))
            ->assertOk()
            ->assertSee('Jejak Audit')
            ->assertSee('Tetapan E-mel SMTP')
            ->assertSee('Templat E-mel Notifikasi');
    }

    public function test_alp_cannot_access_system_hub(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($user)
            ->get(route('settings.hub'))
            ->assertForbidden();
    }

}
