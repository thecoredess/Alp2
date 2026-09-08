<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentRequirement;
use App\Models\FinancialYear;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Budget\BudgetService;
use App\Support\UrsContributionPolicy;
use Database\Seeders\DocumentRequirementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Membuktikan dua penghantaran tidak boleh sama-sama melebihi baki yang sama.
 *
 * Keselamatan serentak sebenar dijamin oleh lockForUpdate pada baris allocation
 * dalam ApplicationSubmissionService (penghantaran diserikan per ALP×tahun).
 * Ujian ini membuktikan semakan Pending Request menolak penghantaran kedua.
 */
class ApplicationConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_submissions_cannot_both_oversubscribe_same_budget(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DocumentRequirementSeeder::class);
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, false);

        $year = FinancialYear::factory()->active()->create();
        $alp = Alp::factory()->create();
        $user = User::factory()->create(['alp_id' => $alp->id])->assignRole(RoleName::ALP->value);

        // Ledger Available = RM100.
        app(BudgetService::class)->allocate($alp, $year, '100.00', 'REF');

        $makeDraft = function () use ($alp, $year) {
            $app = Application::factory()->create([
                'alp_id' => $alp->id,
                'financial_year_id' => $year->id,
                'application_type' => ApplicationType::SUMBANGAN,
                'status' => ApplicationStatus::DRAFT,
                'requested_amount' => '70.00',
                'purpose' => 'Tujuan ujian',
                'recipient_name' => 'Persatuan Ujian',
                'recipient_bank_account' => '1234567890',
            ]);
            foreach (DocumentRequirement::requiredFor() as $t) {
                ApplicationDocument::factory()->type($t)->create(['application_id' => $app->id]);
            }

            return $app;
        };

        $a = $makeDraft();
        $b = $makeDraft();

        $this->actingAs($user)->post(route('applications.submit', $a));
        $this->actingAs($user)->post(route('applications.submit', $b));

        $submittedCount = Application::whereIn('id', [$a->id, $b->id])
            ->where('status', ApplicationStatus::SUBMITTED->value)
            ->count();

        // Paling banyak satu boleh berjaya (70 + 70 > 100).
        $this->assertSame(1, $submittedCount);
    }
}
