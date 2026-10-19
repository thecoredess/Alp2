<?php

namespace Tests\Unit;

use App\Enums\ProgramCategory;
use PHPUnit\Framework\TestCase;

class ProgramCategoryTest extends TestCase
{
    public function test_each_category_has_guide_description(): void
    {
        foreach (ProgramCategory::cases() as $case) {
            $this->assertNotSame('', trim($case->description()));
        }

        $this->assertCount(count(ProgramCategory::cases()), ProgramCategory::guideEntries());
    }
}
