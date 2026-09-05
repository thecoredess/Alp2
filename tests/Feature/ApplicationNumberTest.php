<?php

namespace Tests\Feature;

use App\Enums\ApplicationType;
use App\Services\Application\ApplicationNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationNumberTest extends TestCase
{
    use RefreshDatabase;

    private function generate(ApplicationType $type, int $year): string
    {
        return DB::transaction(fn () => app(ApplicationNumberGenerator::class)->next($type, $year));
    }

    public function test_generates_sequential_unique_numbers(): void
    {
        $a = $this->generate(ApplicationType::CSR, 2026);
        $b = $this->generate(ApplicationType::CSR, 2026);
        $c = $this->generate(ApplicationType::CSR, 2026);

        $this->assertSame('ALP/CSR/2026/0001', $a);
        $this->assertSame('ALP/CSR/2026/0002', $b);
        $this->assertSame('ALP/CSR/2026/0003', $c);
    }

    public function test_separate_sequences_per_type_and_year(): void
    {
        $this->assertSame('ALP/CSR/2026/0001', $this->generate(ApplicationType::CSR, 2026));
        $this->assertSame('ALP/DEV/2026/0001', $this->generate(ApplicationType::DEVELOPMENT, 2026));
        $this->assertSame('ALP/CSR/2027/0001', $this->generate(ApplicationType::CSR, 2027));
        $this->assertSame('ALP/CSR/2026/0002', $this->generate(ApplicationType::CSR, 2026));
    }

    public function test_numbers_do_not_reuse_after_deletion_not_count_based(): void
    {
        // Bukti bukan COUNT(*)+1: pembilang berterusan walaupun tiada baris permohonan.
        $this->generate(ApplicationType::CSR, 2026); // 0001
        $this->generate(ApplicationType::CSR, 2026); // 0002

        // Tiada permohonan langsung dicipta; pembilang kekal maju.
        $this->assertSame('ALP/CSR/2026/0003', $this->generate(ApplicationType::CSR, 2026));
        $this->assertDatabaseHas('application_sequences', ['year' => 2026, 'type' => 'CSR', 'last_number' => 3]);
    }
}
