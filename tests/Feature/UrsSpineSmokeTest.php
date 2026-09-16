<?php

namespace Tests\Feature;

use App\Enums\ApplicationPaymentStatus;
use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\JkewCrosscheckStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\BudgetTransaction;
use App\Models\Project;
use App\Models\Recipient;
use App\Services\Application\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

/**
 * Asap UAT — spine URS v1.2 hujung-ke-hujung (tanpa modul projek legacy).
 */
class UrsSpineSmokeTest extends TestCase
{
    use RefreshDatabase, BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
        Storage::fake('local');
    }

    public function test_urs_spine_submit_review_approve_pay_report_card(): void
    {
        $alp = Alp::factory()->create();
        $year = $this->makeYear();
        $this->allocate($alp, $year, '500000.00');

        $owner = $this->userWithRole(RoleName::ALP->value, $alp);
        $jp = $this->userWithRole(RoleName::PEGAWAI_URUSSETIA->value);
        $peraku = $this->userWithRole(RoleName::PELULUS->value);
        $pengurusan = $this->userWithRole(RoleName::PENGURUSAN->value);
        $kerani = $this->userWithRole(RoleName::PEGAWAI_KEWANGAN->value);
        $jkew = $this->userWithRole(RoleName::PEGAWAI_JKEW->value);

        // 1) Hantar
        $app = $this->submitted($alp, $year, '2500.00');
        $this->assertSame(ApplicationStatus::SUBMITTED, $app->status);

        // 2) Admin JP → Pegawai JP
        $admin = $this->userWithRole(RoleName::SYSTEM_ADMIN->value);
        $this->actingAs($admin)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => 'recommend',
                'checklist' => $this->lengkapChecklist(),
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $app->refresh();
        $this->assertSame(ApplicationStatus::UNDER_SECRETARIAT_REVIEW, $app->status);

        $this->actingAs($jp)
            ->post(route('reviews.store', [$app, 'secretariat']), [
                'decision' => 'recommend',
                'comments' => 'Syor kepada Pengarah JP',
            ])
            ->assertRedirect(route('reviews.secretariat'));

        $app->refresh();
        $this->assertSame(ApplicationStatus::PENDING_APPROVAL, $app->status);
        $this->assertNotNull($app->recipient_id);
        $this->assertTrue(Recipient::whereKey($app->recipient_id)->exists());

        // 3) Peraku + PEPU lulus → COMMITMENT, tiada projek
        app(ApprovalService::class)->approve($app, $peraku, 'Lulus UAT smoke');
        app(ApprovalService::class)->approve($app->fresh(), $pengurusan, 'Lulus PEPU UAT smoke');
        $app->refresh();
        $this->assertSame(ApplicationStatus::APPROVED, $app->status);
        $this->assertSame(ApplicationPaymentStatus::PENDING_PAYMENT, $app->payment_status);
        $this->assertTrue(
            BudgetTransaction::query()
                ->where('application_id', $app->id)
                ->where('type', \App\Enums\BudgetTransactionType::COMMITMENT->value)
                ->exists()
        );
        $this->assertSame(0, Project::query()->where('application_id', $app->id)->count());

        // 4) Cetak surat & borang
        $this->actingAs($owner)->get(route('applications.letter', $app))->assertOk();
        $this->actingAs($owner)->get(route('applications.borang', $app))->assertOk()->assertSee('F · Peruntukan');

        // 5) Kerani hantar ke JKEW
        $this->actingAs($kerani)
            ->put(route('payments.update', $app), [
                'payment_status' => ApplicationPaymentStatus::SENT_TO_JKEW->value,
                'payment_voucher_no' => 'BV-SMOKE-1',
                'sent_to_jkew_at' => now()->format('Y-m-d H:i:s'),
                'jkew_crosscheck_status' => JkewCrosscheckStatus::CLEAR->value,
                'jkew_crosscheck_remarks' => 'OK smoke',
            ])
            ->assertRedirect();

        $app->refresh();
        $this->assertSame(ApplicationPaymentStatus::SENT_TO_JKEW, $app->payment_status);

        // 6) JKEW skop — nampak rekod ini
        $this->actingAs($jkew)
            ->get(route('payments.index', ['status' => 'open']))
            ->assertOk()
            ->assertSee($app->application_number);

        // 7) Report card — draf kemudian hantar
        $this->actingAs($owner)
            ->post(route('applications.report-card.store', $app), [
                'document_type' => DocumentType::REPORT_CARD->value,
                'file' => UploadedFile::fake()->create('report-smoke.pdf', 80, 'application/pdf'),
                'report_card_remarks' => 'Selesai smoke',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->actingAs($owner)
            ->post(route('applications.report-card.submit', $app))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertNotNull($app->fresh()->report_card_submitted_at);

        // 8) Halaman sokongan aktif
        $this->actingAs($jp)->get(route('recipients.index'))->assertOk();
        $this->actingAs($owner)->get(route('manual.show'))->assertOk();
        $this->actingAs($kerani)->get(route('dashboard'))->assertOk();
    }
}
