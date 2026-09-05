<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ApplicationType;
use App\Enums\FinancialYearStatus;
use App\Enums\RoleName;
use App\Models\Alp;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\BudgetTransaction;
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

class ApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Alp $alp;
    private FinancialYear $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DocumentRequirementSeeder::class);
        SystemSetting::set(UrsContributionPolicy::KEY_ENABLED, false);
        $this->year = FinancialYear::factory()->active()->create(['year' => 2026]);
        $this->alp = Alp::factory()->create();
        $this->user = User::factory()->create(['alp_id' => $this->alp->id])->assignRole(RoleName::ALP->value);
    }

    private function allocate(string $amount): void
    {
        app(BudgetService::class)->allocate($this->alp, $this->year, $amount, 'REF');
    }

    private function makeDraft(string $amount, bool $withDocs = true, bool $withItems = true): Application
    {
        $app = Application::factory()->create([
            'alp_id' => $this->alp->id,
            'financial_year_id' => $this->year->id,
            'application_type' => ApplicationType::CSR,
            'status' => ApplicationStatus::DRAFT,
        ]);

        if ($withItems) {
            $app->budgetItems()->create([
                'description' => 'Item', 'quantity' => 1, 'unit_cost' => $amount, 'total' => $amount, 'sort_order' => 1,
            ]);
            $app->recalculateRequestedAmount();
        }

        if ($withDocs) {
            foreach (DocumentRequirement::requiredFor(ApplicationType::CSR) as $t) {
                ApplicationDocument::factory()->type($t)->create(['application_id' => $app->id]);
            }
        }

        return $app;
    }

    private function submit(Application $app)
    {
        return $this->actingAs($this->user)->post(route('applications.submit', $app));
    }

    public function test_valid_draft_can_be_submitted(): void
    {
        $this->allocate('500000.00');
        $app = $this->makeDraft('85000.00');

        $this->submit($app)->assertRedirect(route('applications.show', $app));

        $fresh = $app->fresh();
        $this->assertSame(ApplicationStatus::SUBMITTED, $fresh->status);
        $this->assertNotNull($fresh->submitted_at);
        $this->assertSame('85000.00', $fresh->requested_amount); // snapshot tepat
    }

    public function test_submission_creates_no_budget_ledger_transaction(): void
    {
        $this->allocate('500000.00');
        $before = BudgetTransaction::count(); // termasuk INITIAL_ALLOCATION
        $app = $this->makeDraft('85000.00');

        $this->submit($app);

        // Tiada transaksi ledger BAHARU akibat penghantaran.
        $this->assertSame($before, BudgetTransaction::count());
    }

    public function test_submission_creates_status_history(): void
    {
        $this->allocate('500000.00');
        $app = $this->makeDraft('85000.00');

        $this->submit($app);

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $app->id,
            'from_status' => ApplicationStatus::DRAFT->value,
            'to_status' => ApplicationStatus::SUBMITTED->value,
        ]);
    }

    public function test_submission_creates_audit_record(): void
    {
        $this->allocate('500000.00');
        $app = $this->makeDraft('85000.00');

        $this->submit($app);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'APPLICATION_SUBMITTED',
            'entity_id' => $app->id,
        ]);
    }

    public function test_zero_amount_is_rejected(): void
    {
        $this->allocate('500000.00');
        $app = $this->makeDraft('0.00', withItems: false); // tiada item → 0

        $this->submit($app)->assertSessionHas('error');
        $this->assertSame(ApplicationStatus::DRAFT, $app->fresh()->status);
    }

    public function test_missing_required_document_is_rejected(): void
    {
        $this->allocate('500000.00');
        $app = $this->makeDraft('85000.00', withDocs: false);

        $this->submit($app)->assertSessionHas('error');
        $this->assertSame(ApplicationStatus::DRAFT, $app->fresh()->status);
    }

    public function test_closed_financial_year_is_rejected(): void
    {
        $this->allocate('500000.00');
        $app = $this->makeDraft('85000.00');
        $this->year->update(['status' => FinancialYearStatus::CLOSED, 'is_active' => false]);

        $this->submit($app)->assertSessionHas('error');
        $this->assertSame(ApplicationStatus::DRAFT, $app->fresh()->status);
    }

    public function test_insufficient_budget_is_rejected(): void
    {
        $this->allocate('10000.00');
        $app = $this->makeDraft('50000.00');

        $this->submit($app)->assertSessionHas('error');
        $this->assertSame(ApplicationStatus::DRAFT, $app->fresh()->status);
    }

    public function test_pending_requests_reduce_available_capacity(): void
    {
        $this->allocate('100000.00');

        // Permohonan pending sedia ada RM70,000.
        Application::factory()->submitted()->create([
            'alp_id' => $this->alp->id,
            'financial_year_id' => $this->year->id,
            'requested_amount' => '70000.00',
        ]);

        // Permohonan baharu RM40,000 → hanya RM30,000 tersedia → ditolak.
        $app = $this->makeDraft('40000.00');
        $this->submit($app)->assertSessionHas('error');
        $this->assertSame(ApplicationStatus::DRAFT, $app->fresh()->status);
    }
}
