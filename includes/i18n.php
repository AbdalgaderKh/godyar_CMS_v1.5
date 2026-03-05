<?php
/**
 * Frontend i18n (AR/EN/FR)
 * ------------------------
 *-Switch via ?lang = ar | en | fr
 *-Persist via session + cookie
 *-Dictionary source: /languages/{lang} .php (+ optional {lang}_patch .php)
 *
 * Provides:
 *-gdy_lang()
 *-gdy_is_rtl()
 *-__($key, $fallback = null)
 */

if (!function_exists('gdy_session_start')) {
    function gdy_session_start()
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            session_start();
        }
    }
}

if (!defined('GDY_SUPPORTED_LANGS')) {
    define('GDY_SUPPORTED_LANGS', ['ar', 'en', 'fr']);
}

if (!function_exists('gdy_set_cookie_rfc')) {
    function gdy_set_cookie_rfc($name, $value, $ttlSeconds, $path = '/', $secure = false, $httpOnly = true, $sameSite = 'Lax')
    {
        if (headers_sent()) return;
        $ttlSeconds = max(0, $ttlSeconds);
        $expires = gmdate('D, d M Y H:i:s', time() + $ttlSeconds) . ' GMT';
        $cookie = $name . '=' .rawurlencode($value)
            . '; Expires=' . $expires
            . '; Max-Age=' . $ttlSeconds
            . '; Path=' . $path
            . '; SameSite=' . $sameSite
            . ($secure ? '; Secure' : '')
            . ($httpOnly ? '; HttpOnly' : '');
        header('Set-Cookie: ' . $cookie, false);
    }
}

