<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\DocumentType;
use App\Enums\ReportCardStatus;
use App\Enums\ReviewDecision;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use App\Services\Application\ApplicationReportCardService;
use App\Services\Application\ReportCardReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
        Storage::fake('local');
    }

    private function approvedApp()
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));
        $app = $this->fullyApprove($app);

        return [$app, $alp];
    }

    private function prepareVoucher($app, ?string $date = null): void
    {
        $app->forceFill([
            'payment_status' => ApplicationPaymentStatus::VOUCHER_PREPARED,
            'payment_voucher_no' => 'BV-'.$app->id,
            'payment_supplier_no' => 'SUP-1',
            'payment_voucher_date' => $date ?? now()->toDateString(),
            'payment_updated_at' => now(),
        ])->save();
    }

    private function uploadReportCardDraft(User $owner, $app, DocumentType $type = DocumentType::REPORT_CARD): void
    {
        $this->actingAs($owner)
            ->post(route('applications.report-card.store', $app), [
                'document_type' => $type->value,
                'file' => UploadedFile::fake()->create('report.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    private function submitReportCard(User $owner, $app): void
    {
        $this->actingAs($owner)
            ->post(route('applications.report-card.submit', $app))
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_report_card_url_redirects_to_application_report_tab(): void
    {
        [$app, $alp] = $this->approvedApp();
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->actingAs($owner)
            ->get(route('applications.report-card', $app))
            ->assertRedirect(route('applications.show', [$app, 'tab' => 'report']));
    }

    public function test_alp_cannot_upload_report_card_before_voucher(): void
    {
        [$app, $alp] = $this->approvedApp();
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->actingAs($owner)
            ->post(route('applications.report-card.store', $app), [
                'document_type' => DocumentType::REPORT_CARD->value,
                'file' => UploadedFile::fake()->create('report.pdf', 120, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertNull($app->fresh()->report_card_submitted_at);
    }

    public function test_alp_can_upload_report_card_draft_after_voucher_prepared(): void
    {
        Notification::fake();
        [$app, $alp] = $this->approvedApp();
        $this->prepareVoucher($app);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);

        $this->actingAs($owner)
            ->post(route('applications.report-card.store', $app), [
                'document_type' => DocumentType::REPORT_CARD->value,
                'file' => UploadedFile::fake()->create('report.pdf', 120, 'application/pdf'),
                'report_card_remarks' => 'Aktiviti selesai',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $app->refresh();
        $this->assertNull($app->report_card_submitted_at);
        $this->assertSame(ReportCardStatus::DRAFT, $app->report_card_status);
        $this->assertTrue(app(ApplicationReportCardService::class)->hasDraft($app));
        $this->assertDatabaseHas('application_documents', [
            'application_id' => $app->id,
            'document_type' => DocumentType::REPORT_CARD->value,
        ]);

        Notification::assertNothingSentTo($admin);
    }

    public function test_alp_can_submit_report_card_draft_to_admin_jp(): void
    {
        Notification::fake();
        [$app, $alp] = $this->approvedApp();
        $this->prepareVoucher($app);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);

        $this->uploadReportCardDraft($owner, $app);
        $this->submitReportCard($owner, $app);

        $app->refresh();
        $this->assertNotNull($app->report_card_submitted_at);
        $this->assertSame(ReportCardStatus::AWAITING_ADMIN_JP, $app->report_card_status);
        $this->assertFalse(app(ApplicationReportCardService::class)->hasDraft($app));

        Notification::assertSentTo(
            $admin,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'report_card_submitted'
        );
    }

    public function test_alp_can_discard_report_card_draft(): void
    {
        [$app, $alp] = $this->approvedApp();
        $this->prepareVoucher($app);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->uploadReportCardDraft($owner, $app);

        $this->actingAs($owner)
            ->delete(route('applications.report-card.draft.destroy', $app))
            ->assertRedirect()
            ->assertSessionHas('status');

        $app->refresh();
        $this->assertNull($app->report_card_status);
        $this->assertNull($app->report_card_submitted_at);
        $this->assertDatabaseMissing('application_documents', [
            'application_id' => $app->id,
            'document_type' => DocumentType::REPORT_CARD->value,
        ]);
    }

    public function test_report_card_review_flow_admin_then_pegawai_jp(): void
    {
        Notification::fake();
        [$app, $alp] = $this->approvedApp();
        $this->prepareVoucher($app);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $pegawai = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);

        $this->uploadReportCardDraft($owner, $app, DocumentType::LAPORAN_AKTIVITI);
        $this->submitReportCard($owner, $app);

        app(ReportCardReviewService::class)->review(
            $app->fresh(),
            $admin,
            ReviewDecision::RECOMMEND,
            'Lengkap',
        );

        $app->refresh();
        $this->assertSame(ReportCardStatus::AWAITING_PEGAWAI_JP, $app->report_card_status);

        Notification::assertSentTo(
            $pegawai,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'report_card_awaiting_pegawai_jp'
        );

        app(ReportCardReviewService::class)->review(
            $app->fresh(),
            $pegawai,
            ReviewDecision::RECOMMEND,
            'Disahkan',
        );

        $app->refresh();
        $this->assertSame(ReportCardStatus::APPROVED, $app->report_card_status);
        $this->assertTrue(app(ApplicationReportCardService::class)->hasReportCard($app));

        Notification::assertSentTo(
            $owner,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'report_card_approved'
        );
    }

    public function test_return_clears_submitted_at_and_notifies_alp(): void
    {
        Notification::fake();
        [$app, $alp] = $this->approvedApp();
        $this->prepareVoucher($app);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);

        $this->uploadReportCardDraft($owner, $app);
        $this->submitReportCard($owner, $app);

        app(ReportCardReviewService::class)->review(
            $app->fresh(),
            $admin,
            ReviewDecision::RETURN_FOR_REVISION,
            'Dokumen tidak lengkap',
        );

        $app->refresh();
        $this->assertSame(ReportCardStatus::RETURNED, $app->report_card_status);
        $this->assertNull($app->report_card_submitted_at);

        Notification::assertSentTo(
            $owner,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'report_card_returned'
        );
    }

    public function test_reminder_notifies_alp_seven_days_before_due_date(): void
    {
        Notification::fake();
        [$app, $alp] = $this->approvedApp();
        $app->forceFill(['program_date' => '2026-03-15'])->save();
        $this->prepareVoucher($app);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->travelTo('2026-04-08');

        $sent = app(ApplicationReportCardService::class)->sendReminders();
        $this->assertSame(1, $sent);

        Notification::assertSentTo(
            $owner,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'report_card_reminder_upcoming'
        );

        $this->assertNotNull($app->fresh()->report_card_upcoming_reminder_sent_at);
    }

    public function test_reminder_notifies_alp_seven_days_after_due_date(): void
    {
        Notification::fake();
        [$app, $alp] = $this->approvedApp();
        $app->forceFill(['program_date' => '2026-03-15'])->save();
        $this->prepareVoucher($app);
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->travelTo('2026-04-22');

        $sent = app(ApplicationReportCardService::class)->sendReminders();
        $this->assertSame(1, $sent);

        Notification::assertSentTo(
            $owner,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'report_card_reminder_overdue'
        );

        $this->assertNotNull($app->fresh()->report_card_overdue_reminder_sent_at);
    }

    public function test_due_date_is_one_month_after_program_date(): void
    {
        [$app] = $this->approvedApp();
        $app->forceFill(['program_date' => '2026-03-15'])->save();

        $due = app(ApplicationReportCardService::class)->dueDate($app->fresh());

        $this->assertSame('2026-04-15', $due?->toDateString());
        $this->travelTo('2026-04-16');
        $this->assertTrue(app(ApplicationReportCardService::class)->isOverdue($app->fresh()));
    }

    public function test_no_due_date_without_program_date(): void
    {
        [$app] = $this->approvedApp();
        $app->forceFill(['program_date' => null])->save();
        $service = app(ApplicationReportCardService::class);

        $this->assertNull($service->dueDate($app));
        $this->assertFalse($service->isOverdue($app));
        $this->assertFalse($service->canUpload($app));
    }

    public function test_borang_penyaluran_is_printable(): void
    {
        [$app, $alp] = $this->approvedApp();
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->actingAs($owner)
            ->get(route('applications.borang', $app))
            ->assertOk()
            ->assertSee('Borang Penyaluran')
            ->assertSee($app->application_number)
            ->assertSee('F · Peruntukan')
            ->assertSee('K · Semakan Urusetia JP');
    }

    public function test_manual_page_is_reachable(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);
        $this->actingAs($user)->get(route('manual.show'))->assertOk()->assertSee('Manual Pengguna');
    }
}
