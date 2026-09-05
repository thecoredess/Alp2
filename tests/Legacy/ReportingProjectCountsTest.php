<?php

namespace Tests\Legacy;

use App\Models\Alp;
use App\Models\FinancialYear;
use App\Services\Reports\CsrReportService;
use App\Services\Reports\ProjectReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsProjects;
use Tests\TestCase;

/**
 * Legacy: kiraan projek/CSR bergantung ProjectCreationService (diputuskan Fasa 6).
 * Tidak dijalankan dalam suite aktif.
 *
 * @group legacy
 */
class ReportingProjectCountsTest extends TestCase
{
    use RefreshDatabase, BuildsProjects;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_project_status_counts_exact(): void
    {
        $this->markTestSkipped('Projek auto-create dinyahaktifkan (URS v1.2 Fasa 6).');
    }

    public function test_csr_null_beneficiary_handled_safely(): void
    {
        $this->markTestSkipped('Laporan CSR projek legacy — di luar skop URS v1.2 aktif.');
    }
}
