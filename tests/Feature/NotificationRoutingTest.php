<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class NotificationRoutingTest extends TestCase
{
    use BuildsWorkflow, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
        Notification::fake();
    }

    /** Simpan satu notifikasi database sebenar untuk $user, kemudian pulangkan idnya. */
    private function notify(User $user, Application $application, string $event): string
    {
        $notification = new ApplicationWorkflowNotification($application, $event, 'Ujian');
        $user->notifications()->create([
            'id' => $id = (string) \Illuminate\Support\Str::uuid(),
            'type' => ApplicationWorkflowNotification::class,
            'data' => $notification->toArray($user),
            'read_at' => null,
        ]);

        return $id;
    }

    private function click(User $user, string $notificationId): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->get(route('notifications.read', $notificationId));
    }

    public function test_submitted_notification_opens_admin_jp_review_form(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');

        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $id = $this->notify($admin, $app, 'submitted');

        $this->click($admin, $id)
            ->assertRedirect(route('reviews.show', [$app, 'secretariat']));
    }

    public function test_awaiting_pegawai_jp_notification_opens_review_form(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->afterAdminJpReview($this->submitted($alp, $year, '2500.00'));

        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);
        $id = $this->notify($jp, $app, 'awaiting_pegawai_jp');

        $this->click($jp, $id)
            ->assertRedirect(route('reviews.show', [$app, 'secretariat']));
    }

    public function test_awaiting_peraku_notification_opens_approval_form(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $peraku = $this->userWithRole(RoleName::PELULUS->value);
        $id = $this->notify($peraku, $app, 'awaiting_peraku');

        $this->click($peraku, $id)
            ->assertRedirect(route('approvals.show', $app));
    }

    public function test_revision_required_notification_opens_wizard_for_owner(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');
        $app->update(['status' => ApplicationStatus::REVISION_REQUIRED]);

        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $id = $this->notify($owner, $app->fresh(), 'revision_required');

        $this->click($owner, $id)
            ->assertRedirect(route('applications.wizard.maklumat', $app));
    }

    public function test_report_card_reminder_opens_report_tab(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $id = $this->notify($owner, $app, 'report_card_reminder');

        $this->click($owner, $id)
            ->assertRedirect(route('applications.show', [$app, 'tab' => 'report']));
    }

    public function test_payment_notification_opens_payment_list_for_finance_only(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $kerani = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);
        $this->click($kerani, $this->notify($kerani, $app, 'payment_paid'))
            ->assertRedirect(route('payments.index', ['cari' => $app->application_number]));

        // Pemilik ALP tiada payments.view — kekal pada halaman permohonan.
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $this->click($owner, $this->notify($owner, $app, 'payment_paid'))
            ->assertRedirect(route('applications.show', $app));
    }

    public function test_voucher_notification_opens_report_tab_for_alp(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $this->click($owner, $this->notify($owner, $app, 'payment_voucher'))
            ->assertRedirect(route('applications.show', [$app, 'tab' => 'report']));
    }

    public function test_awaiting_payment_notification_opens_payment_list(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));

        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);
        $id = $this->notify($kewangan, $app, 'awaiting_payment');

        $this->click($kewangan, $id)
            ->assertRedirect(route('payments.index', ['cari' => $app->application_number]));

        $this->actingAs($kewangan)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Sediakan baucar');
    }

    public function test_stale_review_notification_falls_back_to_application_page(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        // Sudah melepasi peringkat semakan JP.
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $id = $this->notify($admin, $app, 'submitted');

        $this->click($admin, $id)
            ->assertRedirect(route('applications.show', $app));
    }

    public function test_clicking_marks_notification_as_read(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');

        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $id = $this->notify($admin, $app, 'submitted');

        $this->click($admin, $id);

        $this->assertNotNull($admin->fresh()->notifications()->whereKey($id)->first()->read_at);
    }

    public function test_index_shows_action_label_per_notification_type(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->submitted($alp, $year, '2500.00');

        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $this->notify($admin, $app, 'submitted');

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Buka borang semakan');
    }

    public function test_report_card_submitted_notification_opens_review_form(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, '2500.00')));
        $app->forceFill([
            'report_card_submitted_at' => now(),
            'report_card_status' => \App\Enums\ReportCardStatus::AWAITING_ADMIN_JP,
        ])->save();

        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $id = $this->notify($admin, $app, 'report_card_submitted');

        $this->click($admin, $id)
            ->assertRedirect(route('report-cards.review.show', $app));

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Semak laporan aktiviti');
    }
}
