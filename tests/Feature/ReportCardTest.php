<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use App\Services\Application\ApplicationReportCardService;
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

    public function test_alp_can_upload_report_card_after_approval(): void
    {
        [$app, $alp] = $this->approvedApp();
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);

        $this->actingAs($owner)
            ->post(route('applications.report-card.store', $app), [
                'document_type' => DocumentType::REPORT_CARD->value,
                'file' => UploadedFile::fake()->create('report.pdf', 120, 'application/pdf'),
                'report_card_remarks' => 'Aktiviti selesai',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $app->refresh();
        $this->assertNotNull($app->report_card_submitted_at);
        $this->assertDatabaseHas('application_documents', [
            'application_id' => $app->id,
            'document_type' => DocumentType::REPORT_CARD->value,
        ]);
    }

    public function test_reminder_command_notifies_overdue_report_cards(): void
    {
        Notification::fake();
        [$app, $alp] = $this->approvedApp();
        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $app->statusHistories()
            ->where('to_status', \App\Enums\ApplicationStatus::APPROVED->value)
            ->update(['created_at' => now()->subMonths(2)]);

        $sent = app(ApplicationReportCardService::class)->sendReminders(onlyOverdue: true);
        $this->assertSame(1, $sent);

        Notification::assertSentTo(
            $owner,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => $n->event === 'report_card_reminder'
        );
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
            ->assertSee('K · Semakan Pegawai JP');
    }

    public function test_manual_page_is_reachable(): void
    {
        $user = User::factory()->create()->assignRole(RoleName::ALP->value);
        $this->actingAs($user)->get(route('manual.show'))->assertOk()->assertSee('Manual Pengguna');
    }
}
