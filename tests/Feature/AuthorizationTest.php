<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(RoleName $role): User
    {
        return User::factory()->create()->assignRole($role->value);
    }

    public function test_alp_cannot_access_user_management(): void
    {
        $alp = $this->userWithRole(RoleName::ALP);

        $this->actingAs($alp)->get(route('users.index'))->assertForbidden();
    }

    public function test_alp_cannot_access_financial_years(): void
    {
        $alp = $this->userWithRole(RoleName::ALP);

        $this->actingAs($alp)->get(route('financial-years.index'))->assertForbidden();
    }

    public function test_system_admin_can_access_user_management(): void
    {
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN);

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
    }

    public function test_super_admin_has_full_access_via_permissions(): void
    {
        $super = $this->userWithRole(RoleName::SUPER_ADMIN);

        $this->actingAs($super)->get(route('users.index'))->assertOk();
        $this->actingAs($super)->get(route('financial-years.index'))->assertOk();
        $this->actingAs($super)->get(route('alps.index'))->assertOk();
    }

    public function test_management_role_can_view_but_not_create_alp(): void
    {
        $mgmt = $this->userWithRole(RoleName::PENGURUSAN);

        $this->actingAs($mgmt)->get(route('alps.index'))->assertOk();
        $this->actingAs($mgmt)->get(route('alps.create'))->assertForbidden();
    }

    public function test_pepu_cannot_access_reports_module(): void
    {
        $pepu = $this->userWithRole(RoleName::PENGURUSAN);

        $this->actingAs($pepu)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($pepu)->get(route('settings.hub'))->assertForbidden();
        $this->actingAs($pepu)->get(route('dashboard'))->assertOk()
            ->assertDontSee('href="'.route('reports.index').'"', false)
            ->assertDontSee('href="'.route('report-cards.index').'"', false)
            ->assertDontSee('href="'.route('settings.hub').'"', false);
    }

    public function test_all_roles_can_view_dashboard(): void
    {
        foreach (RoleName::cases() as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->get(route('dashboard'))->assertOk();
        }
    }
}
