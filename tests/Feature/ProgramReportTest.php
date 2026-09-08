<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ReportCardStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\FinancialYear;
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

    public function test_hub_shows_program_report_card(): void
    {
        FinancialYear::factory()->active()->create();
        $user = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Laporan Program & Laporan Aktiviti')
            ->assertSee(route('reports.programs', ['fy' => FinancialYear::active()->id], false));
    }

    public function test_program_report_lists_approved_programs_and_status(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->get(route('reports.programs', ['fy' => $year->id]))
            ->assertOk()
            ->assertSee($app->application_number)
            ->assertSee('Menunggu laporan')
            ->assertSee('Program diluluskan');
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

        $rows = app(ProgramReportService::class)->listing(['financial_year_id' => $year->id]);
        $byId = $rows->keyBy(fn (array $row) => $row['application']->id);

        $this->assertSame(ProgramReportService::STATUS_RECEIVED, $byId[$received->id]['status']);
        $this->assertSame(ProgramReportService::STATUS_OVERDUE, $byId[$overdue->id]['status']);

        $this->actingAs($this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value))
            ->get(route('reports.programs', ['fy' => $year->id, 'laporan' => ProgramReportService::STATUS_OVERDUE]))
            ->assertOk()
            ->assertSee($overdue->application_number)
            ->assertDontSee($received->application_number);
    }

    public function test_alp_user_only_sees_own_programs(): void
    {
        $alpA = Alp::factory()->create();
        $alpB = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alpA, $year, '500000.00');
        $this->allocate($alpB, $year, '500000.00');

        $own = $this->fullyApprove($this->toPendingApproval($this->submitted($alpA, $year, '1000.00')));
        $other = $this->fullyApprove($this->toPendingApproval($this->submitted($alpB, $year, '1200.00')));

        $owner = $this->userWithRole(RoleName::ALP->value, $alpA);

        $this->actingAs($owner)
            ->get(route('reports.programs', ['fy' => $year->id]))
            ->assertOk()
            ->assertSee($own->application_number)
            ->assertDontSee($other->application_number);
    }

    public function test_category_filter_narrows_listing(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $komuniti = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1000.00')));
        $sukan = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1100.00')));
        $sukan->forceFill(['program_category' => ProgramCategory::SUKAN])->save();

        $this->actingAs($this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value))
            ->get(route('reports.programs', ['fy' => $year->id, 'kategori' => ProgramCategory::SUKAN->value]))
            ->assertOk()
            ->assertSee($sukan->application_number)
            ->assertDontSee($komuniti->application_number);
    }

    public function test_authorized_export_returns_spreadsheet(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '1000.00')));

        $this->actingAs($this->userWithRole(RoleName::PENGURUSAN->value))
            ->get(route('reports.programs', ['fy' => $year->id, 'format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_guest_cannot_open_program_report(): void
    {
        $this->get(route('reports.programs'))->assertRedirect(route('login'));
    }
}
