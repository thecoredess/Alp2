<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Recipient;
use App\Models\User;
use App\Support\OfficialManual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class ManualAndRecipientTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_maklumat_update_creates_recipient_entity(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $user = $this->userWithRole(RoleName::ALP->value, $alp);
        $app = $this->draftWithBudgetAndDocs($alp, $year, '2000.00');

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), [
            'application_type' => $app->application_type->value,
            'project_title' => $app->project_title,
            'recipient_name' => 'Persatuan Baru Ujian',
            'recipient_ros_number' => 'ROS-99887766',
            'recipient_bank_account' => '1122334455',
            'recipient_address' => 'Kampung Baru, Kuala Lumpur',
            'program_category' => \App\Enums\ProgramCategory::KOMUNITI->value,
            'location' => 'Kuala Lumpur',
            'proposed_start_date' => now()->addMonths(3)->toDateString(),
            'compliance_declaration' => '1',
        ])->assertRedirect();

        $app->refresh();
        $this->assertNotNull($app->recipient_id);
        $this->assertDatabaseHas('recipients', [
            'id' => $app->recipient_id,
            'ros_number' => 'ROS-99887766',
            'name' => 'Persatuan Baru Ujian',
        ]);
        $this->assertSame(1, Recipient::count());
    }

    public function test_admin_can_browse_recipients_index(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);
        $recipient = Recipient::create([
            'name' => 'Persatuan Senarai',
            'ros_number' => 'ROS-55667788',
            'bank_account' => '999',
            'address' => 'KL',
        ]);

        $this->actingAs($admin)
            ->get(route('recipients.index'))
            ->assertOk()
            ->assertSee('Persatuan Senarai')
            ->assertSee('ROS-55667788');

        $this->actingAs($admin)
            ->get(route('recipients.show', $recipient))
            ->assertOk()
            ->assertSee('Sejarah permohonan');
    }

    public function test_admin_can_upload_and_download_official_manual_pdf(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);
        $pdf = UploadedFile::fake()->create('manual-urs.pdf', 120, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('manual.upload'), ['manual_pdf' => $pdf])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue(OfficialManual::exists());

        $this->actingAs($admin)
            ->get(route('manual.download'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('manual.show'))
            ->assertOk()
            ->assertSee('Muat turun PDF rasmi');
    }

    public function test_alp_cannot_upload_manual_pdf(): void
    {
        Storage::fake('local');
        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);
        $pdf = UploadedFile::fake()->create('manual.pdf', 50, 'application/pdf');

        $this->actingAs($alp)
            ->post(route('manual.upload'), ['manual_pdf' => $pdf])
            ->assertForbidden();
    }
}
