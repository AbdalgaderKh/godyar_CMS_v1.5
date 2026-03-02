<?php

declare(strict_types = 1);

/**
 * Apply security headers in a hosting-portable way .
 *
 * Controls (env):
 *-GDY_SECURITY_HEADERS = 0 disable all
 *-GDY_CSP = 0 disable CSP only
 *-GDY_HSTS = 0 disable HSTS
 *-GDY_HSTS_MAXAGE = 31536000 customize max-age seconds
 */
function gdy_apply_security_headers(): void
{
    if (headers_sent()) { return; }

    // Generate a per-request CSP nonce (used by templates via GDY_CSP_NONCE).
    // Defining the nonce early also fixes cases where templates output nonce="".
    if (!defined('GDY_CSP_NONCE')) {
        try {
            $nonce = base64_encode(random_bytes(16));
        } catch (Throwable $e) {
            // Fallback (still reasonably unique per-request)
            $nonce = base64_encode((string)microtime(true) . '|' . (string)mt_rand());
        }
        define('GDY_CSP_NONCE', $nonce);
    }

    $enabled = getenv('GDY_SECURITY_HEADERS');
    if ($enabled !== false && trim((string)$enabled) === '0') {
        return;
    }

    // Baseline headers
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // Legacy (harmless; can be ignored by modern browsers)
    header('X-XSS-Protection: 0');

    // Cross-origin isolation defaults (safe for most CMS pages)
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');

    // HSTS (only when HTTPS)
    $hstsEnabled = getenv('GDY_HSTS');
    if (!($hstsEnabled !== false && trim((string)$hstsEnabled) === '0')) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
 || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
        if ($https) {
            $maxAge = getenv('GDY_HSTS_MAXAGE');
            $maxAge = ($maxAge !== false && ctype_digit((string)$maxAge)) ? (int)$maxAge : 31536000;
            header('Strict-Transport-Security: max-age=' . $maxAge . '; includeSubDomains');
        }
    }

    // Conservative CSP (may be adjusted per theme/plugins) .
    // Disable via GDY_CSP = 0 if any integration breaks .
    $cspEnabled = getenv('GDY_CSP');
    if ($cspEnabled !== false && trim((string)$cspEnabled) === '0') {
        return;
    }

    // If a CSP header is already present, do not emit a second one.
    foreach (headers_list() as $h) {
        if (stripos($h, 'Content-Security-Policy:') === 0) {
            return;
        }
    }

    // Keep it permissive enough for legacy inline styles/scripts .
    $reportOnly = getenv('GDY_CSP_REPORT_ONLY');
    $useReportOnly = ($reportOnly !== false && trim((string)$reportOnly) === '1');
    $reportUri = getenv('GDY_CSP_REPORT_URI');
    if ($reportUri === false || trim((string)$reportUri) === '') {
        $reportUri = '/csp-report.php';
    }

    // Nonce-based CSP (safer than 'unsafe-inline').
    // Allow https: for external assets (fonts/CDNs) while keeping a strict default.
    $nonce = defined('GDY_CSP_NONCE') ? GDY_CSP_NONCE : '';
    $csp = "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; " .
           "img-src 'self' data: https:; font-src 'self' data: https:; " .
           "style-src 'self' 'unsafe-inline' https:; style-src-attr 'unsafe-inline'; " .
           "script-src 'self' 'nonce-{$nonce}' https:; connect-src 'self' https:; " .
           "form-action 'self';";

    // Add CSP reporting (safe default).
    if (is_string($reportUri) && trim($reportUri) !== '') {
        $csp .= ' report-uri ' . trim($reportUri) . ';';
    }

    if ($useReportOnly) {
        header('Content-Security-Policy-Report-Only: ' . $csp);
    } else {
        header('Content-Security-Policy: ' . $csp);
    }
}
