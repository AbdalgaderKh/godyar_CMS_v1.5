<?php
/**
 * Godyar Admin Settings - Embed renderer
 *
 * Some settings tabs are full pages, others are partials.
 * This helper makes tabs safe to include inside settings/index.php.
 */

if (!function_exists('gdy_settings_render_embed')) {
  function gdy_settings_render_embed(callable $fn): string {
    ob_start();
    try {
      $fn();
    } catch (Throwable $e) {
      // Render a small, styled error inside the settings UI instead of a blank screen.
      $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
      echo '<div class="alert alert-danger" style="border-radius:14px;">';
      echo '<strong>خطأ في تحميل تبويب الإعدادات:</strong><div class="mt-2" dir="ltr">' . $msg . '</div>';
      echo '</div>';
    }
    $html = ob_get_clean();

    // If the tab itself printed a full HTML document, try to extract <body>.
    if (stripos($html, '<html') !== false) {
      if (preg_match('~<body[^>]*>(.*)</body>~is', $html, $m)) {
        $html = $m[1];
      }
    }

    return $html;
  }
}
