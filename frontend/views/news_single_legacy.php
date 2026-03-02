<?php
/**
 * Legacy news single view bridge.
 *
 * بعض الإصدارات تمرّر المقال داخل المتغير $news بدل $article.
 * هذا الملف يحوّل البيانات ويحمّل القالب الفعلي إن وُجد.
 */

// Normalize variable name
if (!isset($article) || empty($article)) {
  if (isset($news) && !empty($news)) {
    $article = $news;
  }
}

// If still empty, don't fatal — show a friendly placeholder
if (!isset($article) || empty($article)) {
  // لو عندك نظام flash/messages سيعرضها، وإلا نطبع رسالة بسيطة
  if (!headers_sent()) {
    http_response_code(404);
  }
  echo '<div class="container" style="padding:24px">';
  echo '<div class="gdy-alert gdy-alert-warn" style="padding:16px;border-radius:12px;background:rgba(0,0,0,.04)">';
  echo 'عذراً، لم يتم العثور على الخبر.';
  echo '</div></div>';
  return;
}

// Try to locate the actual template in common locations
$base = __DIR__;
$candidates = [
  $base . '/news_single.php',                 // most common: same folder
  $base . '/news_single_view.php',            // alternative naming
  $base . '/news/view_single.php',            // nested folder
  $base . '/pages/news_single.php',           // pages folder
  dirname($base) . '/news_single.php',        // /frontend/news_single.php
  dirname($base) . '/views/news_single.php',  // /frontend/views/news_single.php (redundant but safe)
];

$loaded = false;
foreach ($candidates as $file) {
  if (is_file($file)) {
    require_once $file;
    $loaded = true;
    break;
  }
}

if (!$loaded) {
  // آخر حل: اطبع محتوى بسيط حتى لا تختفي الصفحة
  echo '<div class="container" style="padding:24px">';
  echo '<h1 style="margin:0 0 12px">' . (function_exists('h') ? h((string)($article['title'] ?? '')) : htmlspecialchars((string)($article['title'] ?? ''), ENT_QUOTES, 'UTF-8')) . '</h1>';
  $body = (string)($article['content'] ?? $article['body'] ?? $article['description'] ?? '');
  echo '<div class="prose" style="line-height:1.9">' . $body . '</div>';
  echo '</div>';
}
