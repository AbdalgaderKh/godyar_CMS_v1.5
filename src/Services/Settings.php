<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Settings Service (thin wrapper)
 *
 * الهدف: توحيد قراءة الإعدادات في مكان واحد تدريجيًا.
 * هذا الملف لا يغير سلوك النظام الحالي، بل يوفر واجهة موحدة للاستخدام مستقبلًا.
 */
final class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        if (function_exists('settings_get')) {
            return settings_get($key, $default);
        }

        // Fallback: environment
        $env = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($env !== false && $env !== null && $env !== '') {
            return $env;
        }

        return $default;
    }
}
