<?php

namespace Tests\Unit;

use App\Support\RoleUserManual;
use App\Support\RoleUserManualScreenshots;
use Tests\TestCase;

class RoleUserManualScreenshotsTest extends TestCase
{
    public function test_alp_may_not_access_pengarah_figure_name(): void
    {
        $alpManual = RoleUserManual::alp();
        $this->assertFalse(
            RoleUserManualScreenshots::isAllowedForManual('kelulusan-pengarah.png', $alpManual),
        );
        $this->assertTrue(
            RoleUserManualScreenshots::isAllowedForManual('menu-alp.png', $alpManual),
        );
    }

    public function test_pengarah_figure_list_includes_kelulusan(): void
    {
        $names = array_column(RoleUserManualScreenshots::forRoleManual(RoleUserManual::pengarahJp()), 'file');
        $this->assertContains('kelulusan-pengarah.png', $names);
    }
}
