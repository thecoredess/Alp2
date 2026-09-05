<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Reports\ApplicationReportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Laporan kiraan permohonan aktif (URS M08) — tanpa projek legacy. */
class ReportingCountsTest extends TestCase
{
    use RefreshDatabase;

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
}
