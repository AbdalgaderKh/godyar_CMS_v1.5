<?php
if (!function_exists('h')) {
  function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$article = (isset($article) && is_array($article)) ? $article : ((isset($news) && is_array($news)) ? $news : []);
if (!$article) {
  if (!headers_sent()) { http_response_code(404); }
  echo '<section class="container" style="padding:24px"><div style="padding:16px;border-radius:12px;background:#fff">' . h(__('news.not_found', 'عذراً، لم يتم العثور على الخبر.')) . '</div></section>';
  return;
}
$baseUrl = rtrim((string)($baseUrl ?? ''), '/');
$navBaseUrl = rtrim((string)($navBaseUrl ?? $baseUrl), '/');
$lang = (string)($pageLang ?? (function_exists('gdy_current_lang') ? gdy_current_lang() : 'ar'));
$uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
if ($navBaseUrl === $baseUrl && preg_match('~^/(ar|en|fr)(?=/|$)~i', $uri, $m)) { $navBaseUrl = $baseUrl . '/' . strtolower($m[1]); }
$id = (int)($article['id'] ?? 0);
$slug = trim((string)($article['slug'] ?? ''));
$titleBase = (string)($article['title'] ?? __('news.untitled', 'خبر بدون عنوان'));
$title = (string)gdy_tr('news', $id, 'title', $titleBase);
$bodyBase = (string)($article['content'] ?? $article['body'] ?? $article['description'] ?? '');
$body = (string)gdy_tr('news', $id, 'content', $bodyBase);
$image = trim((string)($article['featured_image'] ?? $article['image'] ?? $article['image_path'] ?? ''));
if ($image !== '' && !preg_match('~^https?://~i', $image)) { $image = $baseUrl . '/' . ltrim($image, '/'); }
$date = (string)($article['publish_at'] ?? $article['published_at'] ?? $article['created_at'] ?? '');
$canonical = function_exists('gdy_route_news_url') ? gdy_route_news_url(['id'=>$id,'slug'=>$slug], $lang) : ($id > 0 ? ($navBaseUrl . '/news/id/' . $id) : ($slug !== '' ? ($navBaseUrl . '/news/' . rawurlencode($slug)) : ($navBaseUrl . '/news')));
$comments = [];
$userLoggedIn = false;
try {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) { @session_start(); }
    $userLoggedIn = !empty($_SESSION['user']) || !empty($_SESSION['user_id']);
} catch (Throwable $e) {}
if (($pdo ?? null) instanceof PDO && $id > 0) {
    try {
        $hasNewsComments = function_exists('gdy_table_exists_safe') ? gdy_table_exists_safe($pdo, 'news_comments') : false;
        if ($hasNewsComments) {
            $st = $pdo->prepare("SELECT id, COALESCE(name,'') AS author_name, body AS content, created_at FROM news_comments WHERE news_id = ? AND status = 'approved' ORDER BY id ASC LIMIT 100");
            $st->execute([$id]);
            $comments = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } elseif (function_exists('gdy_table_exists_safe') ? gdy_table_exists_safe($pdo, 'comments') : false) {
            $st = $pdo->prepare("SELECT id, COALESCE(author_name,'') AS author_name, COALESCE(body, content, '') AS content, created_at FROM comments WHERE news_id = ? AND status IN ('approved','active') ORDER BY id ASC LIMIT 100");
            $st->execute([$id]);
            $comments = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Throwable $e) { $comments = []; }
}
?>
<section class="container" style="padding-top:24px;padding-bottom:32px;max-width:900px;">
  <nav style="font-size:14px;margin-bottom:16px;color:#6b7280"><a href="<?= h(function_exists('gdy_route_home_url') ? gdy_route_home_url($lang) : ($navBaseUrl . '/')) ?>" style="color:inherit;text-decoration:none"><?= h(__('nav.home', 'الرئيسية')) ?></a> / <a href="<?= h($navBaseUrl . '/news') ?>" style="color:inherit;text-decoration:none"><?= h(__('nav.news', 'الأخبار')) ?></a></nav>
  <article style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,.04)">
    <?php if ($image !== ''): ?><img src="<?= h($image) ?>" alt="<?= h($title) ?>" style="width:100%;max-height:460px;object-fit:cover;display:block"><?php endif; ?>
    <div style="padding:24px;line-height:1.95">
      <?php if ($date !== ''): ?><div style="font-size:13px;color:#6b7280;margin-bottom:10px"><?= h(date('Y-m-d', strtotime($date)) ?: $date) ?></div><?php endif; ?>
      <h1 style="margin:0 0 16px;font-size:34px;line-height:1.45"><?= h($title) ?></h1>
      <div class="article-body" style="font-size:18px;color:#111827"><?= $body !== '' ? $body : '<p>' . h(__('news.no_content', 'لا يوجد محتوى لهذا الخبر.')) . '</p>' ?></div>
    </div>
  </article>

  <section id="comments" style="margin-top:24px;background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px;box-shadow:0 6px 18px rgba(0,0,0,.04)">
    <h2 style="margin:0 0 16px;font-size:22px"><?= h(__('comments.title', 'التعليقات')) ?> (<?= (int)count($comments) ?>)</h2>
    <?php if ($userLoggedIn): ?>
      <form class="comment-form" method="POST" action="<?= h($navBaseUrl . '/comment/add') ?>" data-gdy-once="1" style="display:grid;gap:12px;margin-bottom:18px;">
        <?php if (function_exists('csrf_field')) { echo csrf_field(); } ?>
        <input type="hidden" name="news_id" value="<?= (int)$id ?>">
        <textarea name="content" required placeholder="<?= h(__('comments.placeholder', 'أضف تعليقك...')) ?>" style="min-height:110px;padding:12px;border:1px solid #d1d5db;border-radius:12px"></textarea>
        <div><button type="submit" style="padding:10px 16px;border:0;border-radius:10px;background:#111827;color:#fff;cursor:pointer"><?= h(__('comments.submit', 'نشر التعليق')) ?></button></div>
      </form>
    <?php else: ?>
      <p style="margin:0 0 18px;color:#4b5563"><?= h(__('comments.login_required_prefix', 'يجب')) ?> <a href="<?= h($navBaseUrl . '/login') ?>"><?= h(__('auth.login', 'تسجيل الدخول')) ?></a> <?= h(__('comments.login_required_suffix', 'لإضافة تعليق')) ?></p>
    <?php endif; ?>

    <?php if ($comments): ?>
      <div style="display:grid;gap:14px">
        <?php foreach ($comments as $comment): ?>
          <article style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc">
            <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:8px;flex-wrap:wrap">
              <strong><?= h((string)($comment['author_name'] ?? __('comments.guest', 'زائر'))) ?></strong>
              <span style="font-size:12px;color:#6b7280"><?= h((string)($comment['created_at'] ?? '')) ?></span>
            </div>
            <div style="white-space:pre-wrap;line-height:1.9"><?= nl2br(h((string)($comment['content'] ?? ''))) ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="color:#6b7280"><?= h(__('comments.empty', 'لا توجد تعليقات بعد. كن أول من يعلّق.')) ?></div>
    <?php endif; ?>
  </section>
</section>
