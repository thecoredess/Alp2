<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use App\Notifications\ApplicationWorkflowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class ApplicationPaymentTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function approvedApplication(string $amount = '2500.00')
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, $amount));
        $approvals = app(\App\Services\Application\ApprovalService::class);
        $approvals->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
        $approvals->approve($app->fresh(), $this->userWithRole(RoleName::PENGURUSAN->value), null);

        return $app->fresh();
    }

    public function test_approval_sets_pending_payment_status(): void
    {
        $app = $this->approvedApplication();

        $this->assertSame(ApplicationStatus::APPROVED, $app->status);
        $this->assertSame(ApplicationPaymentStatus::PENDING_PAYMENT, $app->payment_status);
    }

    public function test_finance_can_mark_voucher_and_paid(): void
    {
        Notification::fake();
        $app = $this->approvedApplication();
        $finance = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($finance)
            ->put(route('payments.update', $app), [
                'payment_status' => ApplicationPaymentStatus::VOUCHER_PREPARED->value,
                'payment_supplier_no' => 'SUP-2026-001',
                'payment_voucher_no' => 'BV-2026-001',
                'payment_voucher_date' => '2026-09-07',
                'payment_remarks' => 'Catatan ujian',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $app->refresh();
        $this->assertSame(ApplicationPaymentStatus::VOUCHER_PREPARED, $app->payment_status);
        $this->assertSame('BV-2026-001', $app->payment_voucher_no);
        $this->assertSame('SUP-2026-001', $app->payment_supplier_no);

        $this->actingAs($finance)
            ->put(route('payments.update', $app), [
                'payment_status' => ApplicationPaymentStatus::PAID->value,
                'payment_supplier_no' => 'SUP-2026-001',
                'payment_voucher_no' => 'BV-2026-001',
                'payment_voucher_date' => '2026-09-07',
                'paid_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $app->refresh();
        $this->assertSame(ApplicationPaymentStatus::PAID, $app->payment_status);
        $this->assertNotNull($app->paid_at);

        Notification::assertSentTo(
            $app->alp->users,
            ApplicationWorkflowNotification::class,
            fn (ApplicationWorkflowNotification $n) => in_array($n->event, ['payment_voucher', 'payment_paid'], true)
        );
    }

    public function test_paid_requires_voucher_number(): void
    {
        $app = $this->approvedApplication();
        $finance = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($finance)
            ->put(route('payments.update', $app), [
                'payment_status' => ApplicationPaymentStatus::PAID->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(ApplicationPaymentStatus::PENDING_PAYMENT, $app->fresh()->payment_status);
    }

    public function test_alp_cannot_manage_payments(): void
    {
        $app = $this->approvedApplication();
        $alpUser = User::factory()->create(['alp_id' => $app->alp_id])->assignRole(RoleName::ALP->value);

        $this->actingAs($alpUser)
            ->put(route('payments.update', $app), [
                'payment_status' => ApplicationPaymentStatus::PAID->value,
                'payment_voucher_no' => 'X',
            ])
            ->assertForbidden();
    }

    public function test_payments_queue_and_csv_export(): void
    {
        $app = $this->approvedApplication();
        $finance = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($finance)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee($app->application_number)
            ->assertSee('Pembayaran');

        $this->actingAs($finance)
            ->get(route('payments.export'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_payment_update_does_not_create_expenditure_ledger(): void
    {
        $app = $this->approvedApplication();
        $before = \App\Models\BudgetTransaction::count();
        $finance = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($finance)->put(route('payments.update', $app), [
            'payment_status' => ApplicationPaymentStatus::PAID->value,
            'payment_voucher_no' => 'BV-99',
        ]);

        $this->assertSame($before, \App\Models\BudgetTransaction::count());
    }
}
