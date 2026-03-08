<?php
/**
 * Godyar News Platform V3 — Version & Branding
 */
if (!defined('GODYAR_CMS_VERSION')) {
    define('GODYAR_CMS_VERSION', 'v3.0.0-newsroom-core');
}
if (!defined('GODYAR_CMS_COPYRIGHT')) {
    define('GODYAR_CMS_COPYRIGHT', 'Godyar News Platform');
}
if (!function_exists('gdy_cms_badge')) {
    function gdy_cms_badge(): string
    {
        return trim((string)GODYAR_CMS_COPYRIGHT) . ' ' . trim((string)GODYAR_CMS_VERSION);
    }
}
