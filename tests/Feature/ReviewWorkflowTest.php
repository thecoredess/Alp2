<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/**
 * Aliran semakan URS v1.2: Pegawai JP (secretariat) → terus PENDING_APPROVAL.
 */
class ReviewWorkflowTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    private function reviews(): ApplicationReviewService
    {
        return app(ApplicationReviewService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_secretariat_recommend_advances_to_pending_approval(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        $this->reviews()->review(
            $app,
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
            ReviewDecision::RECOMMEND,
            'OK',
            $this->lengkapChecklist(),
        );

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
        $this->assertDatabaseHas('application_reviews', ['application_id' => $app->id, 'review_type' => 'secretariat', 'decision' => 'recommend']);
    }

    public function test_secretariat_can_return_for_revision(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        $this->reviews()->review($app, ReviewType::SECRETARIAT, $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value), ReviewDecision::RETURN_FOR_REVISION, 'Sila betulkan');

        $this->assertSame(ApplicationStatus::REVISION_REQUIRED, $app->fresh()->status);
        $this->assertDatabaseHas('application_revisions', ['application_id' => $app->id, 'return_stage' => 'secretariat']);
    }

    public function test_recommend_blocked_when_checklist_incomplete(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');

        $incomplete = $this->lengkapChecklist();
        $incomplete['dokumen'] = \App\Support\JpReviewChecklist::TIDAK_LENGKAP;

        $this->expectException(ApplicationException::class);
        $this->reviews()->review(
            $app,
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
            ReviewDecision::RECOMMEND,
            'OK',
            $incomplete,
        );
    }

    public function test_http_recommend_requires_full_checklist(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => 'recommend',
                'checklist' => $this->lengkapChecklist(),
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
    }

    public function test_finance_review_not_accepted_while_awaiting_secretariat(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        $this->expectException(ApplicationException::class);
        $this->reviews()->review($app, ReviewType::FINANCE, $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value), ReviewDecision::RECOMMEND, null);
    }

    public function test_development_also_skips_to_pending_approval_after_secretariat(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00', ApplicationType::DEVELOPMENT);

        $this->reviews()->review(
            $app->fresh(),
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
            ReviewDecision::RECOMMEND,
            null,
            $this->lengkapChecklist(),
        );

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
    }

    public function test_alp_cannot_perform_review(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        $alpUser = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->actingAs($alpUser)
            ->post(route('reviews.store', [$app, 'secretariat']), ['decision' => 'recommend'])
            ->assertForbidden();
    }

    public function test_technical_review_route_store_is_not_active(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00', ApplicationType::DEVELOPMENT);

        $this->actingAs($this->userWithRole(RoleName::PEGAWAI_TEKNIKAL->value))
            ->post(route('reviews.store', [$app, 'technical']), ['decision' => 'recommend'])
            ->assertNotFound();
    }

    public function test_secretariat_show_includes_alp_budget_detail(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->get(route('reviews.show', [$app, 'secretariat']))
            ->assertOk()
            ->assertSee('Ringkasan Ledger')
            ->assertSee('Peruntukan Tahunan')
            ->assertSee('Permohonan Diluluskan')
            ->assertSee('Pending Lain')
            ->assertSee('permohonan/semua')
            ->assertSee('status=approved')
            ->assertSee('alp='.$alp->id)
            ->assertSee('Perakuan Pegawai JP')
            ->assertSee('Syor kepada Pengarah JP')
            ->assertSee('Ulasan')
            ->assertDontSee('Keputusan Semakan Admin JP')
            ->assertDontSee('Senarai Semak JP')
            ->assertDontSee('Tidak lengkap');
    }

    public function test_pegawai_jp_cannot_submit_not_recommended(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->from(route('reviews.show', [$app, 'secretariat']))
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => ReviewDecision::NOT_RECOMMENDED->value,
                'comments' => 'Tidak sesuai',
            ])
            ->assertRedirect(route('reviews.show', [$app, 'secretariat']))
            ->assertSessionHasErrors('decision');

        $this->assertSame(ApplicationStatus::SUBMITTED, $app->fresh()->status);
    }

    public function test_pegawai_jp_can_recommend_without_checklist_fields(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => ReviewDecision::RECOMMEND->value,
                'comments' => 'Disyorkan kepada Pengarah JP',
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
    }

    public function test_admin_jp_sees_full_decision_and_can_not_recommend(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('reviews.show', [$app, 'secretariat']))
            ->assertOk()
            ->assertSee('Keputusan Semakan Admin JP')
            ->assertSee('Tidak Disyorkan')
            ->assertSee('Senarai Semak JP')
            ->assertDontSee('Perakuan Pegawai JP');

        $this->actingAs($admin)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => ReviewDecision::NOT_RECOMMENDED->value,
                'comments' => 'Tidak disyorkan oleh Admin JP',
                'checklist' => $this->lengkapChecklist(),
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
        $this->assertDatabaseHas('application_reviews', [
            'application_id' => $app->id,
            'decision' => ReviewDecision::NOT_RECOMMENDED->value,
        ]);
    }
}
