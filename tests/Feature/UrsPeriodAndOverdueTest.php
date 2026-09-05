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
use App\Services\Budget\BudgetService;
use App\Support\UrsContributionPolicy;
use Carbon\Carbon;
use Database\Seeders\DocumentRequirementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrsPeriodAndOverdueTest extends TestCase
{
    use RefreshDatabase;

    private User $alpUser;

    private Alp $alp;

    private FinancialYear $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DocumentRequirementSeeder::class);

        Carbon::setTestNow(Carbon::parse('2026-03-15 10:00:00'));

        $this->year = FinancialYear::factory()->active()->create(['year' => 2026]);
        $this->alp = Alp::factory()->create();
        $this->alpUser = User::factory()->create(['alp_id' => $this->alp->id])->assignRole(RoleName::ALP->value);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
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
            'application_type' => ApplicationType::CSR,
            'status' => ApplicationStatus::DRAFT,
        ]);
        $app->budgetItems()->create([
            'description' => 'Item', 'quantity' => 1, 'unit_cost' => $amount, 'total' => $amount, 'sort_order' => 1,
        ]);
        $app->recalculateRequestedAmount();

        foreach (DocumentRequirement::requiredFor(ApplicationType::CSR) as $t) {
            ApplicationDocument::factory()->type($t)->create(['application_id' => $app->id]);
        }

        return $app;
    }

    public function test_period_quota_blocks_when_exceeded(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, true);
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_PER_APPLICATION, '3000.00');
        SystemSetting::set(UrsContributionPolicy::KEY_PERIOD_QUOTA, '10000.00');
        $this->allocate('30000.00');

        // 4 × RM2,500 = RM10,000 — kuota tempoh penuh.
        for ($i = 0; $i < 4; $i++) {
            $app = $this->makeDraft('2500.00');
            $this->actingAs($this->alpUser)
                ->post(route('applications.submit', $app))
                ->assertRedirect(route('applications.show', $app));
        }

        $blocked = $this->makeDraft('1000.00');
        $this->actingAs($this->alpUser)
            ->post(route('applications.submit', $blocked))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(ApplicationStatus::DRAFT, $blocked->fresh()->status);
    }

    public function test_period_quota_does_not_carry_forward_from_prior_period(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, true);
        SystemSetting::set(UrsContributionPolicy::KEY_MAX_PER_APPLICATION, '3000.00');
        SystemSetting::set(UrsContributionPolicy::KEY_PERIOD_QUOTA, '10000.00');
        $this->allocate('30000.00');

        // Tempoh 1 (Mac): guna RM10,000.
        for ($i = 0; $i < 4; $i++) {
            $app = $this->makeDraft('2500.00');
            $this->actingAs($this->alpUser)->post(route('applications.submit', $app));
        }

        // Lonjak ke Tempoh 2 (Mei) — kuota baharu; baki Tempoh 1 tidak dibawa.
        Carbon::setTestNow(Carbon::parse('2026-05-10 10:00:00'));
        $next = $this->makeDraft('2500.00');
        $this->actingAs($this->alpUser)
            ->post(route('applications.submit', $next))
            ->assertRedirect(route('applications.show', $next));

        $this->assertSame(ApplicationStatus::SUBMITTED, $next->fresh()->status);
    }

    public function test_period_quota_off_allows_above_period_cap(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, false);
        $this->allocate('500000.00');
        $app = $this->makeDraft('15000.00');

        $this->actingAs($this->alpUser)
            ->post(route('applications.submit', $app))
            ->assertRedirect(route('applications.show', $app));
    }

    public function test_dashboard_lists_overdue_applications(): void
    {
        SystemSetting::set(UrsContributionPolicy::KEY_OVERDUE_DAYS, '7');
        $this->allocate('500000.00');

        $overdue = $this->makeDraft('1000.00');
        $this->actingAs($this->alpUser)->post(route('applications.submit', $overdue));
        $overdue->fresh()->update(['submitted_at' => now()->subDays(10)]);

        $fresh = $this->makeDraft('1000.00');
        $this->actingAs($this->alpUser)->post(route('applications.submit', $fresh));

        $response = $this->actingAs($this->alpUser)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Permohonan Tertunggak')
            ->assertViewHas('overdueApplications', function ($apps) use ($overdue, $fresh) {
                return $apps->contains(fn ($a) => $a->id === $overdue->id)
                    && ! $apps->contains(fn ($a) => $a->id === $fresh->id);
            });
    }

    public function test_settings_accepts_period_and_overdue_fields(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);

        $defaults = \App\Support\UrsDocumentTemplates::defaults();

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'urs_policy_enabled' => '1',
                'urs_max_annual_allocation' => '30000',
                'urs_max_per_application' => '3000',
                'urs_period_quota' => '10000',
                'urs_overdue_days' => '14',
                'template_letter_header' => $defaults[\App\Support\UrsDocumentTemplates::KEY_LETTER_HEADER],
                'template_letter_body' => $defaults[\App\Support\UrsDocumentTemplates::KEY_LETTER_BODY],
                'template_letter_footer' => $defaults[\App\Support\UrsDocumentTemplates::KEY_LETTER_FOOTER],
                'template_borang_header' => $defaults[\App\Support\UrsDocumentTemplates::KEY_BORANG_HEADER],
                'template_borang_footer' => $defaults[\App\Support\UrsDocumentTemplates::KEY_BORANG_FOOTER],
            ])
            ->assertRedirect(route('settings.edit'));

        $this->assertTrue(UrsContributionPolicy::enabled());
        $this->assertSame('10000.00', UrsContributionPolicy::maxPeriodQuota()->value());
        $this->assertSame(14, UrsContributionPolicy::overdueDays());
    }

    public function test_admin_can_update_document_templates(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SUPER_ADMIN->value);
        $defaults = \App\Support\UrsDocumentTemplates::defaults();

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'urs_policy_enabled' => '1',
                'urs_max_annual_allocation' => '30000',
                'urs_max_per_application' => '3000',
                'urs_period_quota' => '10000',
                'urs_overdue_days' => '14',
                'template_letter_header' => 'HEADER UJIAN JP',
                'template_letter_body' => $defaults[\App\Support\UrsDocumentTemplates::KEY_LETTER_BODY],
                'template_letter_footer' => $defaults[\App\Support\UrsDocumentTemplates::KEY_LETTER_FOOTER],
                'template_borang_header' => $defaults[\App\Support\UrsDocumentTemplates::KEY_BORANG_HEADER],
                'template_borang_footer' => $defaults[\App\Support\UrsDocumentTemplates::KEY_BORANG_FOOTER],
            ])
            ->assertRedirect(route('settings.edit'));

        $this->assertSame('HEADER UJIAN JP', \App\Support\UrsDocumentTemplates::letterHeader());
    }
}
