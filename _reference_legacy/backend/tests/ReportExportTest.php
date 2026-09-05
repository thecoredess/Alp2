<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function management(): User
    {
        return $this->userWithRole(RoleName::PENGURUSAN->value);
    }

    private function seedYear(): FinancialYear
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $this->allocate($alp, $year, '100000.00');

        return $year;
    }

    public function test_authorized_xlsx_export_returns_valid_spreadsheet(): void
    {
        $year = $this->seedYear();
        $res = $this->actingAs($this->management())->get(route('reports.allocation', ['fy' => $year->id, 'format' => 'xlsx']));

        $res->assertOk();
        $res->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith('PK', $res->getContent());
    }

    public function test_authorized_pdf_export_returns_valid_pdf(): void
    {
        $year = $this->seedYear();
        $res = $this->actingAs($this->management())->get(route('reports.projects', ['fy' => $year->id, 'format' => 'pdf']));

        $res->assertOk();
        $res->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }

    public function test_content_disposition_has_meaningful_filename(): void
    {
        $year = $this->seedYear();
        $res = $this->actingAs($this->management())->get(route('reports.allocation', ['fy' => $year->id, 'format' => 'xlsx']));
        $res->assertOk();
        $this->assertStringContainsString('peruntukan-alp', $res->headers->get('content-disposition'));
    }

    public function test_unauthorized_user_cannot_export(): void
    {
        $year = $this->seedYear();
        // ALP tiada reports.financial → tidak boleh capai laporan peruntukan langsung.
        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);
        $this->actingAs($alp)->get(route('reports.allocation', ['fy' => $year->id, 'format' => 'xlsx']))->assertForbidden();
    }

    public function test_alp_cannot_access_finance_dashboard_or_audit(): void
    {
        $alp = User::factory()->create()->assignRole(RoleName::ALP->value);
        $this->actingAs($alp)->get(route('dashboard.finance'))->assertForbidden();
        $this->actingAs($alp)->get(route('reports.audit'))->assertForbidden();
        $this->actingAs($alp)->get(route('dashboard.executive'))->assertForbidden();
    }

    public function test_management_can_view_dashboards_and_reports(): void
    {
        $year = $this->seedYear();
        $mgr = $this->management();
        $this->actingAs($mgr)->get(route('dashboard.executive', ['fy' => $year->id]))->assertOk();
        $this->actingAs($mgr)->get(route('reports.index'))->assertOk();
        $this->actingAs($mgr)->get(route('reports.allocation', ['fy' => $year->id]))->assertOk();
        $this->actingAs($mgr)->get(route('reports.audit'))->assertOk();
    }
}
