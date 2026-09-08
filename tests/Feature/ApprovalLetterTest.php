<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Services\Documents\ApprovalLetterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class ApprovalLetterTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_approved_application_renders_lulus_letter(): void
    {
        $alp = Alp::factory()->create(['name' => 'Datuk Test ALP', 'address' => 'Kuala Lumpur']);
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));
        $app->update(['recipient_name' => 'Persatuan Komuniti Test']);

        $html = app(ApprovalLetterService::class)->html($app)->render();

        $this->assertStringContainsString('DILULUSKAN', $html);
        $this->assertStringContainsString('Persatuan Komuniti Test', $html);
        $this->assertStringContainsString('DBKL.JP.100-19/1/3 Jld. 4', $html);
        $this->assertStringContainsString('NORHASLINDA BINTI NORDIN', $html);
    }

    public function test_rejected_application_renders_tidak_lulus_letter(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00'));
        app(\App\Services\Application\ApprovalService::class)->reject(
            $app,
            $this->userWithRole(RoleName::PELULUS->value),
            'Tidak memenuhi kriteria',
        );

        $html = app(ApprovalLetterService::class)->html($app->fresh())->render();

        $this->assertStringContainsString('TIDAK DILULUSKAN', $html);
    }

    public function test_alp_can_open_letter_for_approved_application(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->actingAs($owner)
            ->get(route('applications.letter', $app))
            ->assertOk()
            ->assertSee('DILULUSKAN');
    }

    public function test_letter_unavailable_for_pending_application(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->actingAs($owner)
            ->get(route('applications.letter', $app))
            ->assertForbidden();
    }

    public function test_template_docx_is_bundled(): void
    {
        $this->assertTrue(app(ApprovalLetterService::class)->templateExists());
    }
}
