<?php

namespace App\Support;

/**
 * Rajah tangkapan skrin manual pengguna (selari docs/manual-pengguna/build-manuals.mjs).
 */
final class RoleUserManualScreenshots
{
    private const MANUAL_ASSETS = 'docs/manual-pengguna/assets';

    private const FLOW_DIAGRAM = 'docs/ringkasan-eksekutif-alp/assets/flow-diagram.png';

    /**
     * @return list<array{file: string, caption: string, section?: string}>
     */
    public static function forRoleManual(RoleUserManual $manual): array
    {
        return match ($manual->key) {
            'alp' => self::alpFigures(),
            'admin_jp' => self::adminJpFigures(),
            'pengarah_jp' => self::pengarahJpFigures(),
            default => [],
        };
    }

    /** @return list<array{file: string, caption: string, section?: string}> */
    public static function alpFigures(): array
    {
        return [
            ['file' => 'menu-alp.png', 'caption' => 'Rajah 1 — Dashboard & menu (paparan sistem ALP)', 'section' => 'Log masuk & menu'],
            ['file' => 'permohonan-baharu.png', 'caption' => 'Rajah 2 — Permohonan Baharu (Langkah 1)', 'section' => 'Permohonan baharu'],
            ['file' => 'wizard-lampiran.png', 'caption' => 'Rajah 3 — Muat naik dokumen (Langkah 2)', 'section' => 'Permohonan baharu'],
            ['file' => 'flow-diagram.png', 'caption' => 'Rajah 4 — Aliran permohonan (perspektif ALP)', 'section' => 'Gambaran aliran'],
            ['file' => 'status-permohonan-alp.png', 'caption' => 'Rajah 5 — Butiran permohonan / timeline', 'section' => 'Selepas hantar'],
        ];
    }

    /** @return list<array{file: string, caption: string, section?: string}> */
    public static function adminJpFigures(): array
    {
        return [
            ['file' => 'menu-admin-jp.png', 'caption' => 'Rajah 1 — Dashboard & menu (Admin JP)', 'section' => 'Log masuk & menu'],
            ['file' => 'giliran-admin-jp.png', 'caption' => 'Rajah 2 — Giliran semakan Admin JP', 'section' => 'Giliran semakan'],
            ['file' => 'semakan-admin-jp.png', 'caption' => 'Rajah 3 — Skrin semakan Admin JP', 'section' => 'Skrin semakan'],
        ];
    }

    /** @return list<array{file: string, caption: string, section?: string}> */
    public static function pengarahJpFigures(): array
    {
        return [
            ['file' => 'menu-pengarah.png', 'caption' => 'Rajah 1 — Dashboard & menu (Pengarah JP)', 'section' => 'Log masuk & menu'],
            ['file' => 'giliran-pengarah.png', 'caption' => 'Rajah 2 — Giliran kelulusan', 'section' => 'Giliran kelulusan'],
            ['file' => 'kelulusan-pengarah.png', 'caption' => 'Rajah 3 — Skrin kelulusan Pengarah JP', 'section' => 'Skrin kelulusan'],
        ];
    }

    public static function isAllowedForManual(string $filename, RoleUserManual $manual): bool
    {
        $filename = basename($filename);
        foreach (self::forRoleManual($manual) as $figure) {
            if ($figure['file'] === $filename) {
                return true;
            }
        }

        return false;
    }

    public static function absolutePath(string $filename): ?string
    {
        $filename = basename($filename);
        if ($filename === 'flow-diagram.png') {
            $flow = base_path(self::FLOW_DIAGRAM);

            return is_file($flow) ? $flow : null;
        }

        $local = base_path(self::MANUAL_ASSETS.DIRECTORY_SEPARATOR.$filename);

        return is_file($local) ? $local : null;
    }

    /**
     * Rajah yang mempunyai fail imej pada pelayan (untuk paparan dalam sistem).
     *
     * @return list<array{file: string, caption: string, section?: string, url: string}>
     */
    public static function visibleFiguresFor(?RoleUserManual $manual): array
    {
        if ($manual === null) {
            return [];
        }

        $out = [];
        foreach (self::forRoleManual($manual) as $figure) {
            if (self::absolutePath($figure['file']) === null) {
                continue;
            }
            $out[] = array_merge($figure, [
                'url' => route('manual.screenshot', ['name' => $figure['file']]),
            ]);
        }

        return $out;
    }

    /**
     * @return array<string, array{file: string, caption: string, section?: string, url: string}>
     */
    public static function visibleFiguresKeyedFor(?RoleUserManual $manual): array
    {
        $keyed = [];
        foreach (self::visibleFiguresFor($manual) as $figure) {
            $keyed[$figure['file']] = $figure;
        }

        return $keyed;
    }
}
