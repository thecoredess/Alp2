<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_view_audit_trail(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        AuditLog::create([
            'user_id' => $super->id,
            'action' => 'USER_CREATED',
            'entity_type' => User::class,
            'entity_id' => $super->id,
            'new_values' => ['email' => $super->email],
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $this->actingAs($super)
            ->get(route('settings.audit-trail'))
            ->assertOk()
            ->assertSee('Jejak Audit Sistem')
            ->assertSee('USER_CREATED');
    }

    public function test_system_admin_cannot_view_audit_trail(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('settings.audit-trail'))
            ->assertForbidden();
    }

    public function test_super_admin_hub_shows_audit_trail_card(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $this->actingAs($super)
            ->get(route('settings.hub'))
            ->assertOk()
            ->assertSee('Jejak Audit');
    }

    public function test_user_creation_is_logged(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Pengguna Baru',
                'email' => 'baru@dbkl.test',
                'role' => RoleName::PEGAWAI_KEWANGAN->value,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'USER_CREATED',
        ]);
    }

    public function test_audit_trail_filters_by_user_and_date(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);
        $target = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        $other = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        AuditLog::create([
            'user_id' => $target->id,
            'action' => 'TARGET_ONLY',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $other->id,
            'action' => 'OTHER_ONLY',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subMonths(2),
        ]);

        $this->actingAs($super)
            ->get(route('settings.audit-trail', ['user' => $target->id]))
            ->assertOk()
            ->assertSee('TARGET_ONLY')
            ->assertSee('1 rekod dijumpai')
            ->assertDontSee('OTHER_ONLY');

        $this->actingAs($super)
            ->get(route('settings.audit-trail', [
                'user' => $target->id,
                'dari' => now()->subMonth()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('TARGET_ONLY')
            ->assertSee('1 rekod dijumpai');

        $this->actingAs($super)
            ->get(route('settings.audit-trail', [
                'dari' => now()->subMonth()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('TARGET_ONLY')
            ->assertDontSee('OTHER_ONLY');
    }

    public function test_legacy_pengguna_query_param_still_filters(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);
        $target = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        AuditLog::create([
            'user_id' => $target->id,
            'action' => 'LEGACY_FILTER',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $this->actingAs($super)
            ->get(route('settings.audit-trail').'?pengguna='.$target->id)
            ->assertOk()
            ->assertSee('LEGACY_FILTER')
            ->assertSee('1 rekod dijumpai');
    }

    public function test_mail_settings_update_is_logged(): void
    {
        $super = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $this->actingAs($super)
            ->put(route('settings.mail.update'), [
                'mail_enabled' => '1',
                'mailer' => 'log',
                'host' => 'smtp.example.test',
                'port' => '587',
                'encryption' => 'tls',
                'from_address' => 'alp@dbkl.test',
                'from_name' => 'Sistem ALP',
            ])
            ->assertRedirect(route('settings.mail.edit'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $super->id,
            'action' => 'SETTINGS_MAIL_UPDATED',
        ]);
    }
}
