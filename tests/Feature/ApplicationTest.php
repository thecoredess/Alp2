<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Models\User;
use Database\Seeders\DocumentRequirementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DocumentRequirementSeeder::class);
        FinancialYear::factory()->active()->create(['year' => 2026]);
    }

    private function alpUser(?Alp $alp = null): User
    {
        $alp ??= Alp::factory()->create();

        return User::factory()->create(['alp_id' => $alp->id])->assignRole(RoleName::ALP->value);
    }

    public function test_alp_can_create_csr_draft(): void
    {
        $user = $this->alpUser();

        $this->actingAs($user)->post(route('applications.store'), [
            'application_type' => ApplicationType::CSR->value,
            'project_title' => 'Program CSR Ujian',
        ])->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'alp_id' => $user->alp_id,
            'application_type' => ApplicationType::CSR->value,
            'status' => ApplicationStatus::DRAFT->value,
        ]);
    }

    public function test_alp_can_create_development_draft(): void
    {
        $user = $this->alpUser();

        $this->actingAs($user)->post(route('applications.store'), [
            'application_type' => ApplicationType::DEVELOPMENT->value,
            'project_title' => 'Projek Pembangunan Ujian',
        ])->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'alp_id' => $user->alp_id,
            'application_type' => ApplicationType::DEVELOPMENT->value,
        ]);
    }

    public function test_owner_can_edit_own_draft(): void
    {
        $user = $this->alpUser();
        $app = Application::factory()->create(['alp_id' => $user->alp_id]);

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), [
            'application_type' => ApplicationType::CSR->value,
            'project_title' => 'Tajuk Dikemas Kini',
            'recipient_name' => 'Persatuan Contoh',
            'recipient_ros_number' => 'ROS-12345678',
            'recipient_bank_account' => '1234567890',
            'recipient_address' => 'Kampung Baru, Kuala Lumpur',
            'program_category' => \App\Enums\ProgramCategory::KOMUNITI->value,
            'location' => 'Kuala Lumpur',
            'proposed_start_date' => now()->addMonths(3)->toDateString(),
            'compliance_declaration' => '1',
        ])->assertRedirect(route('applications.wizard.objektif', $app));

        $this->assertSame('Tajuk Dikemas Kini', $app->fresh()->project_title);
    }

    public function test_submitted_application_is_read_only_for_owner(): void
    {
        $user = $this->alpUser();
        $app = Application::factory()->submitted()->create(['alp_id' => $user->alp_id]);

        $this->actingAs($user)->get(route('applications.wizard.maklumat', $app))
            ->assertRedirect(route('applications.show', $app));

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), [
            'application_type' => ApplicationType::CSR->value,
            'project_title' => 'Cuba Ubah',
            'recipient_name' => 'Persatuan Contoh',
            'recipient_ros_number' => 'ROS-12345678',
            'recipient_bank_account' => '1234567890',
            'recipient_address' => 'Kampung Baru, Kuala Lumpur',
            'program_category' => \App\Enums\ProgramCategory::KOMUNITI->value,
            'location' => 'Kuala Lumpur',
            'proposed_start_date' => now()->addMonths(3)->toDateString(),
            'compliance_declaration' => '1',
        ])->assertForbidden();
    }

    public function test_alp_cannot_access_another_alps_application(): void
    {
        $owner = $this->alpUser();
        $app = Application::factory()->create(['alp_id' => $owner->alp_id]);

        $otherAlp = Alp::factory()->create();
        $other = User::factory()->create(['alp_id' => $otherAlp->id])->assignRole(RoleName::ALP->value);

        $this->actingAs($other)->get(route('applications.show', $app))->assertForbidden();
    }

    public function test_unauthorized_user_cannot_create_application(): void
    {
        // Pegawai kewangan tiada alp_id & tiada applications.create.
        $officer = User::factory()->create(['alp_id' => null])->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($officer)->get(route('applications.create'))->assertForbidden();
    }

    public function test_staff_with_view_all_can_see_all_applications(): void
    {
        $app = Application::factory()->create();
        $officer = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($officer)->get(route('applications.all'))->assertOk();
        $this->actingAs($officer)->get(route('applications.show', $app))->assertOk();
    }
}
