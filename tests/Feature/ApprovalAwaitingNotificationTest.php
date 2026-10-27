<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Notifications\ApplicationWorkflowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/** Notifikasi kelulusan Pengarah JP. */
class ApprovalAwaitingNotificationTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_admin_jp_recommend_notifies_pengarah(): void
    {
        Notification::fake();

        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $pengarah = $this->userWithRole(RoleName::PELULUS->value);

        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));

        Notification::assertSentTo(
            $pengarah,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'awaiting_peraku'
                && $n->application->is($app),
        );
    }
}
