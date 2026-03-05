<?php
/**
 * Modern Category content (rendered between frontend/templates/header.php and footer.php).
 *
 * Expected vars (from CategoryController):
 *  - $category, $news, $categories, $baseUrl, $currentPage, $pages, $currentCategoryUrl
 */

declare(strict_types=1);
/* Debug markers to verify which view is actually rendered */
echo "\n<!-- VIEW_FILE: frontend/views/category_modern.php v11 -->\n";
if (!empty($GLOBALS['__gdy_category_debug'])) { echo "\n<!-- __gdy_category_debug " . $GLOBALS['__gdy_category_debug'] . " -->\n"; }


if (!function_exists('gdy_e')) {
    function gdy_e($v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

$baseUrl = isset($baseUrl) ? (string)$baseUrl : '';
$category = is_array($category ?? null) ? $category : [];
$categories = is_array($categories ?? null) ? $categories : [];
// Accept multiple variable names depending on controller/router (news/newsItems/newsList/items).
$news = $news ?? ($newsItems ?? ($newsList ?? ($items ?? [])));
$news = is_array($news) ? $news : [];

$catName = (string)(gdy_tr('category', $category['id'], 'name', $category['name']) ?? '');
$catSlug = (string)($category['slug'] ?? '');

// Build canonical category URL safely.
$catUrl = $baseUrl;
if ($catSlug !== '') {
    $catUrl = rtrim($baseUrl, '/') . '/category/' . rawurlencode($catSlug);
}

$currentPage = (int)($currentPage ?? 1);
$pages = (int)($pages ?? 1);
if ($currentPage < 1) $currentPage = 1;
if ($pages < 1) $pages = 1;

$fmtDate = function($dt): string {
    $s = (string)$dt;
    if ($s === '') return '';
    // Prefer YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $s, $m)) return $m[0];
    return $s;
};

$newsUrl = rtrim($baseUrl, '/') . '/news';

?>

<div class="as-main">
  <div class="as-container">

    <div class="as-grid as-grid-main">

      <section class="as-block">
        <div class="as-block-hd">
          <h1 class="as-block-title"><?= gdy_e($catName ?: 'Category') ?></h1>
          <a class="as-block-more" href="<?= gdy_e($newsUrl) ?>">More</a>
        </div>

        <?php if (!empty($news)) : ?>
          <div class="as-grid-news" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;">
            <?php
              // Reuse the shared card template so image handling stays consistent.
              $cardTpl = __DIR__ . '/templates/partials/news_card.php';
              foreach ($news as $item) :
                if (!is_array($item)) continue;
                $row = $item; // template expects $row
                if (is_file($cardTpl)) { require $cardTpl; }
              endforeach;
            ?>
          </div>

          <?php if ($pages > 1) : ?>
            <nav class="p-3" aria-label="Pagination" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;justify-content:center;">
              <?php
                $mk = function(int $p) use ($catUrl): string {
                    $p = max(1, $p);
                    return $p === 1 ? $catUrl : ($catUrl . '?page=' . $p);
                };
                $prev = $currentPage - 1;
                $next = $currentPage + 1;
              ?>

              <a class="as-pill" href="<?= gdy_e($mk(1)) ?>" aria-label="First">«</a>
              <a class="as-pill" href="<?= gdy_e($mk(max(1, $prev))) ?>" aria-label="Previous">‹</a>

              <?php
                $start = max(1, $currentPage - 2);
                $end = min($pages, $currentPage + 2);
                for ($p = $start; $p <= $end; $p++) :
                  $is = ($p === $currentPage);
              ?>
                <a class="as-pill<?= $is ? ' is-active' : '' ?>" href="<?= gdy_e($mk($p)) ?>" aria-current="<?= $is ? 'page' : 'false' ?>">
                  <?= (int)$p ?>
                </a>
              <?php endfor; ?>

              <a class="as-pill" href="<?= gdy_e($mk(min($pages, $next))) ?>" aria-label="Next">›</a>
              <a class="as-pill" href="<?= gdy_e($mk($pages)) ?>" aria-label="Last">»</a>
            </nav>
          <?php endif; ?>

        <?php else : ?>
          <div class="p-3 text-muted">لا توجد أخبار داخل هذا القسم حالياً.</div>
        <?php endif; ?>

      </section>

      <aside class="as-side">
        <?php if (!empty($categories)) : ?>
          <section class="as-block">
            <div class="as-block-hd">
              <h2 class="as-block-title">Categories</h2>
            </div>
            <div class="p-3" style="display:flex;flex-wrap:wrap;gap:10px;">
              <?php foreach ($categories as $c) :
                if (!is_array($c)) continue;
                $name = (string)($c['name'] ?? '');
                $slug = (string)($c['slug'] ?? '');
                if ($slug === '') continue;
                $href = rtrim($baseUrl, '/') . '/category/' . rawurlencode($slug);
                $active = ($slug === $catSlug);
              ?>
                <a class="as-pill<?= $active ? ' is-active' : '' ?>" href="<?= gdy_e($href) ?>"><?= gdy_e($name ?: $slug) ?></a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </aside>

    </div>

  </div>
</div>