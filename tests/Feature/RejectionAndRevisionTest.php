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
        $app->update(['requested_amount' => '90000.00']);
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

        $this->actingAs($owner)->get(route('applications.wizard.maklumat', $app->fresh()))->assertOk();
        $this->actingAs($owner)->put(route('applications.wizard.maklumat.update', $app->fresh()), [
            'recipient_name' => 'Persatuan Dikemas Kini',
            'recipient_ros_number' => $app->recipient_ros_number,
            'program_date' => $app->program_date?->format('Y-m-d'),
            'program_category' => $app->program_category?->value ?? 'komuniti',
            'requested_amount' => '86000.00',
            'purpose' => 'Tujuan dibetulkan',
            'recipient_bank_account' => '1234567890',
            'recipient_address' => 'No. 12, Jalan Raja Laut, 50350 Kuala Lumpur',
        ])->assertRedirect();
        $this->assertSame('Tujuan dibetulkan', $app->fresh()->purpose);
    }

    public function test_alp_sees_red_cards_for_admin_jp_incomplete_checklist(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $checklist = $this->lengkapChecklist();
        $checklist['recipient'] = \App\Support\JpReviewChecklist::TIDAK_LENGKAP;
        $checklist['dokumen'] = \App\Support\JpReviewChecklist::TIDAK_LENGKAP;

        app(ApplicationReviewService::class)->review(
            $app,
            ReviewType::SECRETARIAT,
            $admin,
            ReviewDecision::RETURN_FOR_REVISION,
            'Sila betulkan maklumat persatuan dan lampiran',
            $checklist,
        );

        $this->actingAs($owner)
            ->get(route('applications.show', $app->fresh()))
            ->assertOk()
            ->assertSee('Item ditanda tidak lengkap oleh Admin JP')
            ->assertSee('border-red-400')
            ->assertSee('Tidak lengkap');

        $this->actingAs($owner)
            ->get(route('applications.wizard.maklumat', $app->fresh()))
            ->assertOk()
            ->assertSee('Item ditanda tidak lengkap oleh Admin JP')
            ->assertSee('border-red-400');
    }
}