if (!function_exists('gdy_lang')) {
    function gdy_lang()
    {
        $supported = GDY_SUPPORTED_LANGS;

        $q = isset($_GET['lang']) ? strtolower(trim((string)$_GET['lang'])) : '';
        if ($q !== '' && in_array($q, $supported, true)) {
            gdy_session_start();
            $_SESSION['gdy_lang'] = $q;
            if (!headers_sent()) {
                $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
 || ((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
 || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
                $ttl = 60 * 60 * 24 * 90;
                gdy_set_cookie_rfc('gdy_lang', $q, $ttl, '/', $isSecure, true, 'Lax');
                gdy_set_cookie_rfc('lang', $q, $ttl, '/', $isSecure, true, 'Lax');
            }
            return $q;
        }

        gdy_session_start();
        $s = isset($_SESSION['gdy_lang']) ? strtolower(trim((string)$_SESSION['gdy_lang'])) : '';
        if ($s !== '' && in_array($s, $supported, true)) {
            return $s;
        }

        $c = isset($_COOKIE['gdy_lang']) ? strtolower(trim((string)$_COOKIE['gdy_lang'])) : '';
        if ($c !== '' && in_array($c, $supported, true)) {
            $_SESSION['gdy_lang'] = $c;
            return $c;
        }

        $_SESSION['gdy_lang'] = 'ar';
        return 'ar';
    }
}

if (!function_exists('gdy_is_rtl')) {
    function gdy_is_rtl()
    {
        return gdy_lang() === 'ar';
    }
}

if (!function_exists('gdy_i18n_safe_file')) {
    function gdy_i18n_safe_file($file, $baseDir): ?string
    {
        $base = rtrim((string)realpath($baseDir), '/');
        if ($base === '') return null;

        $real = realpath($file);
        if ($real === false) return null;
        $real = (string)$real;

        if (strpos($real, $base .DIRECTORY_SEPARATOR) !== 0) return null;
        return $real;
    }
}

if (!function_exists('gdy_locale_dict')) {
    function gdy_locale_dict()
    {
        static $cache = [];
        $lang = gdy_lang();
        if (isset($cache[$lang])) return $cache[$lang];

        $root = defined('ROOT_PATH') ? (string)ROOT_PATH : dirname(__DIR__);
        $langDir = rtrim((string)(realpath($root . '/languages') ?: ($root . '/languages')), '/');

        $dict = [];
        $file = $langDir . DIRECTORY_SEPARATOR . $lang . '.php';
        $safe = gdy_i18n_safe_file($file, $langDir);
        if ($safe && is_file($safe)) {
            $tmp = require $safe;
            if (is_array($tmp)) $dict = $tmp;
        }

        $patch = $langDir . DIRECTORY_SEPARATOR . $lang . '_patch.php';
        $safePatch = gdy_i18n_safe_file($patch, $langDir);
        if ($safePatch && is_file($safePatch)) {
            $tmp2 = require $safePatch;
            if (is_array($tmp2)) $dict = array_merge($dict, $tmp2);
        }

        $cache[$lang] = $dict;
        return $dict;
    }
}

if (!function_exists('__')) {
    /**
     * Translation helper .
     * Backward compatible with legacy calls:
     *-__('KEY')
     *-__('KEY', 'fallback')
     *-__('KEY', ['name' => 'X'], 'fallback')
     */
    function __($key, $varsOrFallback = null, $fallback = null)
    {
        $key = trim((string)$key);
        if ($key === '') return '';

        $vars = [];
        $fb = null;

        if (is_array($varsOrFallback)) {
            $vars = $varsOrFallback;
            $fb = is_string($fallback) ? $fallback : null;
        } elseif (is_string($varsOrFallback) && $varsOrFallback !== '') {
            $fb = $varsOrFallback;
        } else {
            $fb = is_string($fallback) ? $fallback : null;
        }

        $dict = gdy_locale_dict();
        $out = null;
        if (array_key_exists($key, $dict)) {
            $v = $dict[$key];
            $out = is_string($v) ? $v : (string)$v;
        }
        if ($out === null || $out === '') {
            $out = $fb ?? $key;
        }

        // Simple placeholder replacement: {name}
        if ($vars) {
            foreach ($vars as $k => $v) {
                $ph = '{' . (string)$k . '}';
                $out = str_replace($ph, (string)$v, $out);
            }
        }

        return $out;
    }
}

// ----------------------------------------------------------------------------
// Database-backed translations (PRO)
// ----------------------------------------------------------------------------

if (!function_exists('gdy_i18n_pdo')) {
    function gdy_i18n_pdo() {
        if (function_exists('gdy_pdo_safe')) {
            try { return gdy_pdo_safe(); } catch (Exception $e) { return null; }
        }
        return null;
    }
}

if (!function_exists('gdy_i18n_db_string')) {
    function gdy_i18n_db_string($key, $lang) {
        $pdo = gdy_i18n_pdo();
        if (!$pdo) return null;
        try {
            $st = $pdo->prepare("SELECT v FROM i18n_strings WHERE lang = ? AND k = ? LIMIT 1");
            $st->execute(array($lang, $key));
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['v'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('gdy_i18n_db_field')) {
    function gdy_i18n_db_field($scope, $itemId, $lang, $field) {
        $pdo = gdy_i18n_pdo();
        if (!$pdo) return null;
        try {
            $st = $pdo->prepare("SELECT value FROM i18n_fields WHERE scope = ? AND item_id = ? AND lang = ? AND field = ? LIMIT 1");
            $st->execute(array($scope, (int)$itemId, $lang, $field));
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['value'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('gdy_tr')) {
    /**
     * Translate a content field (news/category/page/etc.)
     * Example: gdy_tr('news', $news['id'], 'title', $news['title'])
     */
    function gdy_tr($scope, $itemId, $field, $fallback = '') {
        $lang = function_exists('gdy_lang') ? gdy_lang() : 'ar';
        $v = gdy_i18n_db_field($scope, $itemId, $lang, $field);
        if ($v !== null && $v !== '') return $v;
        return $fallback;
    }
}



if (!function_exists('gdy_t')) {
    /**
     * UI string translate (DB-first, then dictionary __()).
     */
    function gdy_t($key, $varsOrFallback = null, $fallback = null) {
        $lang = function_exists('gdy_lang') ? gdy_lang() : 'ar';
        $db = gdy_i18n_db_string($key, $lang);
        if ($db !== null && $db !== '') {
            $out = (string)$db;
            $vars = array();
            $fb = null;

            if (is_array($varsOrFallback)) { $vars = $varsOrFallback; $fb = $fallback; }
            else { $fb = $varsOrFallback; }

            if ($vars) {
                foreach ($vars as $k => $v) {
                    $out = str_replace('{' . (string)$k . '}', (string)$v, $out);
                }
            }
            return $out;
        }
        if (function_exists('__')) return __($key, $varsOrFallback, $fallback);
        return is_string($fallback) ? $fallback : $key;
    }
}

