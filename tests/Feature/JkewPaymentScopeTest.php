<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/** SEC-007 — skop senarai pembayaran JKEW. */
class JkewPaymentScopeTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_jkew_scoped_user_sees_only_sent_to_jkew_rows(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $pending = $this->toPendingApproval($this->submitted($alp, $year, '2000.00'));
        app(\App\Services\Application\ApprovalService::class)
            ->approve($pending, $this->userWithRole(RoleName::PELULUS->value), null);
        $pending = $pending->fresh();
        $this->assertSame(ApplicationPaymentStatus::PENDING_PAYMENT, $pending->payment_status);

        $sent = $this->toPendingApproval($this->submitted($alp, $year, '2100.00'));
        app(\App\Services\Application\ApprovalService::class)
            ->approve($sent, $this->userWithRole(RoleName::PELULUS->value), null);
        $sent = $sent->fresh();
        $sent->update([
            'payment_status' => ApplicationPaymentStatus::SENT_TO_JKEW,
            'sent_to_jkew_at' => now(),
        ]);

        Permission::findOrCreate('payments.view');
        Permission::findOrCreate('payments.jkew_scope');

        $jkew = User::factory()->create();
        $jkew->givePermissionTo(['payments.view', 'payments.jkew_scope']);

        $this->actingAs($jkew)
            ->get(route('payments.index', ['status' => 'open']))
            ->assertOk()
            ->assertSee($sent->application_number)
            ->assertDontSee($pending->application_number);
    }

    public function test_finance_manager_still_sees_pending_payment_queue(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $pending = $this->toPendingApproval($this->submitted($alp, $year, '2200.00'));
        app(\App\Services\Application\ApprovalService::class)
            ->approve($pending, $this->userWithRole(RoleName::PELULUS->value), null);
        $pending = $pending->fresh();

        $finance = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($finance)
            ->get(route('payments.index', ['status' => 'open']))
            ->assertOk()
            ->assertSee($pending->application_number);
    }

    public function test_jkew_role_sees_only_sent_rows(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $pending = $this->toPendingApproval($this->submitted($alp, $year, '2000.00'));
        app(\App\Services\Application\ApprovalService::class)
            ->approve($pending, $this->userWithRole(RoleName::PELULUS->value), null);
        $pending = $pending->fresh();

        $sent = $this->toPendingApproval($this->submitted($alp, $year, '2100.00'));
        app(\App\Services\Application\ApprovalService::class)
            ->approve($sent, $this->userWithRole(RoleName::PELULUS->value), null);
        $sent = $sent->fresh();
        $sent->update([
            'payment_status' => ApplicationPaymentStatus::SENT_TO_JKEW,
            'sent_to_jkew_at' => now(),
        ]);

        $jkew = User::factory()->create()->assignRole(RoleName::PEGAWAI_JKEW->value);

        $this->actingAs($jkew)
            ->get(route('payments.index', ['status' => 'open']))
            ->assertOk()
            ->assertSee($sent->application_number)
            ->assertDontSee($pending->application_number);
    }
}
