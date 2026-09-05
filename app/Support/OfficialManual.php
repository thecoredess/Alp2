<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

/** M11 — fail manual PDF rasmi (UR-M11 / UR-012). */
final class OfficialManual
{
    public const KEY_PATH = 'manual.official_pdf_path';

    public const STORAGE_PATH = 'manuals/official.pdf';

    public static function path(): ?string
    {
        $path = SystemSetting::get(self::KEY_PATH);

        return filled($path) ? (string) $path : null;
    }

    public static function exists(): bool
    {
        $path = self::path();

        return $path !== null && Storage::disk('local')->exists($path);
    }

    public static function storeUploaded(\Illuminate\Http\UploadedFile $file): string
    {
        Storage::disk('local')->putFileAs('manuals', $file, 'official.pdf');
        SystemSetting::set(self::KEY_PATH, self::STORAGE_PATH);

        return self::STORAGE_PATH;
    }

    public static function clear(): void
    {
        $path = self::path();
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
        SystemSetting::set(self::KEY_PATH, '');
    }
}
