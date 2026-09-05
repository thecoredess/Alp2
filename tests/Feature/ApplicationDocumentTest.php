<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\FinancialYear;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Alp $alp;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolePermissionSeeder::class);
        $year = FinancialYear::factory()->active()->create();
        $this->alp = Alp::factory()->create();
        $this->user = User::factory()->create(['alp_id' => $this->alp->id])->assignRole(RoleName::ALP->value);
        $this->application = Application::factory()->create(['alp_id' => $this->alp->id, 'financial_year_id' => $year->id]);
    }

    public function test_valid_document_can_be_uploaded_to_private_storage(): void
    {
        $file = UploadedFile::fake()->create('kertas.pdf', 200, 'application/pdf');

        $this->actingAs($this->user)->post(route('applications.documents.store', $this->application), [
            'document_type' => DocumentType::KERTAS_KERJA->value,
            'file' => $file,
        ])->assertRedirect();

        $doc = ApplicationDocument::first();
        $this->assertNotNull($doc);
        $this->assertNotNull($doc->sha256);
        Storage::disk('local')->assertExists($doc->stored_path);
        // Laluan storan tidak boleh berada di bawah folder awam.
        $this->assertStringStartsWith('applications/', $doc->stored_path);
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        $exe = UploadedFile::fake()->create('malware.exe', 50, 'application/octet-stream');

        $this->actingAs($this->user)->post(route('applications.documents.store', $this->application), [
            'document_type' => DocumentType::KERTAS_KERJA->value,
            'file' => $exe,
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('application_documents', 0);
    }

    public function test_owner_can_download_their_document(): void
    {
        $file = UploadedFile::fake()->create('kertas.pdf', 100, 'application/pdf');
        $this->actingAs($this->user)->post(route('applications.documents.store', $this->application), [
            'document_type' => DocumentType::KERTAS_KERJA->value, 'file' => $file,
        ]);
        $doc = ApplicationDocument::first();

        $this->actingAs($this->user)->get(route('applications.documents.download', [$this->application, $doc]))->assertOk();
    }

    public function test_unauthorized_user_cannot_download_document(): void
    {
        $doc = ApplicationDocument::factory()->create(['application_id' => $this->application->id]);

        $otherAlp = Alp::factory()->create();
        $other = User::factory()->create(['alp_id' => $otherAlp->id])->assignRole(RoleName::ALP->value);

        $this->actingAs($other)->get(route('applications.documents.download', [$this->application, $doc]))->assertForbidden();
    }

    public function test_draft_document_can_be_removed(): void
    {
        $file = UploadedFile::fake()->create('kertas.pdf', 100, 'application/pdf');
        $this->actingAs($this->user)->post(route('applications.documents.store', $this->application), [
            'document_type' => DocumentType::KERTAS_KERJA->value, 'file' => $file,
        ]);
        $doc = ApplicationDocument::first();

        $this->actingAs($this->user)->delete(route('applications.documents.destroy', [$this->application, $doc]))->assertRedirect();
        $this->assertDatabaseCount('application_documents', 0);
        Storage::disk('local')->assertMissing($doc->stored_path);
    }

    public function test_submitted_document_cannot_be_removed(): void
    {
        $doc = ApplicationDocument::factory()->create(['application_id' => $this->application->id]);
        $this->application->update(['status' => ApplicationStatus::SUBMITTED]);

        $this->actingAs($this->user)->delete(route('applications.documents.destroy', [$this->application, $doc]))->assertForbidden();
        $this->assertDatabaseHas('application_documents', ['id' => $doc->id]);
    }
}
