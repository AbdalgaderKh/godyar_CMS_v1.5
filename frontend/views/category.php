<?php
/**
 * Category page view
 *
 * This project ships with a complete template in `category_modern.php`.
 * Historically the controller loads `category.php`, so we keep this file
 * as a wrapper to avoid breaking routing.
 */

$modern = __DIR__ . '/category_modern.php';
if (is_file($modern)) {
    // IMPORTANT: CategoryController includes this view directly (legacy path),
    // so we must render the full page with shared frontend header/footer.

    // Meta vars consumed by `frontend/views/partials/header.php`.
    $catName  = isset($category['name']) ? (string)$category['name'] : '';
    $siteName = isset($siteSettings['site_name']) ? (string)$siteSettings['site_name'] : 'Godyar News';
    $meta_title     = $catName ? ($catName . ' - ' . $siteName) : $siteName;
    $meta_description = isset($category['description']) && trim((string)$category['description']) !== ''
        ? (string)$category['description']
        : ($catName ? ($catName . ' - ' . $siteName) : $siteName);
    $canonical_url  = isset($currentCategoryUrl) ? (string)$currentCategoryUrl : '';

    // Optional social preview image: use the first item's image when present.
    $meta_image = '';
    $first = $news[0] ?? null;
    if (is_array($first)) {
        $meta_image = (string)($first['featured_image']
            ?? $first['image_path']
            ?? $first['main_image']
            ?? $first['image']
            ?? $first['thumbnail']
            ?? '');
    }

    // Pagination rel prev/next for crawlers.
    $meta_prev_url = '';
    $meta_next_url = '';
    if (!empty($pagination) && is_array($pagination) && $canonical_url !== '') {
        $cur = (int)($pagination['current'] ?? 1);
        $pages = (int)($pagination['pages'] ?? 1);
        if ($cur > 1) {
            $meta_prev_url = $canonical_url . '?page=' . ($cur - 1);
        }
        if ($cur < $pages) {
            $meta_next_url = $canonical_url . '?page=' . ($cur + 1);
        }
    }

    $__frontendRoot = dirname(__DIR__); // /frontend
    require $__frontendRoot . '/templates/header.php';
    if (!empty($GLOBALS['__gdy_category_debug'])) { echo "\n<!-- __gdy_category_debug " . $GLOBALS['__gdy_category_debug'] . " -->\n"; }
    require $modern;
    require $__frontendRoot . '/templates/footer.php';
    return;
}

// Fallback: render a minimal, standards-mode page so the browser doesn't
// drop into quirks mode (which breaks layout/CSS).
http_response_code(500);
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>خطأ في عرض التصنيف</title>
</head>
<body>
  <p>تعذر تحميل قالب صفحة التصنيف.</p>
</body>
</html>
