<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Recipient;
use App\Models\User;
use App\Support\KertasKerjaGuide;
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

        $kertas = [];
        foreach (KertasKerjaGuide::fieldKeys() as $key) {
            $kertas[$key] = $app->{$key};
        }

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), [
            ...$kertas,
            'recipient_name' => 'Persatuan Baru Ujian',
            'recipient_ros_number' => $app->recipient_ros_number,
            'program_date' => $app->program_date?->format('Y-m-d'),
            'program_category' => $app->program_category?->value ?? 'komuniti',
            'requested_amount' => '2000.00',
            'recipient_bank_account' => '1122334455',
            'recipient_address' => 'No. 5, Jalan Ampang, 50450 Kuala Lumpur',
        ])->assertRedirect();

        $app->refresh();
        $this->assertNotNull($app->recipient_id);
        $this->assertDatabaseHas('recipients', [
            'id' => $app->recipient_id,
            'name' => 'Persatuan Baru Ujian',
            'bank_account' => '1122334455',
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
            ->assertSee('Manual PDF rasmi (M11)');
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

    public function test_manual_shows_alp_content_only_for_alp_user(): void
    {
        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);

        $response = $this->actingAs($alp)
            ->get(route('manual.show'))
            ->assertOk()
            ->assertSee('Permohonan baharu')
            ->assertSee('Muat turun Manual (Word)')
            ->assertDontSee('Tatacara Pegawai Dalaman')
            ->assertDontSee('Pegawai JP');

        if (is_file(base_path('docs/manual-pengguna/assets/menu-alp.png'))) {
            $response->assertSee('Log masuk &amp; menu', false)
                ->assertSee(route('manual.screenshot', ['name' => 'menu-alp.png']), false);
        }
    }

    public function test_alp_cannot_load_pengarah_manual_screenshot(): void
    {
        if (! is_file(base_path('docs/manual-pengguna/assets/kelulusan-pengarah.png'))) {
            $this->markTestSkipped('Screenshot Pengarah tidak wujud pada pelayan ujian.');
        }

        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($alp)
            ->get(route('manual.screenshot', ['name' => 'kelulusan-pengarah.png']))
            ->assertNotFound();
    }

    public function test_alp_can_load_own_manual_screenshot_when_asset_exists(): void
    {
        if (! is_file(base_path('docs/manual-pengguna/assets/menu-alp.png'))) {
            $this->markTestSkipped('Screenshot ALP tidak wujud pada pelayan ujian.');
        }

        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($alp)
            ->get(route('manual.screenshot', ['name' => 'menu-alp.png']))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_alp_can_download_role_manual_docx_when_file_exists(): void
    {
        $manualPath = base_path('docs/manual-pengguna/Manual-Pengguna-ALP.docx');
        if (! is_file($manualPath)) {
            $this->markTestSkipped('Manual Word ALP tidak wujud pada pelayan ujian.');
        }

        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);

        $this->actingAs($alp)
            ->get(route('manual.docx'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_admin_jp_gets_admin_manual_docx_not_alp(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('manual.show'))
            ->assertOk()
            ->assertSee('Admin Jabatan Pentadbiran')
            ->assertSee('Semakan Admin JP');
    }

    public function test_pengarah_gets_pengarah_manual_on_show(): void
    {
        $pengarah = User::factory()->create()->assignRole(RoleName::PELULUS->value);

        $this->actingAs($pengarah)
            ->get(route('manual.show'))
            ->assertOk()
            ->assertSee('Pengarah Jabatan Pentadbiran')
            ->assertSee('Pengesyoran');
    }

    public function test_pepu_sees_informed_only_summary(): void
    {
        $pepu = User::factory()->create()->assignRole(RoleName::PENGURUSAN->value);

        $this->actingAs($pepu)
            ->get(route('manual.show'))
            ->assertOk()
            ->assertSee('Tatacara PEPU / Pengurusan')
            ->assertDontSee('Menu kelulusan')
            ->assertSee('e-mel makluman');
    }

    public function test_manual_shows_jp_content_only_for_secretariat_user(): void
    {
        $jp = User::factory()->create()->assignRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->get(route('manual.show'))
            ->assertOk()
            ->assertSee('Tatacara Pegawai Dalaman')
            ->assertSee('Pegawai JP')
            ->assertSee('Permohonan baharu')
            ->assertDontSee('Tatacara ALP / Persatuan');
    }
}
