<?php
/**
 * language_prefix_router.php
 * - Parse /{lang}/... prefix (ar|en|fr) and expose:
 *   - $_GET['lang'] (if not already set)
 *   - GDY_LANG_PREFIX (string like 'ar' or '')
 *   - GDY_PATH_INFO (URI without language prefix, starting with '/')
 *
 * Must be BOM-free and must NOT output anything.
 */
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
$path = '/' . ltrim($path, '/');

$lang = null;
$rest = $path;

// Match '/ar' or '/ar/' or '/ar/anything'
if (preg_match('#^/(ar|en|fr)(?:/|$)#i', $path, $m)) {
    $lang = strtolower($m[1]);
    $rest = substr($path, 1 + strlen($m[1])); // remove '/{lang}'
    $rest = $rest === '' ? '/' : $rest;
    if ($rest[0] !== '/') $rest = '/' . $rest;
}

// Expose
if (!empty($lang) && empty($_GET['lang'])) {
    $_GET['lang'] = $lang;
}
define('GDY_LANG_PREFIX', $lang ?: '');
define('GDY_PATH_INFO', $rest);
