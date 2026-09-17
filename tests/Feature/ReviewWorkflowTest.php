<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\ReviewDecision;
use App\Enums\ReviewType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\FinancialYear;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/**
 * Aliran semakan: Admin JP → Pegawai JP → PENDING_APPROVAL (Pengarah/PEPU).
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

    public function test_admin_jp_recommend_goes_to_pegawai_jp_not_pengarah(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        $this->reviews()->review(
            $app,
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::SYSTEM_ADMIN->value),
            ReviewDecision::RECOMMEND,
            'OK Admin',
            $this->lengkapChecklist(),
        );

        $this->assertSame(ApplicationStatus::UNDER_SECRETARIAT_REVIEW, $app->fresh()->status);
        $this->assertDatabaseHas('application_reviews', [
            'application_id' => $app->id,
            'review_type' => 'secretariat',
            'decision' => 'recommend',
        ]);
    }

    public function test_pegawai_jp_syor_after_admin_advances_to_pending_approval(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->afterAdminJpReview($this->submitted($alp, $year, '85000.00'));

        $this->reviews()->review(
            $app,
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
            ReviewDecision::RECOMMEND,
            'Syor Pengarah',
            $this->lengkapChecklist(),
        );

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
    }

    public function test_pegawai_jp_cannot_review_before_admin_jp(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        $this->expectException(ApplicationException::class);
        $this->reviews()->review(
            $app,
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value),
            ReviewDecision::RECOMMEND,
            'Awal',
            $this->lengkapChecklist(),
        );
    }

    public function test_secretariat_can_return_for_revision(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '85000.00');

        $this->reviews()->review(
            $app,
            ReviewType::SECRETARIAT,
            $this->userWithRole(RoleName::SYSTEM_ADMIN->value),
            ReviewDecision::RETURN_FOR_REVISION,
            'Sila betulkan',
            $this->lengkapChecklist(),
        );

        $this->assertSame(ApplicationStatus::REVISION_REQUIRED, $app->fresh()->status);
        $this->assertDatabaseHas('application_revisions', ['application_id' => $app->id, 'return_stage' => 'secretariat']);
    }

    public function test_recommend_blocked_without_jkew_crosscheck_borang(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $app->documents()->where('document_type', DocumentType::SEMAKAN_SILANG_JKEW->value)->delete();
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => 'recommend',
                'checklist' => $this->lengkapChecklist(),
            ])
            ->assertSessionHasErrors('crosscheck');

        $this->expectException(ApplicationException::class);
        $this->reviews()->review(
            $app->fresh(),
            ReviewType::SECRETARIAT,
            $admin,
            ReviewDecision::RECOMMEND,
            'OK',
            $this->lengkapChecklist(),
        );
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
            $this->userWithRole(RoleName::SYSTEM_ADMIN->value),
            ReviewDecision::RECOMMEND,
            'OK',
            $incomplete,
        );
    }

    public function test_http_admin_recommend_requires_full_checklist(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => 'recommend',
                'checklist' => $this->lengkapChecklist(),
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $this->assertSame(ApplicationStatus::UNDER_SECRETARIAT_REVIEW, $app->fresh()->status);
    }

    public function test_finance_review_not_accepted_while_awaiting_secretariat(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');

        $this->expectException(ApplicationException::class);
        $this->reviews()->review(
            $app,
            ReviewType::FINANCE,
            $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value),
            ReviewDecision::RECOMMEND,
            'OK',
        );
    }

    public function test_development_also_skips_to_pending_approval_after_pegawai(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->status);
    }

    public function test_admin_jp_can_update_borang_during_secretariat_review(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($admin)
            ->get(route('reviews.show', [$app, 'secretariat']))
            ->assertOk()
            ->assertSee('Kemaskini');

        $this->actingAs($admin)
            ->put(route('reviews.borang.update', [$app, 'secretariat']), [
                'recipient_name' => 'Persatuan Kemaskini Semakan',
                'recipient_ros_number' => $app->recipient_ros_number,
                'program_date' => $app->program_date->format('Y-m-d'),
                'program_category' => $app->program_category->value,
                'requested_amount' => '3000.00',
                'purpose' => 'Tujuan dikemaskini semasa semakan',
                'recipient_bank_account' => $app->recipient_bank_account,
                'recipient_address' => $app->recipient_address,
            ])
            ->assertRedirect(route('reviews.show', [$app, 'secretariat']))
            ->assertSessionHas('status');

        $app->refresh();
        $this->assertSame('Persatuan Kemaskini Semakan', $app->recipient_name);
        $this->assertSame('3000.00', $app->requested_amount);
    }

    public function test_secretariat_show_includes_alp_budget_detail_for_pegawai_after_admin(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->afterAdminJpReview($this->submitted($alp, $year, '2500.00'));
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->get(route('reviews.show', [$app, 'secretariat']))
            ->assertOk()
            ->assertSee('Ringkasan Kewangan')
            ->assertSee('Peruntukan Tahunan')
            ->assertSee('Permohonan Diluluskan')
            ->assertSee('Dalam Proses')
            ->assertSee('permohonan/semua')
            ->assertSee('status=diluluskan')
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
        $app = $this->afterAdminJpReview($this->submitted($alp, $year, '2500.00'));
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->from(route('reviews.show', [$app, 'secretariat']))
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => ReviewDecision::NOT_RECOMMENDED->value,
                'comments' => 'Tidak sesuai',
            ])
            ->assertRedirect(route('reviews.show', [$app, 'secretariat']))
            ->assertSessionHasErrors('decision');

        $this->assertSame(ApplicationStatus::UNDER_SECRETARIAT_REVIEW, $app->fresh()->status);
    }

    public function test_pegawai_jp_can_recommend_without_checklist_fields(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->afterAdminJpReview($this->submitted($alp, $year, '2500.00'));
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => ReviewDecision::RECOMMEND->value,
                'comments' => 'Disyorkan kepada Pengarah JP',
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
    }

    public function test_admin_jp_sees_full_decision_and_not_recommended_goes_to_pegawai(): void
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

        $this->assertSame(ApplicationStatus::UNDER_SECRETARIAT_REVIEW, $app->fresh()->status);
        $this->assertDatabaseHas('application_reviews', [
            'application_id' => $app->id,
            'decision' => ReviewDecision::NOT_RECOMMENDED->value,
        ]);
    }

    public function test_admin_queue_only_submitted_pegawai_queue_only_after_admin(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $submitted = $this->submitted($alp, $year, '1000.00');
        $awaitingPegawai = $this->afterAdminJpReview($this->submitted($alp, $year, '1100.00'));

        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($admin)
            ->get(route('reviews.secretariat'))
            ->assertOk()
            ->assertSee($submitted->application_number)
            ->assertDontSee($awaitingPegawai->application_number);

        $this->actingAs($jp)
            ->get(route('reviews.secretariat'))
            ->assertOk()
            ->assertSee($awaitingPegawai->application_number)
            ->assertDontSee($submitted->application_number);
    }

    public function test_secretariat_queue_filters_by_program_category(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create(['year' => 2096]);
        $this->allocate($alp, $year, '500000.00');

        $sukan = $this->afterAdminJpReview($this->submitted($alp, $year, '1200.00'));
        $sukan->forceFill(['program_category' => \App\Enums\ProgramCategory::SUKAN])->save();

        $komuniti = $this->afterAdminJpReview($this->submitted($alp, $year, '1300.00'));
        $komuniti->forceFill(['program_category' => \App\Enums\ProgramCategory::KOMUNITI])->save();

        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->actingAs($jp)
            ->get(route('reviews.secretariat', ['jenis' => 'sukan']))
            ->assertOk()
            ->assertSee('Program sukan')
            ->assertSee($sukan->application_number)
            ->assertDontSee($komuniti->application_number);
    }
}
