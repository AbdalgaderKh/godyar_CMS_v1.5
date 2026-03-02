<?php
/**
 * Theme utilities (frontend + admin compatible).
 *
 * Goal: make theme selection predictable and consistent across:
 * - DB settings table
 * - storage/config/*.php generated config files
 * - legacy keys (front_theme / frontend_theme / site_theme / front_preset)
 *
 * Priority (highest -> lowest):
 * 1) DB settings: frontend_theme, front_theme, site_theme
 * 2) Generated config file: storage/config/site_theme.php
 * 3) Default: assets/css/themes/theme-blue.css
 */

if (!function_exists('gdy_norm_theme_path')) {
  /**
   * Normalize theme value to a safe css path under assets/css/themes/.
   * Accepts:
   * - "assets/css/themes/theme-green.css"
   * - "/assets/css/themes/theme-green.css"
   * - "theme-green" or "green" (preset slug)
   */
  function gdy_norm_theme_path($value) {
    $v = trim((string)$value);
    if ($v === '') return null;

    // If it's a preset slug like "green" or "theme-green"
    if (preg_match('~^(theme-)?([a-z0-9_-]+)$~i', $v, $m) && stripos($v, '.css') === false && stripos($v, 'assets/') === false) {
      $slug = strtolower($m[2]);
      return "assets/css/themes/theme-{$slug}.css";
    }

    // Full/relative css path
    $v = str_replace('\\', '/', $v);

    // Strip leading domain if someone stored a full URL
    if (preg_match('~^https?://[^/]+/(.*)$~i', $v, $mm)) {
      $v = $mm[1];
    }

    $v = ltrim($v, '/');

    // Allow only themes folder
    if (!preg_match('~^assets/css/themes/theme-[a-z0-9_-]+\.css$~i', $v)) {
      return null;
    }

    return $v;
  }
}

if (!function_exists('gdy_get_setting_any')) {
  /**
   * Try to read a setting from DB using site_settings_get() if available.
   */
  function gdy_get_setting_any(array $keys) {
    if (!function_exists('site_settings_get')) return null;
    foreach ($keys as $k) {
      try {
        // Newer core expects: site_settings_get($pdo, $key, $default)
        global $pdo;
        if (isset($pdo) && $pdo) {
          $val = site_settings_get($pdo, $k, null);
        } else {
          // Backward compatibility for older 2-arg helper
          $val = site_settings_get($k, null);
        }
      } catch (Throwable $e) {
        $val = null;
      }
      if ($val !== null && $val !== '') return $val;
    }
    return null;
  }
}

if (!function_exists('gdy_site_theme_path')) {
  /**
   * Returns normalized theme css path (relative to web root).
   * Example: "assets/css/themes/theme-green.css"
   */
  function gdy_site_theme_path() {
    // 1) DB keys (new + legacy)
    $dbVal = gdy_get_setting_any(['frontend_theme', 'front_theme', 'site_theme']);
    $norm = gdy_norm_theme_path($dbVal);
    if ($norm) return $norm;

    // 1b) preset key
    $preset = gdy_get_setting_any(['front_preset']);
    $norm = gdy_norm_theme_path($preset);
    if ($norm) return $norm;

    // 2) Generated config file (legacy)
    $cfg = __DIR__ . '/../storage/config/site_theme.php';
    if (is_file($cfg)) {
      $cfgVal = @include $cfg;
      $norm = gdy_norm_theme_path($cfgVal);
      if ($norm) return $norm;
    }

    // 3) Default
    return 'assets/css/themes/theme-blue.css';
  }
}

if (!function_exists('gdy_theme_meta')) {
  /**
   * Returns theme meta: ['path' => ..., 'key' => 'theme-green', 'slug' => 'green']
   */
  function gdy_theme_meta() {
    $path = gdy_site_theme_path();

    // Extract slug from path
    $slug = 'blue';
    if (preg_match('~theme-([a-z0-9_-]+)\.css$~i', $path, $m)) {
      $slug = strtolower($m[1]);
    }

    return ['path' => $path, 'key' => 'theme-' . $slug, 'slug' => $slug];
  }
}
