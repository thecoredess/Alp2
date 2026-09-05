<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::cached();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value],
        );
        Cache::forget('system_settings');
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }

    /** @return array<string, string|null> */
    public static function cached(): array
    {
        return Cache::remember('system_settings', 60, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }
}
