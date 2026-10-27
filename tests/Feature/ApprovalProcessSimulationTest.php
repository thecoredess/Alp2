<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Notifications\ApplicationWorkflowNotification;
use App\Services\Application\ApplicationException;
use App\Services\Application\ApplicationTimelineService;
use App\Services\Application\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/**
 * Simulasi UAT automatik: proses kelulusan sumbangan ALP (URS).
 *
 * ALP hantar → Admin JP semak & syor → Pengarah JP lulus → PEPU e-mel makluman (tiada kelulusan dalam sistem).
 */
class ApprovalProcessSimulationTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_simulated_process_alp_admin_jp_pengarah_pepu_informed(): void
    {
        Notification::fake();

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $adminJp = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $pegawaiJp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);
        $pengarah = $this->userWithRole(RoleName::PELULUS->value);
        $pepu = $this->userWithRole(RoleName::PENGURUSAN->value);

        // ── 1) ALP hantar permohonan ─────────────────────────────
        $app = $this->submitted($alp, $year, '4200.00');
        $this->assertSame(ApplicationStatus::SUBMITTED, $app->status);

        $this->actingAs($owner)
            ->get(route('applications.show', $app))
            ->assertOk();

        // ── 2) Admin JP — senarai semakan (SUBMITTED sahaja) ───────
        $this->actingAs($adminJp)
            ->get(route('reviews.secretariat'))
            ->assertOk()
            ->assertSee($app->application_number);

        $this->actingAs($pegawaiJp)
            ->get(route('reviews.secretariat'))
            ->assertOk()
            ->assertDontSee($app->application_number);

        // ── 3) Admin JP syor → giliran Pengarah ───────────────────
        $this->actingAs($adminJp)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => 'recommend',
                'comments' => 'Simulasi UAT — disyorkan Admin JP',
                'checklist' => $this->lengkapChecklist(),
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $app->refresh();
        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->status);

        Notification::assertSentTo(
            $pengarah,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'awaiting_peraku',
        );

        // ── 4) Pengarah — giliran kelulusan ────────────────────────
        $this->actingAs($pengarah)
            ->get(route('approvals.queue'))
            ->assertOk()
            ->assertSee($app->application_number);

        $this->actingAs($pengarah)
            ->get(route('approvals.show', $app))
            ->assertOk();

        $this->actingAs($pepu)
            ->get(route('approvals.queue'))
            ->assertForbidden();

        // ── 5) PEPU tidak boleh meluluskan (makluman sahaja) ───────
        $this->actingAs($pepu)
            ->post(route('approvals.approve', $app), ['comments' => 'Cuba lulus'])
            ->assertForbidden();

        $this->expectException(ApplicationException::class);
        app(ApprovalService::class)->approve($app, $pepu, null);

        // ── 6) Pengarah lulus → diluluskan + makluman ──────────────
        Notification::fake();

        $this->actingAs($pengarah)
            ->post(route('approvals.approve', $app), ['comments' => 'Simulasi UAT — lulus Pengarah JP'])
            ->assertRedirect();

        $app->refresh();
        $this->assertSame(ApplicationStatus::APPROVED, $app->status);

        Notification::assertSentTo(
            $pepu,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'approved_pepu_informed',
        );

        Notification::assertSentTo(
            $owner,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'approved',
        );

        // ── 7) PEPU lihat status dalam login (senarai diluluskan) ──
        $this->actingAs($pepu)
            ->get(route('applications.all', ['status' => 'diluluskan']))
            ->assertOk()
            ->assertSee($app->application_number);

        // ── 8) Timeline dalaman vs paparan ALP ─────────────────────
        $timeline = app(ApplicationTimelineService::class);
        $staff = collect($timeline->stages($app));
        $alpView = collect($timeline->stages($app, forAlpView: true));

        $this->assertTrue($staff->firstWhere('key', 'peraku')['done']);
        $this->assertTrue($staff->firstWhere('key', 'pepu')['done']);
        $this->assertNull($alpView->firstWhere('key', 'peraku'));
        $this->assertNull($alpView->firstWhere('key', 'pepu'));
        $this->assertSame(
            'Diluluskan — menunggu baucar',
            $alpView->firstWhere('key', 'jp_review')['status_label'],
        );
    }

    public function test_simulated_rejection_at_pengarah_after_admin_syor(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $adminJp = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $pengarah = $this->userWithRole(RoleName::PELULUS->value);

        $app = $this->submitted($alp, $year, '3100.00');

        $this->actingAs($adminJp)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => 'recommend',
                'checklist' => $this->lengkapChecklist(),
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $this->actingAs($pengarah)
            ->post(route('approvals.reject', $app->fresh()), [
                'comments' => 'Simulasi UAT — ditolak Pengarah JP',
            ])
            ->assertRedirect();

        $this->assertSame(ApplicationStatus::REJECTED, $app->fresh()->status);
        $this->assertDatabaseHas('application_approvals', [
            'application_id' => $app->id,
            'decision' => 'rejected',
        ]);
    }
}
