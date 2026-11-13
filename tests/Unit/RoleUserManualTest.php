<?php

namespace Tests\Unit;

use App\Enums\RoleName;
use App\Models\User;
use App\Support\RoleUserManual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleUserManualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_resolves_manual_by_role_priority(): void
    {
        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);
        $this->assertSame('alp', RoleUserManual::forUser($alp)?->key);

        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        $this->assertSame('admin_jp', RoleUserManual::forUser($admin)?->key);

        $pengarah = User::factory()->create()->assignRole(RoleName::PELULUS->value);
        $this->assertSame('pengarah_jp', RoleUserManual::forUser($pengarah)?->key);

        $pepu = User::factory()->create()->assignRole(RoleName::PENGURUSAN->value);
        $this->assertNull(RoleUserManual::forUser($pepu));
        $this->assertSame('pepu', RoleUserManual::audienceKey($pepu));
    }
}
