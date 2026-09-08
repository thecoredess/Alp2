<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_flagged_must_change_password_is_redirected(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('profile.edit').'#kata-laluan');
    }

    public function test_user_can_reach_profile_page_while_flagged(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Tukar Kata Laluan');
    }

    public function test_old_password_url_redirects_to_profile(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user)->get(route('password.change'))
            ->assertRedirect(route('profile.edit').'#kata-laluan');
    }

    public function test_changing_password_clears_the_flag(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpass123'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user)->put(route('password.change.update'), [
            'current_password' => 'oldpass123',
            'password' => 'newpass456',
            'password_confirmation' => 'newpass456',
        ])->assertRedirect(route('profile.edit').'#kata-laluan');

        $this->assertFalse($user->fresh()->must_change_password);
    }
}
