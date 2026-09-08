<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use App\Services\Application\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/** NT-003 / NT-004 — notifikasi Peraku & PEPU. */
class ApprovalAwaitingNotificationTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_secretariat_recommend_notifies_peraku(): void
    {
        Notification::fake();

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $peraku = $this->userWithRole(RoleName::PELULUS->value);

        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        Notification::assertSentTo(
            $peraku,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'awaiting_peraku'
                && $n->application->is($app),
        );
    }

    public function test_mid_level_approve_notifies_pepu(): void
    {
        Notification::fake();

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $peraku = $this->userWithRole(RoleName::PELULUS->value);
        $pepu = $this->userWithRole(RoleName::PENGURUSAN->value);

        // Peraku kemudian PEPU untuk semua jumlah.
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        Notification::assertSentTo(
            $peraku,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'awaiting_peraku',
        );

        Notification::fake(); // reset sebelum aras 1

        app(ApprovalService::class)->approve($app, $peraku, 'Peraku OK');

        Notification::assertSentTo(
            $pepu,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'awaiting_pepu',
        );
    }
}
