<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_admin_can_create_user_with_temp_password_and_role(): void
    {
        $response = $this->actingAs($this->admin())->post(route('users.store'), [
            'name' => 'Pegawai Baharu',
            'email' => 'baharu@dbkl.test',
            'role' => RoleName::PEGAWAI_KEWANGAN->value,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('temp_password');

        $user = User::where('email', 'baharu@dbkl.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->hasRole(RoleName::PEGAWAI_KEWANGAN->value));
    }

    public function test_alp_role_requires_an_alp_to_be_selected(): void
    {
        $this->actingAs($this->admin())->post(route('users.store'), [
            'name' => 'ALP User',
            'email' => 'alpuser@dbkl.test',
            'role' => RoleName::ALP->value,
            // alp_id sengaja ditinggalkan
        ])->assertSessionHasErrors('alp_id');
    }

    public function test_admin_can_create_alp_linked_user(): void
    {
        $alp = Alp::factory()->create();

        $this->actingAs($this->admin())->post(route('users.store'), [
            'name' => 'ALP User',
            'email' => 'alpuser2@dbkl.test',
            'role' => RoleName::ALP->value,
            'alp_id' => $alp->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'alpuser2@dbkl.test', 'alp_id' => $alp->id]);
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('users.deactivate', $admin))->assertForbidden();

        $this->assertTrue($admin->fresh()->isActive());
    }

    public function test_reset_password_marks_must_change_password(): void
    {
        $target = User::factory()->create(['must_change_password' => false]);

        $this->actingAs($this->admin())
            ->post(route('users.reset-password', $target))
            ->assertSessionHas('temp_password');

        $this->assertTrue($target->fresh()->must_change_password);
    }
}
