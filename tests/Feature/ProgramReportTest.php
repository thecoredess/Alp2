<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ReportCardStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Services\Reports\ApplicationReportService;
use App\Services\Reports\ProgramReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class ProgramReportTest extends TestCase
{
    use BuildsWorkflow, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_reports_hub_no_longer_links_program_report_page(): void
    {
        FinancialYear::factory()->active()->create();
        $user = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('Laporan Program & Laporan Aktiviti');
    }

    public function test_program_report_route_removed(): void
    {
        $this->get(route('reports.index'))
            ->assertRedirect(route('login'));

        $this->assertSame(
            404,
            $this->actingAs($this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value))
                ->get('/laporan/program')
                ->getStatusCode(),
        );
    }

    public function test_staff_program_dashboard_summary_counts_approved_programs(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));
        $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1500.00')));

        $summary = app(ProgramReportService::class)->dashboardSummary([
            'financial_year_id' => $year->id,
        ], staffStatusFilters: true);

        $this->assertSame(2, $summary['total']);
        $this->assertSame(2, $summary['awaiting_voucher']);
        $this->assertSame(0, $summary['received']);
    }

    public function test_program_report_service_status_filtering(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $awaitingVoucher = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1000.00')));
        $received = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1100.00')));
        $received->forceFill([
            'report_card_submitted_at' => now(),
            'report_card_status' => ReportCardStatus::APPROVED,
        ])->save();

        $service = app(ProgramReportService::class);
        $rows = $service->listing(['financial_year_id' => $year->id], staffStatusFilters: true);
        $byId = $rows->keyBy(fn (array $row) => $row['application']->id);

        $this->assertSame(ApplicationReportService::FILTER_AWAITING_VOUCHER, $byId[$awaitingVoucher->id]['status']);
        $this->assertSame(ApplicationReportService::FILTER_RECEIVED, $byId[$received->id]['status']);
    }

    public function test_program_report_marks_received_and_overdue(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $received = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1000.00')));
        $received->forceFill([
            'report_card_submitted_at' => now(),
            'report_card_status' => ReportCardStatus::APPROVED,
        ])->save();

        $overdue = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1100.00')));
        $overdue->forceFill([
            'payment_status' => ApplicationPaymentStatus::VOUCHER_PREPARED,
            'payment_voucher_no' => 'BV-OVERDUE',
            'payment_voucher_date' => now()->subMonths(2)->toDateString(),
        ])->save();

        $service = app(ProgramReportService::class);
        $staffRows = $service->listing(['financial_year_id' => $year->id], staffStatusFilters: true);
        $byId = $staffRows->keyBy(fn (array $row) => $row['application']->id);

        $this->assertSame(ApplicationReportService::FILTER_RECEIVED, $byId[$received->id]['status']);
        $this->assertSame(ApplicationReportService::FILTER_PENDING, $byId[$overdue->id]['status']);

        $alpRows = $service->listing(['financial_year_id' => $year->id], staffStatusFilters: false);
        $alpById = $alpRows->keyBy(fn (array $row) => $row['application']->id);
        $this->assertSame(ApplicationReportService::FILTER_PENDING, $alpById[$overdue->id]['status']);
    }

    public function test_staff_program_report_status_dropdown_excludes_tertunggak(): void
    {
        $options = ApplicationReportService::statusFilterOptionsForUser(
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
        );

        $this->assertSame(ApplicationReportService::statusFilterOptions(), $options);
        $this->assertArrayNotHasKey(ProgramReportService::STATUS_OVERDUE, $options);
        $this->assertArrayHasKey(ApplicationReportService::FILTER_RECOMMENDED, $options);
    }

    public function test_alp_program_report_uses_operational_status_dropdown(): void
    {
        $alp = Alp::factory()->create();
        $options = ApplicationReportService::statusFilterOptionsForUser(
            $this->userWithRole(RoleName::ALP->value, $alp),
        );

        $this->assertSame(ApplicationReportService::statusFilterOptionsForAlpApplications(), $options);
        $this->assertArrayNotHasKey(ProgramReportService::STATUS_OVERDUE, $options);
        $this->assertArrayNotHasKey(ApplicationReportService::FILTER_RECOMMENDED, $options);
        $this->assertArrayHasKey(ApplicationReportService::FILTER_APPROVED, $options);
        $this->assertArrayHasKey(ApplicationReportService::FILTER_REJECTED, $options);
    }

    public function test_alp_user_only_sees_own_programs_in_service_listing(): void
    {
        $alpA = Alp::factory()->create();
        $alpB = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alpA, $year, '500000.00');
        $this->allocate($alpB, $year, '500000.00');

        $own = $this->fullyApprove($this->toPendingApproval($this->submitted($alpA, $year, '1000.00')));
        $other = $this->fullyApprove($this->toPendingApproval($this->submitted($alpB, $year, '1200.00')));

        $rows = app(ProgramReportService::class)->listing([
            'financial_year_id' => $year->id,
            'alp_id' => $alpA->id,
        ], staffStatusFilters: false);

        $numbers = $rows->pluck('application.application_number');

        $this->assertTrue($numbers->contains($own->application_number));
        $this->assertFalse($numbers->contains($other->application_number));
    }

    public function test_category_filter_narrows_service_listing(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $komuniti = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1000.00')));
        $sukan = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1100.00')));
        $sukan->forceFill(['program_category' => ProgramCategory::SUKAN])->save();

        $rows = app(ProgramReportService::class)->listing([
            'financial_year_id' => $year->id,
            'category' => ProgramCategory::SUKAN->value,
        ], staffStatusFilters: true);

        $numbers = $rows->pluck('application.application_number');

        $this->assertTrue($numbers->contains($sukan->application_number));
        $this->assertFalse($numbers->contains($komuniti->application_number));
    }
}
