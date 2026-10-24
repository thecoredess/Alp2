<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationShellDraftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        FinancialYear::factory()->active()->create(['year' => 2026]);
    }

    public function test_alp_can_create_shell_draft_without_borang_fields(): void
    {
        $alp = Alp::factory()->create();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        $response = $this->actingAs($user)->post(route('applications.store.shell'), []);

        $response->assertRedirect();
        $response->assertSessionHas('association_invite_url');

        $app = Application::query()->where('alp_id', $alp->id)->first();
        $this->assertNotNull($app);
        $this->assertSame(ApplicationStatus::DRAFT, $app->status);
        $this->assertNull($app->recipient_name);
        $this->assertNotNull($app->association_invite_expires_at);
    }
}
