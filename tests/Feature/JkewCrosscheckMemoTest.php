<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\ApplicationDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class JkewCrosscheckMemoTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_secretariat_can_download_filled_crosscheck_memo_doc(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $app = $this->submittedDirect($alp, $year, '85000.00');
        $app->update([
            'recipient_name' => 'Persatuan Test',
            'recipient_ros_number' => 'PPM-060-01-12345678',
            'purpose' => 'Program Komuniti Test',
            'program_date' => now()->addMonth()->toDateString(),
        ]);

        $doc = $this->actingAs($this->userWithRole(RoleName::SYSTEM_ADMIN->value))
            ->get(route('applications.crosscheck.memo.doc', $app));

        $doc->assertOk();
        $doc->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );
        $this->assertStringStartsWith('PK', $doc->getContent());
    }

    public function test_pegawai_jp_cannot_download_or_upload_crosscheck_memo(): void
    {
        Storage::fake('local');

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $app = $this->submittedDirect($alp, $year, '85000.00');
        $pegawaiJp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);
        $file = UploadedFile::fake()->create('ulasan-jkew.pdf', 120, 'application/pdf');

        $this->actingAs($pegawaiJp)
            ->get(route('applications.crosscheck.memo.doc', $app))
            ->assertForbidden();

        $this->actingAs($pegawaiJp)
            ->post(route('applications.crosscheck.store', $app), ['file' => $file])
            ->assertForbidden();
    }

    public function test_alp_cannot_download_crosscheck_memo(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $app = $this->submittedDirect($alp, $year, '85000.00');
        $alpUser = User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        $this->actingAs($alpUser)
            ->get(route('applications.crosscheck.memo.doc', $app))
            ->assertForbidden();
    }

    public function test_jkew_can_upload_crosscheck_reference_and_alp_cannot_view(): void
    {
        Storage::fake('local');

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $app = $this->submittedDirect($alp, $year, '85000.00');
        $jkew = $this->userWithRole(RoleName::PEGAWAI_JKEW->value);
        $alpUser = User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        $file = UploadedFile::fake()->create('ulasan-jkew.pdf', 120, 'application/pdf');

        $this->actingAs($jkew)
            ->post(route('applications.crosscheck.store', $app), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHas('status');

        $doc = ApplicationDocument::query()
            ->where('application_id', $app->id)
            ->where('document_type', DocumentType::SEMAKAN_SILANG_JKEW->value)
            ->first();

        $this->assertNotNull($doc);

        $this->actingAs($jkew)
            ->get(route('applications.documents.download', [$app, $doc]))
            ->assertOk();

        $this->actingAs($alpUser)
            ->get(route('applications.documents.download', [$app, $doc]))
            ->assertForbidden();

        $this->actingAs($alpUser)
            ->get(route('applications.documents.view', [$app, $doc]))
            ->assertForbidden();
    }
}
