<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ReportCardStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Reports\ApplicationReportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/** Laporan kiraan permohonan aktif (URS M08) — tanpa projek legacy. */
class ReportingCountsTest extends TestCase
{
    use BuildsWorkflow, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_application_status_counts_exact_and_filtered_by_year(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2090]);
        $other = FinancialYear::factory()->create(['year' => 2091, 'is_active' => false]);

        Application::factory()->count(2)->create(['alp_id' => $alp->id, 'financial_year_id' => $year->id, 'status' => ApplicationStatus::DRAFT]);
        Application::factory()->create(['alp_id' => $alp->id, 'financial_year_id' => $year->id, 'status' => ApplicationStatus::APPROVED]);
        Application::factory()->create(['alp_id' => $alp->id, 'financial_year_id' => $other->id, 'status' => ApplicationStatus::DRAFT]);

        $counts = app(ApplicationReportService::class)->statusCounts(['financial_year_id' => $year->id]);
        $this->assertSame(3, $counts['total']);
        $this->assertSame(2, $counts['draft']);
        $this->assertSame(1, $counts['approved']);
    }

    public function test_application_report_status_filter_options(): void
    {
        $options = ApplicationReportService::statusFilterOptionsForUser(
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
        );

        $this->assertSame([
            'diterima' => 'Laporan aktiviti diterima',
            'dalam_semakan' => 'Dalam semakan JP',
            'dikembalikan' => 'Dikembalikan untuk pembetulan',
            'menunggu_baucar' => 'Menunggu baucar',
            'menunggu' => 'Menunggu laporan',
            'disyorkan' => 'Disyorkan',
            'diluluskan' => 'Diluluskan',
            'ditolak' => 'Ditolak',
        ], $options);
        $this->assertArrayNotHasKey('tertunggak', $options);
    }

    public function test_application_report_rejects_legacy_status_query(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2094]);
        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::DRAFT,
        ]);

        $user = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($user)
            ->get(route('reports.applications', ['fy' => $year->id, 'status' => 'draft']))
            ->assertOk()
            ->assertDontSee('value="draft"', false);

        $listing = app(ApplicationReportService::class)->listing([
            'financial_year_id' => $year->id,
            'status' => ApplicationReportService::sanitizeStatusFilter('draft', $user),
        ]);

        $this->assertCount(1, $listing);
    }

    public function test_application_report_filters_by_report_received_status(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2092]);

        $received = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::APPROVED,
            'report_card_status' => ReportCardStatus::APPROVED,
        ]);
        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::APPROVED,
            'report_card_status' => ReportCardStatus::AWAITING_ADMIN_JP,
        ]);

        $listing = app(ApplicationReportService::class)->listing([
            'financial_year_id' => $year->id,
            'status' => ApplicationReportService::FILTER_RECEIVED,
        ]);

        $this->assertCount(1, $listing);
        $this->assertSame($received->id, $listing->first()->id);
    }

    public function test_disyorkan_filter_matches_after_peraku_before_pepu(): void
    {
        $this->seedWorkflow();
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00'));

        app(\App\Services\Application\ApprovalService::class)->approve(
            $app,
            $this->userWithRole(RoleName::PELULUS->value),
            'Disyorkan oleh TP/Pengarah JP',
        );

        $app = $app->fresh();
        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->status);

        $listing = app(ApplicationReportService::class)->listing([
            'financial_year_id' => $year->id,
            'status' => ApplicationReportService::FILTER_RECOMMENDED,
        ]);

        $this->assertCount(1, $listing);
        $this->assertSame($app->id, $listing->first()->id);

        $waitingPeraku = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));
        $listing = app(ApplicationReportService::class)->listing([
            'financial_year_id' => $year->id,
            'status' => ApplicationReportService::FILTER_RECOMMENDED,
        ]);
        $this->assertCount(1, $listing);
        $this->assertNotSame($waitingPeraku->id, $listing->first()->id);
    }

    public function test_application_report_filters_rejected_applications(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2093]);

        $rejected = Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::REJECTED,
        ]);
        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'status' => ApplicationStatus::APPROVED,
        ]);

        $listing = app(ApplicationReportService::class)->listing([
            'financial_year_id' => $year->id,
            'status' => ApplicationReportService::FILTER_REJECTED,
        ]);

        $this->assertCount(1, $listing);
        $this->assertSame($rejected->id, $listing->first()->id);
    }
}
