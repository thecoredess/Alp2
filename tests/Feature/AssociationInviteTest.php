<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationAssociationInviteService;
use App\Services\Budget\BudgetService;
use App\Notifications\ApplicationWorkflowNotification;
use App\Support\KertasKerjaGuide;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssociationInviteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        FinancialYear::factory()->active()->create(['year' => 2026]);
    }

    public function test_alp_can_issue_link_and_persatuan_can_fill_form(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-15'));
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        app(BudgetService::class)->allocate($alp, $year, '500000.00', 'REF');

        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::DRAFT,
        ]);

        $issueResponse = $this->actingAs($user)
            ->post(route('applications.association-invite.store', $app));

        $issueResponse->assertRedirect()->assertSessionHas('association_invite_url');

        $url = session('association_invite_url');
        $this->assertNotEmpty($url);

        preg_match('#borang-persatuan/([A-Za-z0-9]+)#', $url, $m);
        $token = $m[1];

        $this->get(route('association-form.show', $token))->assertOk()->assertSee('Borang Penyaluran');

        $payload = [
            'recipient_name' => 'Persatuan Jemputan Ujian',
            'recipient_ros_number' => 'PPM-060-01-55555555',
            'program_date' => '2026-04-01',
            'program_category' => 'komuniti',
            'requested_amount' => '2500.00',
            'recipient_bank_account' => '9988776655',
            'recipient_address' => 'No. 3, Jalan Raja Laut, 50350 Kuala Lumpur',
            ...KertasKerjaGuide::samplePayload(),
            'pdpa_consent' => '1',
        ];

        $this->put(route('association-form.update', $token), $payload)
            ->assertRedirect(route('association-form.show', $token))
            ->assertSessionHas('status');

        $app->refresh();
        $this->assertSame('Persatuan Jemputan Ujian', $app->recipient_name);
        $this->assertNotNull($app->association_invite_filled_at);
        $this->assertNotNull($app->association_pdpa_consented_at);

        Carbon::setTestNow();
    }

    public function test_persatuan_cannot_submit_borang_without_pdpa_consent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-15'));
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::DRAFT,
        ]);

        $url = app(ApplicationAssociationInviteService::class)->issue($app, $user);
        preg_match('#borang-persatuan/([A-Za-z0-9]+)#', $url, $m);
        $token = $m[1];

        $payload = [
            'recipient_name' => 'Persatuan Tanpa PDPA',
            'recipient_ros_number' => 'PPM-060-01-99999999',
            'program_date' => '2026-04-01',
            'program_category' => 'komuniti',
            'requested_amount' => '1500.00',
            'recipient_bank_account' => '1122334455',
            'recipient_address' => 'No. 3, Jalan Raja Laut, 50350 Kuala Lumpur',
            ...KertasKerjaGuide::samplePayload(),
        ];

        $this->from(route('association-form.show', $token))
            ->put(route('association-form.update', $token), $payload)
            ->assertRedirect(route('association-form.show', $token))
            ->assertSessionHasErrors('pdpa_consent');

        $this->assertNull($app->fresh()->association_invite_filled_at);

        Carbon::setTestNow();
    }

    public function test_persatuan_submit_notifies_alp_to_review_before_jp(): void
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::parse('2026-01-15'));
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        app(BudgetService::class)->allocate($alp, $year, '500000.00', 'REF');

        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::DRAFT,
        ]);

        $url = app(ApplicationAssociationInviteService::class)->issue($app, $user);
        preg_match('#borang-persatuan/([A-Za-z0-9]+)#', $url, $m);
        $token = $m[1];

        $payload = [
            'recipient_name' => 'Persatuan Notifikasi',
            'recipient_ros_number' => 'PPM-060-01-88888888',
            'program_date' => '2026-04-01',
            'program_category' => 'komuniti',
            'requested_amount' => '1500.00',
            'recipient_bank_account' => '1122334455',
            'recipient_address' => 'No. 3, Jalan Raja Laut, 50350 Kuala Lumpur',
            ...KertasKerjaGuide::samplePayload(),
            'pdpa_consent' => '1',
        ];

        $this->put(route('association-form.update', $token), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        Notification::assertSentTo(
            $user,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'association_form_filled',
        );

        Carbon::setTestNow();
    }

    public function test_persatuan_can_upload_contribution_documents_via_token(): void
    {
        Storage::fake('local');
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::DRAFT,
        ]);

        $url = app(ApplicationAssociationInviteService::class)->issue($app, $user);
        preg_match('#borang-persatuan/([A-Za-z0-9]+)#', $url, $m);
        $token = $m[1];

        $file = UploadedFile::fake()->create('ros.pdf', 200, 'application/pdf');

        $this->post(route('association-form.documents.store', $token), [
            'document_type' => 'sijil_ros',
            'file' => $file,
        ])->assertRedirect(route('association-form.show', $token));

        $this->assertDatabaseHas('application_documents', [
            'application_id' => $app->id,
            'document_type' => 'sijil_ros',
            'uploaded_by' => null,
        ]);
    }

    public function test_expired_link_shows_unavailable_page(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);
        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::DRAFT,
        ]);

        $service = app(ApplicationAssociationInviteService::class);
        $url = $service->issue($app, $user);
        preg_match('#borang-persatuan/([A-Za-z0-9]+)#', $url, $m);
        $token = $m[1];

        $app->update(['association_invite_expires_at' => now()->subMinute()]);

        $this->get(route('association-form.show', $token))
            ->assertOk()
            ->assertSee('Pautan tidak boleh digunakan');
    }

    public function test_public_form_can_use_recipient_suggest_via_token(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::first();
        $user = \App\Models\User::factory()->create(['alp_id' => $alp->id])
            ->assignRole(RoleName::ALP->value);

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan NGO Cadangan',
            'recipient_ros_number' => 'PPM-060-01-77777777',
        ]);

        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::DRAFT,
        ]);

        $url = app(ApplicationAssociationInviteService::class)->issue($app, $user);
        preg_match('#borang-persatuan/([A-Za-z0-9]+)#', $url, $m);
        $token = $m[1];

        $this->getJson(route('association-form.recipients.suggest', ['token' => $token, 'q' => 'NGO']))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Persatuan NGO Cadangan');

        $this->getJson(route('association-form.recipients.suggest', ['token' => $token, 'q' => 'N']))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_invalid_token_returns_generic_unavailable_without_oracle(): void
    {
        $this->get(route('association-form.show', ['token' => str_repeat('a', 48)]))
            ->assertOk()
            ->assertSee('Pautan tidak sah');
    }
}
