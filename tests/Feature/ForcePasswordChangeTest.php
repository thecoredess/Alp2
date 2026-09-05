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
            ->assertRedirect(route('password.change'));
    }

    public function test_user_can_reach_change_password_page_while_flagged(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user)->get(route('password.change'))->assertOk();
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
        ])->assertRedirect(route('dashboard'));

        $this->assertFalse($user->fresh()->must_change_password);
    }
}
