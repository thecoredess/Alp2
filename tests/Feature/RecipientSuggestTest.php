<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientSuggestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        FinancialYear::factory()->active()->create(['year' => 2026]);
    }

    public function test_alp_receives_suggestions_from_own_past_applications(): void
    {
        $alp = Alp::factory()->create();
        $otherAlp = Alp::factory()->create();
        $year = FinancialYear::first();

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Komuniti Sentul',
            'recipient_ros_number' => 'PPM-060-01-11111111',
            'recipient_bank_account' => '1111222233',
            'recipient_address' => 'No. 1, Jalan Sentul, 51100 Kuala Lumpur',
        ]);

        Application::factory()->create([
            'alp_id' => $otherAlp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Lain',
            'recipient_ros_number' => 'PPM-060-01-99999999',
        ]);

        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        $response = $this->actingAs($user)->getJson(route('applications.recipients.suggest', [
            'q' => 'Sentul',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Persatuan Komuniti Sentul');
        $response->assertJsonPath('data.0.ros_number', 'PPM-060-01-11111111');
        $response->assertJsonPath('data.0.bank_account', '1111222233');
    }

    public function test_alp_ignores_alp_id_param_and_only_sees_own_history(): void
    {
        $alp = Alp::factory()->create();
        $other = Alp::factory()->create();
        $year = FinancialYear::first();

        Application::factory()->create([
            'alp_id' => $other->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Rahsia ALP Lain',
            'recipient_ros_number' => 'PPM-060-01-88888888',
        ]);

        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        $this->actingAs($user)->getJson(route('applications.recipients.suggest', [
            'alp_id' => $other->id,
            'q' => 'Rahsia',
        ]))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_guest_cannot_access_suggestions(): void
    {
        $this->getJson(route('applications.recipients.suggest', ['q' => 'x']))
            ->assertUnauthorized();
    }

    public function test_api_returns_single_row_for_many_applications_same_persatuan(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        Application::factory()->count(4)->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Komuniti Sentul',
            'recipient_ros_number' => 'PPM-060-01-11111111',
        ]);

        $this->actingAs($user)->getJson(route('applications.recipients.suggest', ['q' => 'Sentul']))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_check_ros_api_reports_mismatch_for_known_persatuan(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Komuniti Sentul',
            'recipient_ros_number' => 'PPM-060-01-11111111',
        ]);

        $this->actingAs($user)->getJson(route('applications.recipients.check_ros', [
            'name' => 'Persatuan Komuniti Sentul',
            'ros' => 'PPM-060-01-99999999',
        ]))
            ->assertOk()
            ->assertJsonPath('mismatch', true)
            ->assertJsonPath('recorded_ros', 'PPM-060-01-11111111');

        $this->actingAs($user)->getJson(route('applications.recipients.check_ros', [
            'name' => 'Persatuan Komuniti Sentul',
            'ros' => 'PPM-060-01-11111111',
        ]))
            ->assertOk()
            ->assertJsonPath('mismatch', false);
    }
}
