<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Services\Application\ApplicationTimelineService;
use App\Services\Application\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class ApplicationTimelineTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    private function timeline(): ApplicationTimelineService
    {
        return app(ApplicationTimelineService::class);
    }

    private function approvals(): ApprovalService
    {
        return app(ApprovalService::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_peraku_approval_leaves_pepu_pending_on_timeline(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $stages = collect($this->timeline()->stages($app->fresh()));
        $pepu = $stages->firstWhere('key', 'pepu');
        $peraku = $stages->firstWhere('key', 'peraku');

        $this->assertSame('Pengesyoran TP/Pengarah JP', $peraku['label']);
        $this->assertTrue($peraku['done']);
        $this->assertFalse($pepu['done']);
        $this->assertFalse($pepu['skipped']);
        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
    }

    public function test_multi_level_shows_pepu_pending_after_peraku(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $stages = collect($this->timeline()->stages($app->fresh()));
        $pepu = $stages->firstWhere('key', 'pepu');

        $this->assertNotNull($pepu);
        $this->assertFalse($pepu['done']);
        $this->assertTrue($stages->firstWhere('key', 'peraku')['done']);
        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->fresh()->status);
    }

    public function test_multi_level_marks_pepu_done_after_second_approval(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '5000.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
        $this->approvals()->approve($app->fresh(), $this->userWithRole(RoleName::PENGURUSAN->value), null);

        $stages = collect($this->timeline()->stages($app->fresh()));

        $this->assertTrue($stages->firstWhere('key', 'peraku')['done']);
        $this->assertTrue($stages->firstWhere('key', 'pepu')['done']);
        $this->assertSame(ApplicationStatus::APPROVED, $app->fresh()->status);
    }

    public function test_alp_view_hides_internal_approval_stages(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $this->approvals()->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);

        $staffStages = collect($this->timeline()->stages($app->fresh()));
        $alpStages = collect($this->timeline()->stages($app->fresh(), forAlpView: true));

        $this->assertNotNull($staffStages->firstWhere('key', 'peraku'));
        $this->assertNotNull($staffStages->firstWhere('key', 'pepu'));
        $this->assertNull($alpStages->firstWhere('key', 'peraku'));
        $this->assertNull($alpStages->firstWhere('key', 'pepu'));
        $this->assertSame(['submitted', 'jp_review', 'voucher', 'report'], $alpStages->pluck('key')->all());

        $jpStage = $alpStages->firstWhere('key', 'jp_review');
        $this->assertFalse($jpStage['done']);
        $this->assertSame('Dalam proses kelulusan', $jpStage['status_label']);

        $this->approvals()->approve($app->fresh(), $this->userWithRole(RoleName::PENGURUSAN->value), null);

        $alpStages = collect($this->timeline()->stages($app->fresh(), forAlpView: true));
        $this->assertNull($alpStages->firstWhere('key', 'peraku'));
        $this->assertNull($alpStages->firstWhere('key', 'pepu'));
        $this->assertFalse($alpStages->firstWhere('key', 'voucher')['done']);
        $this->assertSame('Diluluskan — menunggu baucar', $alpStages->firstWhere('key', 'jp_review')['status_label']);
    }

    public function test_alp_jp_stage_completes_when_voucher_prepared(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $app->update([
            'payment_status' => \App\Enums\ApplicationPaymentStatus::VOUCHER_PREPARED,
            'payment_updated_at' => now(),
        ]);

        $jpStage = collect($this->timeline()->stages($app->fresh(), forAlpView: true))
            ->firstWhere('key', 'jp_review');

        $this->assertTrue($jpStage['done']);
        $this->assertNull($jpStage['status_label']);
        $this->assertTrue(collect($this->timeline()->stages($app->fresh(), forAlpView: true))
            ->firstWhere('key', 'voucher')['done']);

        $voucherStage = collect($this->timeline()->stages($app->fresh(), forAlpView: true))
            ->firstWhere('key', 'voucher');
        $this->assertSame(ApplicationTimelineService::ALP_VOUCHER_PAYMENT_HINT, $voucherStage['hint']);
    }

    public function test_report_stage_skipped_when_not_approved(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');

        $report = collect($this->timeline()->stages($app))->firstWhere('key', 'report');

        $this->assertTrue($report['skipped']);
        $this->assertFalse($report['done']);
    }

    public function test_report_stage_waiting_upload_after_voucher(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $app->update([
            'program_date' => '2026-08-05',
            'payment_status' => \App\Enums\ApplicationPaymentStatus::VOUCHER_PREPARED,
            'payment_voucher_date' => '2026-08-05',
            'payment_updated_at' => now(),
        ]);

        $report = collect($this->timeline()->stages($app->fresh()))->firstWhere('key', 'report');

        $this->assertFalse($report['skipped']);
        $this->assertFalse($report['done']);
        $this->assertSame('Menunggu muat naik', $report['status_label']);
        $this->assertStringContainsString('Tarikh akhir: 05/09/2026', $report['hint']);
    }

    public function test_report_stage_in_review_when_submitted(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $app->update([
            'payment_status' => \App\Enums\ApplicationPaymentStatus::VOUCHER_PREPARED,
            'payment_voucher_date' => now()->toDateString(),
            'payment_updated_at' => now(),
            'report_card_submitted_at' => now(),
            'report_card_status' => \App\Enums\ReportCardStatus::AWAITING_ADMIN_JP,
        ]);

        $staffReport = collect($this->timeline()->stages($app->fresh()))->firstWhere('key', 'report');
        $alpReport = collect($this->timeline()->stages($app->fresh(), forAlpView: true))->firstWhere('key', 'report');

        $this->assertSame('Dalam semakan JP', $staffReport['status_label']);
        $this->assertSame('Dalam semakan JP', $alpReport['status_label']);
        $this->assertStringContainsString('Dihantar', $staffReport['hint']);
    }

    public function test_alp_timeline_shows_rejection_with_comments_before_voucher(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $this->approvals()->reject(
            $app,
            $this->userWithRole(RoleName::PELULUS->value),
            'Tidak memenuhi syarat sumbangan.',
        );

        $app = $app->fresh();
        $this->assertSame(ApplicationStatus::REJECTED, $app->status);

        $alpStages = collect($this->timeline()->stages($app, forAlpView: true));
        $keys = $alpStages->pluck('key')->all();

        $this->assertSame(
            ['submitted', 'jp_review', 'rejected', 'voucher', 'report'],
            $keys,
        );

        $rejected = $alpStages->firstWhere('key', 'rejected');
        $this->assertSame('rejected', $rejected['variant']);
        $this->assertTrue($rejected['done']);
        $this->assertSame('Ditolak', $rejected['status_label']);
        $this->assertSame('Tidak memenuhi syarat sumbangan.', $rejected['hint']);
        $this->assertNotNull($rejected['at']);

        $voucher = $alpStages->firstWhere('key', 'voucher');
        $this->assertTrue($voucher['skipped']);
        $this->assertFalse($voucher['done']);

        $report = $alpStages->firstWhere('key', 'report');
        $this->assertTrue($report['skipped']);

        $jpStage = $alpStages->firstWhere('key', 'jp_review');
        $this->assertTrue($jpStage['done']);
        $this->assertNull($jpStage['status_label']);
    }

    public function test_report_stage_done_when_approved_by_pegawai_jp(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $approvedAt = now()->subDay();
        $app->update([
            'payment_status' => \App\Enums\ApplicationPaymentStatus::VOUCHER_PREPARED,
            'payment_updated_at' => now()->subWeek(),
            'report_card_submitted_at' => now()->subDays(2),
            'report_card_status' => \App\Enums\ReportCardStatus::APPROVED,
        ]);

        $app->reportCardReviews()->create([
            'reviewer_id' => $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value)->id,
            'stage' => 'pegawai_jp',
            'decision' => \App\Enums\ReviewDecision::RECOMMEND,
            'comments' => 'OK',
            'reviewed_at' => $approvedAt,
            'created_at' => $approvedAt,
        ]);

        $report = collect($this->timeline()->stages($app->fresh()))->firstWhere('key', 'report');

        $this->assertTrue($report['done']);
        $this->assertSame('Disahkan Pegawai JP', $report['hint']);
        $this->assertSame($approvedAt->format('Y-m-d H:i'), $report['at']->format('Y-m-d H:i'));
    }
}
