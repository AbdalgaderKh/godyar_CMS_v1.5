<?php
/**
 * v3.8.2 compatibility helpers for frontend/views/news_report.php
 * Fixes:
 * - Undefined variable $updatedAt
 * - strtotime(null) deprecated
 * - Undefined variable $canonical
 */

if (!function_exists('gdy_safe_updated_at')) {
    function gdy_safe_updated_at($news) {
        if (is_array($news)) {
            if (!empty($news['updated_at'])) return (string)$news['updated_at'];
            if (!empty($news['published_at'])) return (string)$news['published_at'];
            if (!empty($news['created_at'])) return (string)$news['created_at'];
        }
        return '';
    }
}

if (!function_exists('gdy_safe_canonical')) {
    function gdy_safe_canonical($canonical, $fallbackUrl) {
        $canonical = is_string($canonical) ? trim($canonical) : '';
        $fallbackUrl = is_string($fallbackUrl) ? trim($fallbackUrl) : '';
        return $canonical !== '' ? $canonical : $fallbackUrl;
    }
}

// Suggested replacement near the JSON-LD/meta section:
// $updatedAt = gdy_safe_updated_at(isset($news) ? $news : array());
// $updatedAtIso = $updatedAt !== '' ? date('c', strtotime($updatedAt)) : '';
// $canonical = gdy_safe_canonical(isset($canonical) ? $canonical : '', isset($currentUrl) ? $currentUrl : '');
