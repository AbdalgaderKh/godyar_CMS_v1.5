<?php
/**
 * Frontend theme loader (single include).
 * Reads selected theme from storage/config/site_theme.php and prints a <link>.
 *
 * Usage in frontend <head> (after app-core/app-rtl):
 *   <?php include __DIR__ . '/../includes/theme_loader.php'; ?>
 */
$PUBLIC_ROOT = realpath(__DIR__ . '/..');
if ($PUBLIC_ROOT === false) $PUBLIC_ROOT = dirname(__DIR__);

$CFG_FILE = $PUBLIC_ROOT . '/storage/config/site_theme.php';

$theme = null;
if (is_file($CFG_FILE)) {
  $v = include $CFG_FILE;
  if (is_string($v) && $v !== '') $theme = $v;
}

// Always include theme-core as the base (tokens)
echo '<link rel="stylesheet" href="/assets/css/themes/theme-core.css">', "\n";

// Then include ONLY the selected theme; fallback to theme-default
$themePath = $theme ?: 'assets/css/themes/theme-default.css';
$themePath = ltrim(str_replace(['\\', '//'], ['/', '/'], $themePath), '/');

// add cache-busting via filemtime when possible
$file = $PUBLIC_ROOT . '/' . $themePath;
$ver = (is_file($file) ? (string)@filemtime($file) : (string)time());

echo '<link rel="stylesheet" href="/' . htmlspecialchars($themePath, ENT_QUOTES, 'UTF-8') . '?v=' . htmlspecialchars($ver, ENT_QUOTES, 'UTF-8') . '">', "\n";
