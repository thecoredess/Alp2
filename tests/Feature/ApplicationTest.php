<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Models\User;
use App\Services\Reports\ApplicationReportService;
use Carbon\Carbon;
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

    private function borangPayload(array $overrides = []): array
    {
        return array_merge([
            'recipient_name' => 'Persatuan Komuniti Ujian',
            'recipient_ros_number' => 'ROS-12345678',
            'program_date' => now()->addMonths(3)->format('Y-m-d'),
            'program_category' => 'komuniti',
            'requested_amount' => '1500.00',
            'purpose' => 'Sumbangan program komuniti',
            'recipient_bank_account' => '1234567890',
            'recipient_address' => 'No. 12, Jalan Ampang, 50450 Kuala Lumpur',
        ], $overrides);
    }

    public function test_alp_can_create_sumbangan_draft(): void
    {
        $user = $this->alpUser();

        $this->actingAs($user)->post(route('applications.store'), $this->borangPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'alp_id' => $user->alp_id,
            'application_type' => ApplicationType::SUMBANGAN->value,
            'purpose' => 'Sumbangan program komuniti',
            'requested_amount' => '1500.00',
            'status' => ApplicationStatus::DRAFT->value,
        ]);
    }

    public function test_owner_can_edit_own_draft(): void
    {
        $user = $this->alpUser();
        $app = Application::factory()->create(['alp_id' => $user->alp_id]);

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), $this->borangPayload([
            'purpose' => 'Tujuan dikemas kini',
            'requested_amount' => '2000.00',
        ]))->assertRedirect(route('applications.wizard.dokumen', $app));

        $this->assertSame('Tujuan dikemas kini', $app->fresh()->purpose);
    }

    public function test_borang_rejects_program_date_within_two_months(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-15'));
        $user = $this->alpUser();

        $this->actingAs($user)->post(route('applications.store'), $this->borangPayload([
            'program_date' => '2026-04-01',
        ]))->assertSessionHasErrors('program_date');

        Carbon::setTestNow();
    }

    public function test_borang_rejects_non_kl_address(): void
    {
        $user = $this->alpUser();
        $app = Application::factory()->create(['alp_id' => $user->alp_id]);

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), $this->borangPayload([
            'recipient_address' => 'Persatuan Komuniti, Johor Bahru, Johor',
        ]))
            ->assertSessionHasErrors('recipient_address');
    }

    public function test_borang_rejects_amount_above_policy_limit(): void
    {
        \App\Models\SystemSetting::set(\App\Support\UrsContributionPolicy::KEY_ENABLED, true);
        \App\Models\SystemSetting::set(\App\Support\UrsContributionPolicy::KEY_MAX_PER_APPLICATION, '3000.00');

        $user = $this->alpUser();
        $app = Application::factory()->create(['alp_id' => $user->alp_id]);

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), $this->borangPayload([
            'requested_amount' => '5000.00',
        ]))
            ->assertSessionHasErrors('requested_amount');

        $this->assertNotSame('5000.00', $app->fresh()->requested_amount);
    }

    public function test_submitted_application_is_read_only_for_owner(): void
    {
        $user = $this->alpUser();
        $app = Application::factory()->submitted()->create(['alp_id' => $user->alp_id]);

        $this->actingAs($user)->get(route('applications.wizard.maklumat', $app))
            ->assertRedirect(route('applications.show', $app));

        $this->actingAs($user)->put(route('applications.wizard.maklumat.update', $app), $this->borangPayload([
            'purpose' => 'Cuba Ubah',
        ]))->assertForbidden();
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
        $officer = User::factory()->create(['alp_id' => null])->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($officer)->get(route('applications.create'))->assertForbidden();
    }

    public function test_admin_jp_can_create_short_notice_application_for_alp(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-15'));
        $alp = Alp::factory()->create();
        $admin = User::factory()->create(['alp_id' => null])->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)->get(route('applications.create'))->assertOk()->assertSee('Notis pendek dibenarkan');

        $this->actingAs($admin)->post(route('applications.store'), $this->borangPayload([
            'alp_id' => $alp->id,
            'program_date' => '2026-04-01',
        ]))->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'alp_id' => $alp->id,
            'created_by' => $admin->id,
            'program_date' => '2026-04-01',
            'status' => ApplicationStatus::DRAFT->value,
        ]);

        Carbon::setTestNow();
    }

    public function test_admin_jp_amount_field_shows_live_max_warning_like_alp(): void
    {
        $admin = User::factory()->create(['alp_id' => null])->assignRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('applications.create'))
            ->assertOk()
            ->assertSee('Had setiap permohonan: RM')
            ->assertSee('forceMaxPerApp: true', false)
            ->assertSee('Ralat jumlah sumbangan');
    }

    public function test_admin_jp_cannot_create_application_above_rm3000(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::query()->first();
        $admin = User::factory()->create(['alp_id' => null])->assignRole(RoleName::SYSTEM_ADMIN->value);
        app(\App\Services\Budget\BudgetService::class)->allocate($alp, $year, '500000.00', 'REF-MAX');

        $this->actingAs($admin)->post(route('applications.store'), $this->borangPayload([
            'alp_id' => $alp->id,
            'requested_amount' => '3000.01',
        ]))->assertSessionHasErrors('requested_amount');

        $this->actingAs($admin)->post(route('applications.store'), $this->borangPayload([
            'alp_id' => $alp->id,
            'requested_amount' => '3000.00',
        ]))->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'alp_id' => $alp->id,
            'created_by' => $admin->id,
            'requested_amount' => '3000.00',
        ]);
    }

    public function test_admin_jp_can_submit_short_notice_application_to_pegawai_jp(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-15'));
        $alp = Alp::factory()->create();
        $year = FinancialYear::query()->where('status', 'active')->first()
            ?? FinancialYear::factory()->active()->create(['year' => 2026]);
        $admin = User::factory()->create(['alp_id' => null])->assignRole(RoleName::SYSTEM_ADMIN->value);

        app(\App\Services\Budget\BudgetService::class)->allocate($alp, $year, '500000.00', 'REF-ADMIN');

        $app = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'application_type' => ApplicationType::SUMBANGAN,
            'status' => ApplicationStatus::DRAFT,
            'requested_amount' => '1500.00',
            'program_date' => '2026-04-01',
            'created_by' => $admin->id,
        ]);

        foreach (\App\Models\DocumentRequirement::requiredFor() as $docType) {
            \App\Models\ApplicationDocument::factory()->type($docType)->create(['application_id' => $app->id]);
        }

        $this->actingAs($admin)
            ->post(route('applications.submit', $app))
            ->assertRedirect(route('applications.show', $app))
            ->assertSessionHas('status');

        $this->assertSame(ApplicationStatus::SUBMITTED, $app->fresh()->status);
        $this->assertStringContainsString('Pegawai JP', session('status'));

        Carbon::setTestNow();
    }

    public function test_staff_with_view_all_can_see_all_applications(): void
    {
        $app = Application::factory()->create();
        $officer = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($officer)->get(route('applications.all'))->assertOk();
        $this->actingAs($officer)->get(route('applications.show', $app))->assertOk();
    }

    public function test_all_applications_uses_operational_status_filter_like_report(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2095]);
        $officer = User::factory()->create()->assignRole(RoleName::PEGAWAI_URUSSETIA->value);

        $inReview = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::SUBMITTED,
            'application_number' => 'ALP-2095-IN-REVIEW',
        ]);
        $approved = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::APPROVED,
            'application_number' => 'ALP-2095-APPROVED',
        ]);

        $this->actingAs($officer)
            ->get(route('applications.all', [
                'status' => ApplicationReportService::FILTER_IN_REVIEW,
                'tahun' => $year->id,
            ]))
            ->assertOk()
            ->assertSee('Dalam semakan JP')
            ->assertSee($inReview->application_number)
            ->assertDontSee($approved->application_number);

        $this->actingAs($officer)
            ->get(route('applications.all', ['status' => 'draft']))
            ->assertOk()
            ->assertDontSee('value="draft"', false);
    }

    public function test_alp_applications_use_operational_status_filter_without_recommended(): void
    {
        $alp = Alp::factory()->create();
        $user = $this->alpUser($alp);
        $year = FinancialYear::first();

        $inReview = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::SUBMITTED,
        ]);
        $approved = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::APPROVED,
        ]);

        $options = ApplicationReportService::statusFilterOptionsForAlpApplications();

        $this->assertArrayNotHasKey(ApplicationReportService::FILTER_RECOMMENDED, $options);
        $this->assertArrayHasKey(ApplicationReportService::FILTER_IN_REVIEW, $options);
        $this->assertArrayHasKey(ApplicationReportService::FILTER_APPROVED, $options);

        $this->actingAs($user)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Menunggu laporan')
            ->assertSee('Diluluskan')
            ->assertDontSee('value="disyorkan"', false)
            ->assertDontSee('>Disyorkan<', false);

        $this->actingAs($user)
            ->get(route('applications.index', [
                'status' => ApplicationReportService::FILTER_IN_REVIEW,
                'tahun' => $year->id,
            ]))
            ->assertOk()
            ->assertSee($inReview->application_number)
            ->assertDontSee($approved->application_number);
    }

    public function test_display_heading_maps_approval_placeholders_to_kelulusan_pepu(): void
    {
        $app = Application::factory()->create([
            'purpose' => Application::SIMULATION_PREFIX.'Menunggu Peraku (TP/Pengarah JP)',
            'program_category' => 'komuniti',
        ]);

        $this->assertSame('Kelulusan PEPU', $app->fresh()->displayHeading());
    }

    public function test_simulation_prefix_is_hidden_from_purpose_display(): void
    {
        $app = Application::factory()->create([
            'purpose' => Application::SIMULATION_PREFIX.'Menunggu Peraku (TP/Pengarah JP)',
        ]);

        $app = $app->fresh();

        $this->assertSame('Menunggu Peraku (TP/Pengarah JP)', $app->purpose);
        $this->assertSame('Menunggu Peraku (TP/Pengarah JP)', $app->project_title);
        $this->assertSame(
            Application::SIMULATION_PREFIX.'Menunggu Peraku (TP/Pengarah JP)',
            $app->getRawOriginal('purpose'),
        );
    }

    public function test_report_program_label_ignores_workflow_placeholder_purpose(): void
    {
        $app = Application::factory()->create([
            'purpose' => Application::SIMULATION_PREFIX.'Permohonan ditolak',
            'program_category' => 'kemasyarakatan',
            'recipient_name' => 'Persatuan Komuniti Simulasi KL',
        ]);

        $this->assertSame('Program aktiviti kemasyarakatan', $app->fresh()->programLabelForReport());
        $this->assertSame('Persatuan Komuniti Simulasi KL', $app->fresh()->recipientLabelForReport());
    }

    public function test_report_program_label_uses_real_purpose_when_available(): void
    {
        $app = Application::factory()->create([
            'purpose' => 'MAIN BOWLING',
            'recipient_name' => 'PERSATUAN PPTM',
        ]);

        $this->assertSame('MAIN BOWLING', $app->fresh()->programLabelForReport());
        $this->assertSame('PERSATUAN PPTM', $app->fresh()->recipientLabelForReport());
    }

    public function test_report_program_label_ignores_simulation_workflow_placeholders(): void
    {
        $placeholders = [
            'Diluluskan — baucar disedia',
            'Diluluskan — menunggu baucar',
            'Menunggu kelulusan PEPU',
        ];

        foreach ($placeholders as $purpose) {
            $app = Application::factory()->create([
                'purpose' => Application::SIMULATION_PREFIX.$purpose,
                'program_category' => 'sukan',
            ]);

            $this->assertSame(
                'Program sukan',
                $app->fresh()->programLabelForReport(),
                "Expected category fallback for placeholder: {$purpose}",
            );
        }
    }
}
