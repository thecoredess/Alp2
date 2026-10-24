<?php

namespace Tests\Unit;

use App\Support\KertasKerjaGuide;
use PHPUnit\Framework\TestCase;

class KertasKerjaGuideTest extends TestCase
{
    public function test_minimum_sections_and_themes_are_defined(): void
    {
        $this->assertCount(6, KertasKerjaGuide::themes());
        $this->assertCount(4, KertasKerjaGuide::minimumSections());
        $this->assertStringContainsString('pecahan perbelanjaan', strtolower(KertasKerjaGuide::notesForAssociationGuide()));
    }
}
