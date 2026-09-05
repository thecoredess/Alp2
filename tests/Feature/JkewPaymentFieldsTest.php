<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\JkewCrosscheckStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class JkewPaymentFieldsTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_finance_can_record_sent_to_jkew_and_crosscheck(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');
        $app = $this->toPendingApproval($this->submitted($alp, $year, '2500.00'));
        app(\App\Services\Application\ApprovalService::class)
            ->approve($app, $this->userWithRole(RoleName::PELULUS->value), null);
        $app = $app->fresh();

        $finance = User::factory()->create()->assignRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->actingAs($finance)
            ->put(route('payments.update', $app), [
                'payment_status' => ApplicationPaymentStatus::SENT_TO_JKEW->value,
                'payment_voucher_no' => 'BV-JKEW-1',
                'sent_to_jkew_at' => now()->format('Y-m-d H:i:s'),
                'jkew_crosscheck_status' => JkewCrosscheckStatus::CLEAR->value,
                'jkew_crosscheck_remarks' => 'Tiada rekod JPKKB',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $app->refresh();
        $this->assertSame(ApplicationPaymentStatus::SENT_TO_JKEW, $app->payment_status);
        $this->assertNotNull($app->sent_to_jkew_at);
        $this->assertSame(JkewCrosscheckStatus::CLEAR, $app->jkew_crosscheck_status);
        $this->assertSame('Tiada rekod JPKKB', $app->jkew_crosscheck_remarks);
    }
}
