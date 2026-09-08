<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentRequirement;
use App\Models\FinancialYear;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use App\Services\Application\ApplicationSubmissionService;
use App\Services\Budget\BudgetService;
use App\Support\UrsContributionPolicy;
use Database\Seeders\DocumentRequirementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UrsPolicyAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $alpUser;

    private User $secretariat;

    private Alp $alp;

    private FinancialYear $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DocumentRequirementSeeder::class);

        $this->year = FinancialYear::factory()->active()->create(['year' => 2026]);
        $this->alp = Alp::factory()->create();
        $this->alpUser = User::factory()->create(['alp_id' => $this->alp->id])->assignRole(RoleName::ALP->value);
        $this->secretariat = User::factory()->create()
            ->assignRole(RoleName::PEGAWAI_URUSSETIA->value);
    }

    private function allocate(string $amount): void
    {
        app(BudgetService::class)->allocate($this->alp, $this->year, $amount, 'REF');
    }

    private function makeDraft(string $amount): Application
    {
        $app = Application::factory()->create([
            'alp_id' => $this->alp->id,
            'financial_year_id' => $this->year->id,
            'application_type' => ApplicationType::SUMBANGAN,
            'status' => ApplicationStatus::DRAFT,
            'requested_amount' => $amount,
            'purpose' => 'Tujuan ujian',
            'recipient_name' => 'Persatuan Ujian',
            'recipient_bank_account' => '1234567890',
        ]);

        foreach (DocumentRequirement::requiredFor() as $t) {
            ApplicationDocument::factory()->type($t)->create(['application_id' => $app->id]);
        }

        return $app;
    }

    public function test_urs_policy_off_allows_amount_above_three_thousand(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, false);
        $this->allocate('500000.00');
        $app = $this->makeDraft('85000.00');

        $this->actingAs($this->alpUser)
            ->post(route('applications.submit', $app))
            ->assertRedirect(route('applications.show', $app));

        $this->assertSame(ApplicationStatus::SUBMITTED, $app->fresh()->status);
    }

    public function test_urs_policy_on_blocks_amount_above_max_per_application(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, true);
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_PER_APPLICATION, '3000.00');
        $this->allocate('30000.00');
        $app = $this->makeDraft('85000.00');

        $this->actingAs($this->alpUser)
            ->post(route('applications.submit', $app))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(ApplicationStatus::DRAFT, $app->fresh()->status);
    }

    public function test_urs_policy_on_allows_amount_within_limit(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, true);
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_PER_APPLICATION, '3000.00');
        $this->allocate('30000.00');
        $app = $this->makeDraft('2500.00');

        $this->actingAs($this->alpUser)
            ->post(route('applications.submit', $app))
            ->assertRedirect(route('applications.show', $app));

        $this->assertSame(ApplicationStatus::SUBMITTED, $app->fresh()->status);
    }

    public function test_submission_notifies_admin_jp(): void
    {
        Notification::fake();
        $this->allocate('500000.00');
        $app = $this->makeDraft('1000.00');
        $admin = User::factory()->create()->assignRole(RoleName::SYSTEM_ADMIN->value);

        app(ApplicationSubmissionService::class)->submit($app, $this->alpUser);

        Notification::assertSentTo(
            $admin,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'submitted'
        );

        Notification::assertNotSentTo(
            $this->secretariat,
            ApplicationWorkflowNotification::class,
        );
    }

    public function test_approved_application_letter_is_printable(): void
    {
        $this->allocate('500000.00');
        $app = $this->makeDraft('1000.00');
        app(ApplicationSubmissionService::class)->submit($app, $this->alpUser);
        $app->update(['status' => ApplicationStatus::APPROVED]);

        $this->actingAs($this->alpUser)
            ->get(route('applications.letter', $app))
            ->assertOk()
            ->assertSee($app->application_number)
            ->assertSee('DILULUSKAN');
    }

    public function test_letter_forbidden_when_not_approved(): void
    {
        $app = $this->makeDraft('1000.00');

        $this->actingAs($this->alpUser)
            ->get(route('applications.letter', $app))
            ->assertForbidden();
    }

    public function test_settings_page_requires_permission(): void
    {
        $this->actingAs($this->alpUser)
            ->get(route('settings.edit'))
            ->assertForbidden();

        $admin = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);
        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Polisi URS')
            ->assertSee('Templat dokumen');
    }
}
