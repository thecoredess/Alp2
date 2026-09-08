<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Tetapan')
            ->assertSee('Tukar Kata Laluan')
            ->assertSee($user->email);
    }

    public function test_user_can_update_account_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Pegawai Lama',
            'unit' => 'Unit A',
        ])->assignRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Pegawai Baharu',
            'email' => 'pegawai.baharu@dbkl.test',
            'unit' => 'Jabatan Perancang',
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('Pegawai Baharu', $user->name);
        $this->assertSame('pegawai.baharu@dbkl.test', $user->email);
        $this->assertSame('Jabatan Perancang', $user->unit);
    }

    public function test_alp_user_can_update_contact_fields(): void
    {
        $alp = Alp::factory()->create([
            'name' => 'ALP Lama',
            'phone' => '011-1111111',
            'email' => 'lama@example.com',
            'address' => 'Alamat lama',
        ]);

        $user = User::factory()->create([
            'name' => 'ALP Lama',
            'email' => 'alp99@dbkl.test',
            'alp_id' => $alp->id,
        ])->assignRole(RoleName::ALP->value);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'ALP Dikemas',
            'email' => 'alp99@dbkl.test',
            'phone' => '012-9998877',
            'contact_email' => 'hubungi@example.com',
            'address' => 'No. 1, Jalan DBKL',
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();
        $alp->refresh();

        $this->assertSame('ALP Dikemas', $user->name);
        $this->assertSame('ALP Dikemas', $alp->name);
        $this->assertSame('012-9998877', $alp->phone);
        $this->assertSame('hubungi@example.com', $alp->email);
        $this->assertSame('No. 1, Jalan DBKL', $alp->address);
    }

    public function test_login_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@dbkl.test']);
        $user = User::factory()->create(['email' => 'mine@dbkl.test'])
            ->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'taken@dbkl.test',
            'unit' => 'DBKL',
        ])->assertSessionHasErrors('email');
    }

    public function test_user_can_set_avatar_icon(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'unit' => 'DBKL',
            'avatar_icon' => 'star',
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('star', $user->fresh()->avatar_icon);
    }

    public function test_user_can_upload_avatar_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar_icon' => 'user',
        ])->assignRole(RoleName::SYSTEM_ADMIN->value);

        $file = UploadedFile::fake()->image('profil.jpg', 200, 200);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'unit' => 'DBKL',
            'avatar' => $file,
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        $this->assertNull($user->avatar_icon);
        Storage::disk('public')->assertExists($user->avatar_path);
    }
}
