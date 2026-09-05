<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Services\Application\ApplicationBudgetService;
use App\Services\Application\ApplicationReviewService;
use App\Services\Application\ApplicationSubmissionService;
use App\Services\Application\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class RejectionAndRevisionTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_rejected_application_leaves_pending_and_creates_no_commitment(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00'));

        // Aras 1 (Peraku) lulus, kemudian tolak pada aras 2 (PEPU).
        app(ApprovalService::class)->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
        app(ApprovalService::class)->reject($app->fresh(), $this->userWithRole(RoleName::PENGURUSAN->value), 'Tidak wajar');

        $app->refresh();
        $this->assertSame(ApplicationStatus::REJECTED, $app->status);

        // Ditolak tidak lagi dikira Pending, dan tiada komitmen.
        $this->assertSame('0.00', app(ApplicationBudgetService::class)->pendingRequest($alp->id, $year->id)->value());
        $this->assertSame(0, BudgetTransaction::where('type', 'commitment')->count());
        $this->assertDatabaseHas('application_status_histories', ['application_id' => $app->id, 'to_status' => 'rejected']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'APPLICATION_REJECTED', 'entity_id' => $app->id]);
    }

    public function test_revision_preserves_history_and_allows_resubmission(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        // Urus setia kembalikan untuk pembetulan.
        app(ApplicationReviewService::class)->review($app, ReviewType::SECRETARIAT, $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value), ReviewDecision::RETURN_FOR_REVISION, 'Betulkan bajet');
        $app->refresh();
        $this->assertSame(ApplicationStatus::REVISION_REQUIRED, $app->status);

        // Rekod revisi (bukti pusingan sebelum) kekal.
        $this->assertDatabaseHas('application_revisions', ['application_id' => $app->id, 'revision_number' => 0]);

        // Masih dikira sebagai Pending Request (kapasiti dirizab).
        $this->assertSame('85000.00', app(ApplicationBudgetService::class)->pendingRequest($alp->id, $year->id)->value());

        // Pemohon mengubah bajet & hantar semula.
        $app->budgetItems()->first()->update(['unit_cost' => '90000.00', 'total' => '90000.00']);
        $app->recalculateRequestedAmount();
        app(ApplicationSubmissionService::class)->resubmit($app->fresh(), $this->userWithRole(RoleName::ALP->value, $alp));

        $app->refresh();
        $this->assertSame(ApplicationStatus::SUBMITTED, $app->status);
        $this->assertSame(1, $app->revision_number); // pusingan baharu
        $this->assertSame('90000.00', $app->requested_amount);

        // Semakan pusingan sebelum kekal sebagai sejarah.
        $this->assertDatabaseHas('application_reviews', ['application_id' => $app->id, 'revision_number' => 0]);
        // Rekod revisi ditanda dihantar semula.
        $this->assertNotNull($app->revisions()->where('revision_number', 0)->first()->resubmitted_at);
    }

    public function test_returned_application_can_be_edited_by_owner(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');
        app(ApplicationReviewService::class)->review($app, ReviewType::SECRETARIAT, $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value), ReviewDecision::RETURN_FOR_REVISION, 'Betulkan');

        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        // Boleh capai wizard & tambah item semasa REVISION_REQUIRED.
        $this->actingAs($owner)->get(route('applications.wizard.maklumat', $app->fresh()))->assertOk();
        $this->actingAs($owner)->post(route('applications.items.store', $app->fresh()), [
            'description' => 'Tambahan', 'quantity' => 1, 'unit_cost' => '100.00',
        ])->assertRedirect();
    }
}
