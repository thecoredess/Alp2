<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class PaymentAlertCommandTest extends TestCase
{
    use BuildsWorkflow, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    private function approvedApplication(string $amount = '2500.00'): Application
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        return $this->fullyApprove($this->toPendingApproval($this->submitted($alp, $year, $amount)));
    }

    private function alertCount(User $user): int
    {
        return $user->notifications()
            ->get()
            ->filter(fn ($n) => ($n->data['event'] ?? null) === 'awaiting_payment')
            ->count();
    }

    public function test_command_alerts_finance_user_for_outstanding_payments(): void
    {
        $application = $this->approvedApplication();
        // Buang alert automatik daripada kelulusan supaya kes "terlepas" diuji.
        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->assertSame(0, $this->alertCount($kewangan));

        $this->artisan('urs:alert-pembayaran')
            ->expectsOutputToContain($application->application_number)
            ->assertSuccessful();

        $this->assertSame(1, $this->alertCount($kewangan));
    }

    public function test_command_does_not_resend_for_already_alerted_application(): void
    {
        $this->approvedApplication();
        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->artisan('urs:alert-pembayaran')->assertSuccessful();
        $this->artisan('urs:alert-pembayaran')
            ->expectsOutputToContain('Alert dihantar untuk 0 permohonan.')
            ->assertSuccessful();

        $this->assertSame(1, $this->alertCount($kewangan));
    }

    public function test_paksa_option_resends_alert(): void
    {
        $this->approvedApplication();
        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->artisan('urs:alert-pembayaran')->assertSuccessful();
        $this->artisan('urs:alert-pembayaran', ['--paksa' => true])->assertSuccessful();

        $this->assertSame(2, $this->alertCount($kewangan));
    }

    public function test_kering_option_sends_nothing(): void
    {
        $this->approvedApplication();
        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->artisan('urs:alert-pembayaran', ['--kering' => true])
            ->expectsOutputToContain('Kering: 1 permohonan akan dialert.')
            ->assertSuccessful();

        $this->assertSame(0, $this->alertCount($kewangan));
    }

    public function test_paid_application_is_not_alerted(): void
    {
        $application = $this->approvedApplication();
        $application->forceFill(['payment_status' => ApplicationPaymentStatus::PAID])->save();

        $kewangan = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);

        $this->artisan('urs:alert-pembayaran')
            ->expectsOutputToContain('Tiada permohonan menunggu proses bayaran.')
            ->assertSuccessful();

        $this->assertSame(0, $this->alertCount($kewangan));
    }

    public function test_alert_does_not_reach_users_without_payments_manage(): void
    {
        $this->approvedApplication();

        $jkew = $this->userWithRole(RoleName::PEGAWAI_JKEW->value);  // payments.view sahaja
        $pengurusan = $this->userWithRole(RoleName::PENGURUSAN->value);

        $this->artisan('urs:alert-pembayaran')->assertSuccessful();

        $this->assertSame(0, $this->alertCount($jkew));
        $this->assertSame(0, $this->alertCount($pengurusan));
    }
}
