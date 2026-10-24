<?php

namespace Tests\Unit;

use App\Models\Alp;
use App\Models\Application;
use App\Models\FinancialYear;
use App\Services\Application\RecipientRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggestions_show_one_entry_for_many_applications_with_same_name(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();

        foreach (range(1, 5) as $i) {
            Application::factory()->create([
                'alp_id' => $alp->id,
                'financial_year_id' => $year->id,
                'recipient_name' => 'Persatuan Komuniti Sentul',
                'recipient_ros_number' => 'PPM-060-01-12345678',
                'recipient_bank_account' => '100'.$i,
            ]);
        }

        $items = app(RecipientRegistry::class)->suggestionsForAlp($alp->id);

        $this->assertCount(1, $items);
        $this->assertSame('Persatuan Komuniti Sentul', $items[0]['name']);
        $this->assertSame('1005', $items[0]['bank_account']);
    }

    public function test_suggestions_deduplicate_by_name_even_when_ros_differs(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Kebajikan',
            'recipient_ros_number' => 'PPM-060-01-11111111',
            'recipient_bank_account' => '1111',
        ]);

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Kebajikan',
            'recipient_ros_number' => 'PPM-060-01-22222222',
            'recipient_bank_account' => '2222',
        ]);

        $items = app(RecipientRegistry::class)->suggestionsForAlp($alp->id);

        $this->assertCount(1, $items);
        $this->assertSame('2222', $items[0]['bank_account']);
        $this->assertSame('PPM-060-01-22222222', $items[0]['ros_number']);
    }

    public function test_suggestions_treat_name_spacing_and_case_as_same(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan  Komuniti',
            'recipient_bank_account' => '1000',
        ]);

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'persatuan komuniti',
            'recipient_bank_account' => '2000',
        ]);

        $this->assertCount(1, app(RecipientRegistry::class)->suggestionsForAlp($alp->id));
    }

    public function test_ros_mismatch_detects_different_number_for_same_name(): void
    {
        $alp = Alp::factory()->create();
        $year = FinancialYear::factory()->active()->create();
        $registry = app(RecipientRegistry::class);

        Application::factory()->create([
            'alp_id' => $alp->id,
            'financial_year_id' => $year->id,
            'recipient_name' => 'Persatuan Sentul',
            'recipient_ros_number' => 'PPM-060-01-11111111',
        ]);

        $this->assertSame('PPM-060-01-11111111', $registry->recordedRosForPersatuanName($alp->id, 'Persatuan Sentul'));

        $result = $registry->rosMismatchForPersatuan($alp->id, 'Persatuan Sentul', 'PPM-060-01-22222222');
        $this->assertTrue($result['mismatch']);
        $this->assertSame('PPM-060-01-11111111', $result['recorded_ros']);

        $ok = $registry->rosMismatchForPersatuan($alp->id, 'Persatuan Sentul', 'ppm-060-01-11111111');
        $this->assertFalse($ok['mismatch']);
    }
}
