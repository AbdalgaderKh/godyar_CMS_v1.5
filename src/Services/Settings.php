<?php
namespace App\Services;

use PDO;
use Throwable;

/**
 * Settings Service (v1.6)
 *
 * هدفه: توحيد قراءة/كتابة الإعدادات في مكان واحد، مع توافق كامل مع v1.5.
 * - يدعم مفاتيح dot-notation مثل: social.facebook
 * - ويحوّلها إلى الشكل القديم: social_facebook
 * - يعتمد على settings_get/settings_set إن وُجدت (الأسرع/الأضمن)
 * - ويحتوي fallback مباشر على قاعدة البيانات إن لم تتوفر الدوال.
 */
final class Settings
{
    /** Convert dot-notation to legacy underscore keys (when needed). */
    public static function normalizeKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') return $key;
        return str_contains($key, '.') ? str_replace('.', '_', $key) : $key;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $rawKey  = trim($key);
        $normKey = self::normalizeKey($rawKey);

        // Primary: existing global helpers
        if (function_exists('settings_get')) {
            // Many existing code paths store keys using dot notation (e.g. social.facebook).
            // Others use legacy underscore (e.g. social_facebook). We try BOTH safely.
            $v = settings_get($rawKey, null);
            if ($v !== null && $v !== '') return $v;
            if ($normKey !== $rawKey) {
                $v2 = settings_get($normKey, null);
                if ($v2 !== null && $v2 !== '') return $v2;
            }
            return $default;
        }

        // Secondary: DB fallback (settings table)
        $val = self::dbGet($rawKey);
        if ($val === null && $normKey !== $rawKey) {
            $val = self::dbGet($normKey);
        }
        if ($val !== null) return $val;

        // Fallback: environment
        $env = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($env !== false && $env !== null && $env !== '') {
            return $env;
        }

        return $default;
    }

    public static function set(string $key, mixed $value): bool
    {
        $rawKey  = trim($key);
        $normKey = self::normalizeKey($rawKey);

        if (function_exists('settings_set')) {
            // Prefer writing in dot-notation if caller used it; also update legacy underscore for compatibility.
            $ok = (bool) settings_set($rawKey, $value);
            if ($normKey !== $rawKey) {
                $ok2 = (bool) settings_set($normKey, $value);
                return $ok && $ok2;
            }
            return $ok;
        }

        // DB fallback: keep both keys in sync
        $ok = self::dbSet($rawKey, $value);
        if ($normKey !== $rawKey) {
            $ok2 = self::dbSet($normKey, $value);
            return $ok && $ok2;
        }
        return $ok;
    }

    private static function dbGet(string $key): ?string
    {
        try {
            $pdo = self::pdoOrNull();
            if (!$pdo) return null;

            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return null;
            return (string)($row['setting_value'] ?? '');
        } catch (Throwable) {
            return null;
        }
    }

    private static function dbSet(string $key, mixed $value): bool
    {
        try {
            $pdo = self::pdoOrNull();
            if (!$pdo) return false;

            $value = is_scalar($value) || $value === null ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Upsert (MySQL)
            $stmt = $pdo->prepare(
                "INSERT INTO settings (setting_key, setting_value, updated_at)
                 VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()"
            );
            return $stmt->execute([$key, $value]);
        } catch (Throwable) {
            return false;
        }
    }

    private static function pdoOrNull(): ?PDO
    {
        try {
            if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
                return $GLOBALS['pdo'];
            }
            if (class_exists('\\Godyar\\DB') && method_exists('\\Godyar\\DB', 'pdoOrNull')) {
                /** @var ?PDO $pdo */
                $pdo = \Godyar\DB::pdoOrNull();
                return $pdo instanceof PDO ? $pdo : null;
            }
        } catch (Throwable) {
            // ignore
        }
        return null;
    }
}
