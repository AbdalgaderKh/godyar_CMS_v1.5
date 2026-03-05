<?php
// ---------------------------------------------------------------------
// Compatibility fallback: gdy_regex_replace()
// ---------------------------------------------------------------------
if (!function_exists('gdy_regex_replace')) {
    /**
     * Safe wrapper around preg_replace with sane defaults.
     * Supports strings/arrays like preg_replace.
     */
    function gdy_regex_replace($pattern, $replacement, $subject, $limit = -1) {
        try {
            // Normalize inputs
            if ($pattern === null) return $subject;

            // Make sure $limit is int
            $limit = is_numeric($limit) ? (int)$limit : -1;

            $result = @preg_replace($pattern, $replacement, $subject, $limit);

            // If preg_replace fails it can return null (preg_last_error != 0)
            if ($result === null) {
                return $subject;
            }
            return $result;
        } catch (\Throwable $e) {
            return $subject;
        }
    }
}

// ---------------------------------------------------------------------
// godyar/frontend/views/news_report.php
// تقرير/خبر بنمط قريب من صفحات التقارير (عنوان + بيانات + شريط أدوات + ملخص + أقسام + جداول)
// ---------------------------------------------------------------------

if (!function_exists('h')) {
    function h($v): string { 
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); 
    }
}

// baseUrl
if (!isset($baseUrl) || $baseUrl === '') {
    if (function_exists('base_url')) {
        $baseUrl = rtrim((string)base_url(), '/');
    } elseif (defined('BASE_URL')) {
        $baseUrl = rtrim((string)BASE_URL, '/');
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;
    }
} else {
    $baseUrl = rtrim((string)$baseUrl, '/');
}

$cspNonce = defined('GDY_CSP_NONCE') ? (string)GDY_CSP_NONCE : '';
$nonceAttr = ($cspNonce !== '') ? ' nonce="' . htmlspecialchars($cspNonce, ENT_QUOTES, 'UTF-8') . '"' : '';

if (!function_exists('gdy_image_url')) {
    /**
     * Build an absolute URL for a news image while normalizing old/duplicated paths .
     * Accepts:
     *-full http(s) URL
     *-absolute path /uploads/news/ ...
     *-relative path (stored in DB) like uploads/news/ ... , or filename .jpg
     *
     * Fixes duplicated paths like: /uploads/news/uploads/news/ ...
     */
    function gdy_image_url(string $baseUrl, ?string $path): ?string
    {
        $path = trim((string)$path);
        if ($path === '') return null;

        // Full URL
        if (preg_match('~^https?://~i', $path)) return $path;

        // Normalize duplicated segments and leading slashes
        $path = ltrim($path, '/');

        // If DB stores full uploads path (not necessarily uploads/news)
        //-uploads/anything .jpg => {baseUrl}/uploads/anything .jpg
        //-uploads/news/x .jpg => normalize to avoid duplication then handled below
        if (preg_match('~^uploads/[^\s]+~i', $path) && !preg_match('~^uploads/news/~i', $path)) {
            return rtrim($baseUrl, '/') . '/' . $path;
        }

        // remove duplicated "uploads/news/uploads/news/"
        $path = gdy_regex_replace('~^(?:uploads/news/)+uploads/news/~i', 'uploads/news/', $path);
        // if stored with "uploads/news/" prefix, strip it because we will prefix once
        $path = gdy_regex_replace('~^uploads/news/~i', '', $path);
        $path = ltrim($path, '/');

        // If original was absolute path like "/something", keep it (after normalization)
        // (Note: we already trimmed leading '/', so handle explicitly)
        // If the normalized path still contains a leading directory like "assets/..." leave it as-is by returning baseUrl + "/" + path .
        if (preg_match('~^(assets|static|images?)/~i', $path)) {
            return rtrim($baseUrl, '/') . '/' . $path;
        }

        $url = rtrim($baseUrl, '/') . '/uploads/news/' . $path;

        // Prevent broken-image requests on shared hosting: if file doesn't exist locally, return null.
        // (This keeps the UI clean and avoids 404 spam in the browser console . )
        if (defined('ROOT_PATH')) {
            $p = parse_url($url, PHP_URL_PATH);
            if (is_string($p) && $p !== '' && str_starts_with($p, '/uploads/')) {
                $local = rtrim((string)ROOT_PATH, '/ ') . $p;
                if (is_file($local) === false) {
                    return null;
                }
            }
        }

        return $url;
    }
}

if (!function_exists('gdy_plaintext')) {
    function gdy_plaintext(string $html): string
    {
        $txt = (string)$html;
        if ($txt === '') return '';

        // حوّل الوسوم الكتلية إلى أسطر للحفاظ على الفقرات
        $txt = gdy_regex_replace('~<\s*br\s*/?\s*>~i', "\n", $txt);
        $txt = gdy_regex_replace('~</\s*(p|div|li|h1|h2|h3|h4|h5|h6|tr|blockquote)\s*>~i', "\n", $txt);

        $txt = strip_tags($txt);
        $txt = gdy_regex_replace('~[ \t]+~u', ' ', $txt);

        // وحّد الأسطر
        $txt = gdy_regex_replace('~\r?\n~u', "\n", $txt);
        $txt = gdy_regex_replace('~\n{3,}~u', "\n\n", $txt);

        return trim($txt);
    }
}

if (!function_exists('gdy_auto_summary_lines')) {
    /**
     * توليد ملخص سريع محلي (بدون API) من النص .
     * يحاول استخراج 4–6 جمل، وإن تعذر يأخذ مقاطع من بداية النص .
     */
    function gdy_auto_summary_lines(string $bodyHtml, int $maxLines = 5): array
    {
        $txt = gdy_plaintext($bodyHtml);
        if ($txt === '') return [];

        // قسم النص إلى جمل (عربي/إنجليزي)
        $parts = preg_split('~(?<=[\.!\?؟])\s+|[

]+~u', $txt);
        $lines = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') continue;

            // اقبل الجمل الأقصر (العربية أحياناً قصيرة)
            $words = preg_split('/\s+/u', $p);
            if (count($words) < 4 && mb_strlen($p, 'UTF-8') < 40) continue;

            if (mb_strlen($p, 'UTF-8') > 180) {
                $p = mb_substr($p, 0, 180, 'UTF-8');
                $p = rtrim($p, " 	

\0
،، . ") . '…';
            }
            $lines[] = $p;
            if (count($lines) >= $maxLines) break;
        }

        // احتياطي 1: فقرات
        if (count($lines) < 2) {
            $paras = preg_split("/
{2,}/u", $txt);
            foreach ($paras as $para) {
                $para = trim($para);
                if ($para === '') continue;
                if (mb_strlen($para, 'UTF-8') > 220) {
                    $para = mb_substr($para, 0, 220, 'UTF-8') . '…';
                }
                $lines[] = $para;
                if (count($lines) >= $maxLines) break;
            }
        }

        // احتياطي 2: خذ من بداية النص وقطّعه على الفواصل العربية
        if (count($lines) < 2) {
            $head = mb_substr($txt, 0, 520, 'UTF-8');
            $chunks = preg_split('~[،؛

\-]+~u', $head);
            foreach ($chunks as $c) {
                $c = trim($c);
                if ($c === '') continue;
                if (mb_strlen($c, 'UTF-8') < 25) continue;
                if (mb_strlen($c, 'UTF-8') > 200) $c = mb_substr($c, 0, 200, 'UTF-8') . '…';
                $lines[] = $c;
                if (count($lines) >= $maxLines) break;
            }
        }

        // إزالة التكرار
        $uniq = [];
        foreach ($lines as $l) {
            $k = mb_strtolower(gdy_regex_replace('/\s+/u',' ', $l), 'UTF-8');
            $uniq[$k] = $l;
        }
        return array_values($uniq);
    }
}

if (!function_exists('gdy_auto_summary_html')) {
    function gdy_auto_summary_html(string $bodyHtml): string
    {
        $lines = gdy_auto_summary_lines($bodyHtml, 5);
        if (!$lines) return '';
        $out = '<ul class="gdy-ai-list">';
        foreach ($lines as $l) {
            $out .= '<li>' .htmlspecialchars($l, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $out .= '</ul>';
        return $out;
    }
}

if (!function_exists('gdy_slugify_ar')) {
    // مبسط: يولّد id للحواشي/العناوين — لا يعتمد عليه كرابط عام
    function gdy_slugify_ar(string $s): string {
        $s = trim($s);
        $s = gdy_regex_replace('~\s+~u', ' ', $s);
        $s = mb_substr($s, 0, 80, 'UTF-8');
        // أبقِ العربية، واحذف الرموز الغريبة
        $s = gdy_regex_replace('~[^\p{L}\p{N}\s\-]+~u', '', $s);
        $s = preg_replace_callback('~\s+~u', static fn($m) => '-', $s);
        $s = trim($s, '-');
        return $s !== '' ? $s : 'sec';
    }
}

if (!function_exists('gdy_build_toc')) {
    /**
     * يضيف ids تلقائياً لـ h2/h3 ويبني جدول محتويات
     * @return array{html:string,toc:array<int,array{level:int,id:string,text:string}>}
     */
    function gdy_build_toc(string $html): array
    {
        $toc = [];
        $used = [];

        $cb = function(array $m) use (&$toc, &$used) {
            $tag = strtolower($m[1]); // h2/h3
            $attrs = (string)$m[2];
            $inner = (string)$m[3];

            $text = trim(strip_tags($inner));
            if ($text === '') {
                return $m[0];
            }

            $level = ($tag === 'h2') ? 2 : 3;

            // هل يوجد id؟
            if (preg_match('~\bid\s*=\s*"([^"]+)"~i', $attrs, $idm)) {
                $id = $idm[1];
            } else {
                $id = gdy_slugify_ar($text);
            }

            // ضمان التفرد
            $base = $id;
            $i = 2;
            while (isset($used[$id])) {
                $id = $base . '-' . $i;
                $i++;
            }
            $used[$id] = true;

            if (!preg_match('~\bid\s*=~i', $attrs)) {
                $attrs = trim($attrs) . ' id="' .h($id) . '"';
            }

            $toc[] = ['level' => $level, 'id' => $id, 'text' => $text];

            return '<' . $tag . $attrs . '>' . $inner . '</' . $tag . '>';
        };

        // التقط h2/h3 مع محتواها (بدون كسر بنية معقدة)
        $html2 = preg_replace_callback('~<(h2|h3)([^>]*)>(.*?)</\1>~isu', $cb, $html);

        return ['html' => $html2 ?? $html, 'toc' => $toc];
    }
}

if (!function_exists('gdy_wrap_tables')) {
    function gdy_wrap_tables(string $html): string
    {
        // أضف class للجداول ولفّها بحاوية سكرول
        $html = preg_replace_callback('~<table(\s[^>]*)?>~i', static fn($m) => '<div class="gdy-table-wrap"><table' . ($m[1] ?? '') . ' class="gdy-report-table">', $html);
        $html = gdy_regex_replace('~</table>~i', '</table></div>', $html);
        return $html;
    }
}

if (!function_exists('gdy_strtotime')) {
    function gdy_strtotime($date) {
        if (empty($date)) return false;
        return strtotime($date);
    }
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
// بعض القوالب تعتمد على دالة gdy_news_field (كانت موجودة في نسخ قديمة).
// عند غيابها يحدث Fatal Error ويختفي المحتوى + الفوتر.
// هذه نسخة آمنة: تُعيد قيمة الحقل من مصفوفة الخبر أولاً، ثم (اختيارياً)
// تُحاول جلبه من قاعدة البيانات من جدول news باستخدام نفس الـ id.
if (!function_exists('gdy_news_field')) {
    function gdy_news_field(PDO $pdo, array $post, string $field): string
    {
        $field = strtolower(trim($field));

        // 1) من المصفوفة مباشرة (الأولوية)
        if (array_key_exists($field, $post) && $post[$field] !== null && $post[$field] !== '') {
            return (string)$post[$field];
        }

        // مرادفات شائعة بين نسخ القاعدة/القوالب
        $aliases = [
            'title'   => ['title', 'name'],
            'content' => ['content', 'body', 'description', 'details', 'text'],
            'excerpt' => ['excerpt', 'summary', 'description'],
            'image'   => ['featured_image', 'image_path', 'image'],
        ];

        // 2) جرّب المرادفات من المصفوفة
        foreach (($aliases[$field] ?? []) as $k) {
            if (array_key_exists($k, $post) && $post[$k] !== null && $post[$k] !== '') {
                return (string)$post[$k];
            }
        }

        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) {
            return '';
        }

        // 3) جلب من قاعدة البيانات (قائمة أعمدة آمنة فقط)
        $cands = $aliases[$field] ?? [$field];
        $valid = [];
        foreach ($cands as $c) {
            $c = trim((string)$c);
            if ($c !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $c)) {
                $valid[] = $c;
            }
        }
        $valid = array_values(array_unique($valid));
        if (!$valid) {
            return '';
        }

        // ملاحظة: نستخدم backticks للأعمدة لتفادي التعارض مع كلمات محجوزة
        $cols = implode(', ', array_map(static fn($c) => '`' . $c . '`', $valid));
        $sql  = "SELECT {$cols} FROM `news` WHERE `id` = :id LIMIT 1";

        try {
            $st = $pdo->prepare($sql);
            $st->execute([':id' => $id]);
            $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

            foreach ($valid as $c) {
                if (isset($row[$c]) && $row[$c] !== null && $row[$c] !== '') {
                    return (string)$row[$c];
                }
            }
        } catch (Throwable $e) {
            // تجاهل الخطأ: المطلوب عدم كسر العرض
            return '';
        }

        return '';
    }
}

// ------------------------------------------------------------
// Data
// ------------------------------------------------------------
$post = $news ?? $article ?? [];
$postId = (int)($post['id'] ?? 0);

// URL of this article (used for sharing + QR)
$newsUrl = '';
if ($postId > 0) {
    $newsUrl = rtrim((string)$baseUrl, '/') . '/news/id/' . $postId;
}
$pageUrl = $newsUrl;

// QR API (fallback)
$qrApi = (isset($qrApi) && $qrApi !== '') ? (string)$qrApi : 'https://api.qrserver.com/v1/create-qr-code/?';
$qrApi = trim($qrApi);
if (strpos($qrApi, '?') === false) { $qrApi .= '?'; }
elseif (!str_ends_with($qrApi, '?') && !str_ends_with($qrApi, '&')) { $qrApi .= '&'; }

$pdo = $pdo ?? (function_exists('gdy_pdo_safe') ? gdy_pdo_safe() : null);

$title = ($pdo instanceof PDO) ? (string)gdy_news_field($pdo, $post, 'title') : (string)($post['title'] ?? '');
$body = ($pdo instanceof PDO) ? (string)gdy_news_field($pdo, $post, 'content') : (string)($post['content'] ?? ($post['body'] ?? ''));

// ✅ تم إزالة ميزة الترجمة نهائياً (لا يتم استخدام ?lang لترجمة المقال)
$excerpt = ($pdo instanceof PDO) ? (string)gdy_news_field($pdo, $post, 'excerpt') : (string)($post['excerpt'] ?? ($post['summary'] ?? ''));
$cover = (string)($post['featured_image'] ?? ($post['image_path'] ?? ($post['image'] ?? '')));

$categoryName = (string)($post['category_name'] ?? ($category['name'] ?? 'أخبار عامة'));
$categorySlug = (string)($post['category_slug'] ?? ($category['slug'] ?? 'general-news'));

$date = (string)($post['published_at'] ?? ($post['publish_at'] ?? ($post['created_at'] ?? '')));
$views = (int)($post['views'] ?? 0);
$readMinutes = (int)($post['read_time'] ?? ($readingTime ?? 0));
if ($readMinutes <= 0) $readMinutes = 1;

// مصدر/كاتب
$sourceLabel = (string)($post['source'] ?? ($post['source_name'] ?? ''));
$authorName = (string)($post['author_name'] ?? ($post['opinion_author_name'] ?? ''));
$opinionAuthorId = (int)($post['opinion_author_id'] ?? 0);
$opinionAuthorRow = null;

$authorUrl = '';
if ($opinionAuthorId > 0) {
    // جلب بيانات كاتب الرأي (للصفحة + الكرت) إن توفرت
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmtOA = $pdo->prepare("
                SELECT id, name, slug, page_title, avatar, social_facebook, social_twitter, social_website, email
                FROM opinion_authors
                WHERE id = :id AND is_active = 1
                LIMIT 1
            ");
            $stmtOA->execute([':id' => $opinionAuthorId]);
            $opinionAuthorRow = $stmtOA->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            $opinionAuthorRow = null;
        }
    }

    $slugOA = trim((string)($opinionAuthorRow['slug'] ?? ''));
    if ($slugOA !== '') {
        $authorUrl = rtrim($baseUrl, '/') . '/opinion_author.php?slug=' .rawurlencode($slugOA);
    } else {
        $authorUrl = rtrim($baseUrl, '/') . '/opinion_author.php?id=' . $opinionAuthorId;
    }

    // استخدام اسم الكاتب من جدول كتّاب الرأي إن وُجد
    if (!empty($opinionAuthorRow['name'])) {
        $authorName = (string)$opinionAuthorRow['name'];
    }
}

$showOpinionAuthorCard = ($opinionAuthorId > 0 && $opinionAuthorRow !== null);
$pageUrl = (string)$newsUrl;
// بيانات كرت الكاتب + روابط التواصل (فيس/تويتر/واتساب/إيميل)
$oaName = $authorName;
$oaPageTitle = $showOpinionAuthorCard ? trim((string)($opinionAuthorRow['page_title'] ?? '')) : '';
$oaAvatarRaw = $showOpinionAuthorCard ? trim((string)($opinionAuthorRow['avatar'] ?? '')) : '';
$oaAvatar = rtrim($baseUrl,'/') . '/assets/images/avatar.png';
if ($oaAvatarRaw !== '') {
    $oaAvatar = preg_match('~^https?://~i', $oaAvatarRaw)
        ? $oaAvatarRaw
        : (rtrim($baseUrl, '/') . '/' .ltrim($oaAvatarRaw, '/'));
}

$oaFacebook = $showOpinionAuthorCard ? trim((string)($opinionAuthorRow['social_facebook'] ?? '')) : '';
$oaTwitter = $showOpinionAuthorCard ? trim((string)($opinionAuthorRow['social_twitter'] ?? '')) : '';
$oaEmail = $showOpinionAuthorCard ? trim((string)($opinionAuthorRow['email'] ?? '')) : '';
$oaWebsite = $showOpinionAuthorCard ? trim((string)($opinionAuthorRow['social_website'] ?? '')) : '';

$shareWhatsapp = 'https://wa.me/?text=' .rawurlencode($pageUrl);
$shareEmail = 'mailto:?subject=' .rawurlencode($title) . '&body=' .rawurlencode($pageUrl);
// ملخص AI (اختياري) — إن لم يوجد يتم توليد ملخص سريع محلياً
$aiSummaryDb = (string)($post['ai_summary'] ?? ($post['summary_ai'] ?? ''));
$aiSummaryMode = $aiSummaryDb !== '' ? 'db' : 'auto';
$aiSummary = $aiSummaryDb !== '' ? $aiSummaryDb : gdy_auto_summary_html($body);
if ($aiSummaryDb === '' && $aiSummary === '') {
    $plain = gdy_plaintext($body);
    if ($plain !== '') {
        $plain = mb_substr($plain, 0, 320, 'UTF-8');
        $aiSummary = '<div class="gdy-lh-19">' .h($plain) . (mb_strlen($plain,'UTF-8')>=320 ? '…' : '') . '</div>';
    }
}

$aiBtnLabel = ($aiSummaryMode === 'db') ? 'ملخص بالذكاء الاصطناعي' : 'ملخص سريع';
$aiBtnNote = ($aiSummaryMode === 'auto') ? 'تم توليده تلقائياً' : '';

// (already defined earlier) $newsUrl is available here .
$coverUrl = gdy_image_url($baseUrl, $cover) ?: null;

// LCP: مرِّر صورة الغلاف للهيدر ليعمل Preload مبكراً + امنع التحميل الكسول
$pagePreloadImages = !empty($coverUrl) ? [$coverUrl] : [];

// SEO (الهيدر الموحد يقرأ $pageSeo)
$seoDesc = $excerpt !== '' ? $excerpt : mb_substr(trim(strip_tags($body)), 0, 170, 'UTF-8');
$publishedIso = '';
if ($date !== '') {
    $ts = gdy_strtotime($date);
    if ($ts) $publishedIso = date('c', $ts);
}
$pageSeo = [
    'title' => $title !== '' ? ($title . (isset($siteName) && $siteName ? '-' . (string)$siteName : '')) : ((string)($siteName ?? '')),
    'description' => $seoDesc,
    'image' => $coverUrl,
    'url' => $newsUrl,
    'type' => 'article',
    'published_time' => $publishedIso,
];

// ------------------------------------------------------------
// Header include (if not wrapped by TemplateEngine)
// ------------------------------------------------------------
$isPrintMode = false;
try {
    $isPrintMode = isset($_GET['print']) && (string)$_GET['print'] === '1';
} catch (\Throwable $e) {
    $isPrintMode = false;
}

// === Metered/Members Paywall logic moved BEFORE header include (fix headers already sent) ===
$membersOnly = isset($membersOnly)
    ? (bool)$membersOnly
    : (((int)($post['is_members_only'] ?? 0) === 1) || ((int)($category['is_members_only'] ?? 0) === 1));

$canReadFull = isset($canReadFull)
    ? (bool)$canReadFull
    : (!empty($isLoggedIn) || !empty($_SESSION['user']) || !empty($_SESSION['user_id']));

$meteredLocked = false;

$gdyMeterCookieToSet = null;
$gdyMeterCookieMaxAge = null;
// Metered Paywall (الخيار 2): حد قراءة مجاني للزائر (بدون تسجيل)
$meterLimit = 3; // 3 مقالات
$meterWindowSeconds = 7 * 24 * 60 * 60; // خلال أسبوع
$meterCount = 0;
$meterCurrentInWindow = false;

$isGuest = !$canReadFull;
if ($isGuest && !$membersOnly && $postId > 0) {
    $raw = $_COOKIE['gdy_meter'] ?? '';
    $rawDecoded = $raw !== '' ? rawurldecode($raw) : '';
    $items = [];
    if ($raw !== '') {
        $decoded = json_decode($rawDecoded, true);
        if (is_array($decoded)) $items = $decoded;
    }

    $now = time();
    $fresh = [];
    $seen = [];
    foreach ($items as $it) {
        $id = (int)($it['id'] ?? 0);
        $t = (int)($it['t'] ?? 0);
        if ($id > 0 && $t > 0 && ($now-$t) <= $meterWindowSeconds) {
            $fresh[] = ['id' => $id, 't' => $t];
            $seen[$id] = true;
        }
    }

    $meterCount = count($seen);
    $meterCurrentInWindow = isset($seen[$postId]);

    // إذا تجاوز حد القراءة المجانية (3/أسبوع) وقمت بفتح مقال جديد → قفل ذكي
    if ($meterCount >= $meterLimit && !$meterCurrentInWindow) {
        $meteredLocked = true;
    }

    // احفظ النسخة المُصفّاة في كوكي (لتقليل الحجم)
    if ($raw !== '' || !empty($fresh)) {
        $gdyMeterCookieToSet = rawurlencode(json_encode($fresh, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        $gdyMeterCookieMaxAge = 30 * 24 * 60 * 60; // 30 days
    }
}

// === End moved block ===


// ------------------------------------------------------------
// v3.0 SEO: NewsArticle + Breadcrumb JSON-LD + Tags fetch
// ------------------------------------------------------------
$tags = [];
if (isset($pdo) && ($pdo instanceof PDO) && $postId > 0) {
    try {
        $st = $pdo->prepare("
            SELECT t.name, t.slug
            FROM news_tags nt
            JOIN tags t ON t.id = nt.tag_id
            WHERE nt.news_id = :nid AND (t.is_active = 1 OR t.is_active IS NULL)
            ORDER BY t.name ASC
            LIMIT 20
        ");
        $st->execute([':nid' => $postId]);
        $tags = (array)$st->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        $tags = [];
    }
}

// Build JSON-LD for article page (rendered by header.php if $jsonLd is set)
try {
    $img = is_string($meta_image ?? '') ? (string)$meta_image : '';
    $author = $authorName !== '' ? $authorName : ($siteName ?? 'Godyar News');
    $pub = $date !== '' ? date('c', strtotime($date)) : null;
    $mod = $updatedAt !== '' ? date('c', strtotime($updatedAt)) : ($pub ?: null);

    $crumbItems = [
        ['@type'=>'ListItem','position'=>1,'name'=>($t_home ?? 'الرئيسية'),'item'=>rtrim($baseUrl,'/').'/'],
        ['@type'=>'ListItem','position'=>2,'name'=>($categoryName ?: ($t_news ?? 'الأخبار')),'item'=>($categorySlug ? rtrim($baseUrl,'/').'/category/'.rawurlencode($categorySlug) : rtrim($baseUrl,'/').'/news')],
        ['@type'=>'ListItem','position'=>3,'name'=>$title,'item'=>$canonical],
    ];

    $jsonLd = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $crumbItems,
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => ['@type'=>'WebPage','@id'=>$canonical],
            'headline' => $title,
            'description' => $seoDesc,
            'datePublished' => $pub,
            'dateModified' => $mod,
            'author' => ['@type'=>'Person','name'=>$author],
            'publisher' => [
                '@type'=>'Organization',
                'name'=>$siteName ?? 'Godyar News',
                'logo'=> $logoUrl ? ['@type'=>'ImageObject','url'=>$logoUrl] : null
            ],
            'image' => $img !== '' ? [$img] : null,
        ],
    ];

    // Remove nulls recursively (clean JSON-LD)
    $jsonLd = array_map(function($item){
        if (!is_array($item)) return $item;
        $it = [];
        foreach ($item as $k=>$v){
            if (is_array($v)) {
                // filter nested nulls
                $v2 = $v;
                if (array_values($v2) === $v2) {
                    $v2 = array_values(array_filter($v2, fn($x)=>$x!==null && $x!=='' ));
                } else {
                    foreach ($v2 as $kk=>$vv){
                        if ($vv === null || $vv === '') unset($v2[$kk]);
                    }
                }
                if (empty($v2)) continue;
                $it[$k]=$v2;
            } else {
                if ($v === null || $v === '') continue;
                $it[$k]=$v;
            }
        }
        return $it;
    }, $jsonLd);

} catch (\Throwable $e) {
    // ignore JSON-LD failures
}

$header = __DIR__ . '/partials/header.php';
$footer = __DIR__ . '/partials/footer.php';
if (!defined('GDY_TPL_WRAPPED') && is_file($header)) {
    require $header;
}

// Not found guard
$newsExists = $postId > 0 && $title !== '';
if (!$newsExists) {
    http_response_code(404);
    ?>
    <main class="layout-main">
      <div class="container">
        <div class="gdy-notfound">
          <div class="gdy-notfound-icon"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg></div>
          <h1>الخبر غير موجود</h1>
          <p>عذراً، لم نتمكن من العثور على الخبر الذي تبحث عنه.</p>
          <div class="gdy-notfound-actions">
            <a class="btn-primary" href="<?php echo h($baseUrl) ?>/">الرئيسية</a>
            <a class="btn-secondary" href="<?php echo h($baseUrl) ?>/trending">الأكثر تداولاً</a>
          </div>
        </div>
      </div>
    </main>
    <?php
    if (!defined('GDY_TPL_WRAPPED') && is_file($footer)) require $footer;
    return;
}

// ------------------------------------------------------------
// Members-only (Option A: show list + lock badge + paywall)
// ------------------------------------------------------------
$isPaywalled = ($membersOnly && !$canReadFull) || $meteredLocked;

$paywallBoxHtml = '';

// Paywall (الخيار 2): عرض جزء كبير من المقال وإخفاء الجزء الأخير فقط .
// نحدد مصدر منفصل لـ TTS حتى لا يقرأ نص صندوق الـ Paywall .
$gdyBodyForTts = null;

if ($isPaywalled) {
    $fullBody = (string)$body;

    // محاولة قصّ HTML على مستوى الفقرات للحفاظ على تنسيق المقال .
    $previewHtml = '';
    $paras = [];
    if (preg_match_all('~<p\b[^>]*>.*?</p>~is', $fullBody, $m)) {
        $paras = $m[0] ?? [];
    }

    if (!empty($paras)) {
        $count = count($paras);
        // اعرض 85% من الفقرات كحد افتراضي (مع حد أدنى 2) وبشرط أن يبقى جزء محجوب .
        $keep = (int)ceil($count * 0.85);
        $keep = max(2, $keep);
        $keep = min($keep, $count-1);

        $previewHtml = implode("\n", array_slice($paras, 0, $keep));
    } else {
        // احتياطي: قصّ نصي إذا كان المحتوى بدون فقرات .
        $txt = trim((string)gdy_plaintext($fullBody));
        $txt = gdy_regex_replace('~\s+~u', ' ', (string)$txt);
        $more = (mb_strlen((string)$txt, 'UTF-8') > 1200);
        $txt = mb_substr((string)$txt, 0, 1200, 'UTF-8');
        $previewHtml = '<p class="gdy-m-0">' .nl2br(h($txt)) . ($more ? '…' : '') . '</p>';
    }

    $loginUrl = rtrim((string)$baseUrl, '/') . '/login.php?next=' .rawurlencode((string)$newsUrl);
    $registerUrl = rtrim((string)$baseUrl, '/') . '/register.php?next=' .rawurlencode((string)$newsUrl);

    if ($meteredLocked) {
        $paywallBoxHtml = '<div class="gdy-paywall-box" role="region" aria-label="حد القراءة المجانية">' .
            '<div class="gdy-paywall-icon"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg></div>' .
            '<div class="gdy-paywall-content">' .
                '<strong>وصلت لحد القراءة المجانية</strong>' .
                '<div class="gdy-paywall-sub">يمكنك قراءة ' . (int)$meterLimit . ' مقالات مجاناً خلال أسبوع. سجّل دخولك أو أنشئ حساباً لمتابعة القراءة.</div>' .
                '<div class="gdy-paywall-sub" class="gdy-op-90">قرأت: ' . (int)$meterCount . ' / ' . (int)$meterLimit . '</div>' .
                '<div class="gdy-paywall-actions">' .
                    '<a class="gdy-paywall-btn primary" href="' .h($loginUrl) . '">' .
                        '<svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#user"></use></svg> تسجيل الدخول' .
                    '</a>' .
                    '<a class="gdy-paywall-btn" href="' .h($registerUrl) . '">' .
                        '<svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#plus"></use></svg> إنشاء حساب' .
                    '</a>' .
                '</div>' .
            '</div>' .
        '</div>';
    } else {
        $paywallBoxHtml = '<div class="gdy-paywall-box" role="region" aria-label="محتوى للأعضاء">' .
            '<div class="gdy-paywall-icon"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg></div>' .
            '<div class="gdy-paywall-content">' .
                '<strong>هذا المقال للأعضاء فقط</strong>' .
                '<div class="gdy-paywall-sub">سجّل دخولك أو أنشئ حساباً لمتابعة قراءة المقال بالكامل.</div>' .
                '<div class="gdy-paywall-actions">' .
                    '<a class="gdy-paywall-btn primary" href="' .h($loginUrl) . '">' .
                        '<svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#user"></use></svg> تسجيل الدخول' .
                    '</a>' .
                    '<a class="gdy-paywall-btn" href="' .h($registerUrl) . '">' .
                        '<svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#plus"></use></svg> إنشاء حساب' .
                    '</a>' .
                '</div>' .
            '</div>' .
        '</div>';
    }

    // الخيار 2: اعرض المعاينة داخل المقال ثم ضع صندوق الـ Paywall في نهاية المعاينة .
    $body = $previewHtml;
    // TTS يعتمد على نص المعاينة فقط
    $gdyBodyForTts = $previewHtml;
}

// ------------------------------------------------------------
// Prepare content (TOC + tables)
// ------------------------------------------------------------
if (!$isPaywalled) {
    $body = gdy_wrap_tables($body);
    $built = gdy_build_toc($body);
    $body = $built['html'];
    $toc = $built['toc'];

    // ------------------------------------------------------------
    // Inline blocks (TOC + Poll داخل المقال)
    // ------------------------------------------------------------
    $inlineTocHtml = '';
    if (!empty($toc) && is_array($toc)) {
        $inlineTocHtml .= '<div class="gdy-inline-toc" id="gdyInlineToc">';
        $inlineTocHtml .= '<div class="gdy-inline-toc-h"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> ' .h(__('فهرس المحتوى')) . '</div>';
        $inlineTocHtml .= '<div class="gdy-inline-toc-b">';
        foreach ($toc as $item) {
            $cls = ($item['level'] ?? 2) === 3 ? 'lv3' : 'lv2';
            $id = (string)($item['id'] ?? '');
            $tx = (string)($item['text'] ?? '');
            if ($id === '' || $tx === '') continue;
            $inlineTocHtml .= '<div class="' . $cls . '"><a href="#' .h($id) . '">' .h($tx) . '</a></div>';
        }
        $inlineTocHtml .= '</div></div>';
    }

    // Poll placeholder (سيتم ملؤه عبر JS إذا كان هناك استطلاع لهذا المقال)
    $pollHtml = '';
    if ($postId > 0) {
        $pollHtml = '<div class="gdy-inline-poll"><div class="gdy-inline-poll-h"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#plus"></use></svg> ' .h(__('استطلاع')) . '</div><div id="gdy-poll" data-news-id="' . (int)$postId . '"></div></div>';
    }

    // إدراج الاستطلاع بعد أول فقرة إن أمكن
    $bodyWithPoll = $body;
    if ($pollHtml !== '') {
        if (stripos($bodyWithPoll, '</p>') !== false) {
            $bodyWithPoll = gdy_regex_replace('~</p>~i', '</p>' . $pollHtml, $bodyWithPoll, 1);
        } else {
            $bodyWithPoll = $pollHtml . $bodyWithPoll;
        }
    }

    // TOC داخل المقال (يظهر خصوصاً على الجوال) + محتوى المقال
    $body = $inlineTocHtml . $bodyWithPoll;
} else {
    $toc = [];
}

?>
<div class="gdy-progress" id="gdyProgress"></div>

<main class="gdy-report-page">
  <div class="container">
    <?php $printSiteName = (string)($siteName ?? 'Godyar News'); ?>
    <div class="gdy-print-head" aria-hidden="true">
      <div class="gdy-print-head-inner">
        <div class="gdy-print-brand">
          <div class="name"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#news"></use></svg><?php echo h($printSiteName) ?></div>
          <div class="gdy-print-right">
            <a class="url" href="<?php echo h($newsUrl) ?>"><?php echo h($newsUrl) ?></a>
            <div class="gdy-print-qr" title="QR"></div>
          </div>
        </div>
        <div class="gdy-print-sub">
          <span><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg>رابط المقال</span>
        </div>
      </div>
    </div>

    <div class="gdy-report-hero">
      <?php if ($newsUrl !== ''): ?>
        <div class="gdy-hero-qr" id="gdyHeroQr" title="رمز QR للمقال">
          <img class="gdy-qr-image" alt="QR" loading="lazy" src="<?php echo h($qrApi) ?>size=160x160&data=<?php echo rawurlencode($newsUrl) ?>" />
        </div>
      <?php endif; ?>

      <nav class="gdy-breadcrumbs" aria-label="مسار التنقل">
        <a href="<?php echo h($baseUrl) ?>/">الرئيسية</a>
        <span>›</span>
        <a href="<?php echo h($baseUrl) ?>/category/<?php echo rawurlencode($categorySlug) ?>"><?php echo h($categoryName) ?></a>
        <span>›</span>
        <span>تقرير</span>
      </nav>

      <?php if (!empty($showOpinionAuthorCard)): ?>
        <div class="gdy-opinion-author-card" aria-label="<?php echo h(__('كاتب المقال')) ?>">
          <div class="gdy-opinion-author-avatar" aria-hidden="true"><?php
            $initial = $oaName !== '' ? mb_substr($oaName, 0, 1, 'UTF-8') : '؟';
            echo h($initial);
          ?></div>

          <div class="gdy-opinion-author-name">
            <?php if ($authorUrl !== ''): ?>
              <a href="<?php echo h($authorUrl) ?>" style="color:inherit;text-decoration:none;">
                <?php echo h($oaName) ?>
              </a>
            <?php else: ?>
              <?php echo h($oaName) ?>
            <?php endif; ?>
          </div>

          <?php if ($oaPageTitle !== ''): ?>
            <div class="gdy-opinion-author-pill">
              <span><?php echo h($oaPageTitle) ?></span>
            </div>
          <?php endif; ?>

          <div class="gdy-opinion-social" aria-label="<?php echo h(__('تواصل مع الكاتب')) ?>">
            <?php if ($oaFacebook !== ''): ?>
              <a href="<?php echo h($oaFacebook) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#facebook"></use></svg></a>
            <?php endif; ?>
            <?php if ($oaTwitter !== ''): ?>
              <a href="<?php echo h($oaTwitter) ?>" target="_blank" rel="noopener" aria-label="X"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#x"></use></svg></a>
            <?php endif; ?>

            <a href="<?php echo h($shareWhatsapp) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#whatsapp"></use></svg></a>

            <?php if ($oaEmail !== ''): ?>
              <a href="mailto:<?php echo h($oaEmail) ?>" aria-label="Email"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg></a>
            <?php else: ?>
              <a href="<?php echo h($shareEmail) ?>" aria-label="Email"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg></a>
            <?php endif; ?>
          </div>
        </div>

        <div class="gdy-opinion-divider" aria-hidden="true"></div>
        <div class="gdy-opinion-article-badge"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> <?php echo h(__('مقال مميز')) ?></div>
      <?php endif; ?>

      <h1 class="gdy-report-title"><?php echo h($title) ?></h1>

      <div class="gdy-meta-row">
        <?php if ($date !== ''): ?>
          <span class="gdy-pill"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg><?php echo h(date('Y/m/d', strtotime($date))) ?></span>
        <?php endif; ?>
        <?php if ($sourceLabel !== ''): ?>
          <span class="gdy-pill"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg><?php echo h($sourceLabel) ?></span>
        <?php endif; ?>
        <?php if (!$showOpinionAuthorCard && $authorName !== ''): ?>
          <span class="gdy-pill"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#user"></use></svg><?php echo h($authorName) ?></span>
        <?php endif; ?>
        <span class="gdy-pill"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg><?php echo (int)$readMinutes ?> د</span>
        <?php if ($views > 0): ?>
          <span class="gdy-pill"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#external-link"></use></svg><?php echo (int)$views ?></span>
        <?php endif; ?>
        <?php if (!empty($membersOnly)): ?>
          <span class="gdy-pill"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> للأعضاء</span>
        <?php endif; ?>
      </div>

      <?php if (!empty($tags)): ?>
        <div class="gdy-tags" aria-label="الوسوم">
          <?php foreach ($tags as $__t):
            $__ts = (string)($__t['slug'] ?? '');
            $__tn = (string)($__t['name'] ?? '');
            if ($__ts === '' || $__tn === '') continue;
            $__th = rtrim($baseUrl,'/') . '/tag/' . rawurlencode($__ts);
          ?>
            <a class="gdy-tag" href="<?php echo h($__th) ?>">#<?php echo h($__tn) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>


      <div class="gdy-actions" role="toolbar" aria-label="أدوات التقرير">
        <button class="gdy-act" type="button" id="gdyCopyLink">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#copy"></use></svg>نسخ الرابط
        </button>
        <button class="gdy-act" type="button" id="gdyShare">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#share"></use></svg>مشاركة
        </button>
        <button class="gdy-act" type="button" id="gdyBookmark"
                data-news-id="<?php echo (int)($post['id'] ?? 0) ?>"
                data-title="<?php echo h((string)($post['title'] ?? '')) ?>"
                data-image="<?php echo h((string)($coverUrl ?? '')) ?>"
                data-url="<?php echo h((string)($newsUrl ?? '')) ?>">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#bookmark"></use></svg><span class="gdy-bm-text">حفظ</span>
        </button>

        <button class="gdy-act" type="button" id="gdyPrint">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#printer"></use></svg>طباعة
        </button>
        <button class="gdy-act" type="button" id="gdyPdf">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#file-pdf"></use></svg>PDF
        </button>
        <button class="gdy-act" type="button" id="gdyReadingMode">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg>وضع قراءة
        </button>
        <button class="gdy-act gdy-act-icon" type="button" id="gdyFontInc" title="تكبير الخط" aria-label="تكبير الخط"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#plus"></use></svg></button>
        <button class="gdy-act gdy-act-icon" type="button" id="gdyFontDec" title="تصغير الخط" aria-label="تصغير الخط"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#minus"></use></svg></button>
        <button class="gdy-act" type="button" id="gdyQrToggle" aria-label="QR" title="QR">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg>
        </button>

        <a class="gdy-act" target="_blank" rel="noopener"
           href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($newsUrl) ?>">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#facebook"></use></svg>فيسبوك
        </a>
        <a class="gdy-act" target="_blank" rel="noopener"
           href="https://x.com/intent/post?url=<?php echo urlencode($newsUrl) ?>">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#x"></use></svg>X
        </a>
        <a class="gdy-act" target="_blank" rel="noopener"
           href="https://wa.me/?text=<?php echo urlencode($newsUrl) ?>">
          <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#whatsapp"></use></svg>واتساب
        </a>

        <?php if ($aiSummary !== ''): ?>
          <button class="gdy-act secondary gdy-ai-toggle" type="button" id="gdyAiToggle" aria-expanded="false" aria-controls="gdyAiBox" data-mode="<?php echo h($aiSummaryMode) ?>">
            <?php echo h($aiBtnLabel) ?>
            <?php if ($aiBtnNote !== ''): ?><span class="gdy-badge"><?php echo h($aiBtnNote) ?></span><?php endif; ?>
          </button>
        <?php endif; ?>
      </div>

      <?php if ($aiSummary !== ''): ?>
        <div class="gdy-ai-box" id="gdyAiBox" hidden>
          <strong><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> ملخص المحتوى</strong>
          <div style="margin-top:10px; line-height:1.85;"><?php echo $aiSummary ?></div>
          <div class="gdy-ai-note">ملاحظة: هذا الملخص تم إنشاؤه آلياً، يُفضّل مراجعة النص الأصلي للتفاصيل.</div>
        </div>
      <?php endif; ?>
    </div>

    <div class="gdy-report-shell gdy-shell" style="margin-top:16px;">
      <section>
        <article class="gdy-card">
          <div class="gdy-article">
            <div class="gdy-article-cover">
              <?php if (!empty($coverUrl)): ?>
                <img src="<?php echo h($coverUrl) ?>" alt="<?php echo h($title) ?>" loading="eager" fetchpriority="high" decoding="async"
                     data-gdy-hide-onerror="1" data-gdy-hide-parent-class="gdy-cover-empty">
              <?php else: ?>
                <div class="gdy-cover-placeholder" aria-hidden="true"></div>
              <?php endif; ?>
            </div>

            <?php if (!empty($paywallBoxHtml)): ?>
              <?php echo $paywallBoxHtml ?>
            <?php endif; ?>

            <div class="gdy-article-body" id="gdyArticleBody">
              <?php echo $body ?>
            </div>

            <?php if (!empty($tags) && is_array($tags)): ?>
              <div style="margin-top:16px; display:flex; flex-wrap:wrap; gap:8px;">
                <?php foreach ($tags as $t): ?>
                  <?php $tn = (string)($t['name'] ?? $t['title'] ?? ''); $ts = (string)($t['slug'] ?? ''); ?>
                  <?php if ($tn !== ''): ?>
                    <a class="gdy-pill" style="text-decoration:none; color:#0f172a; background:#fff;"
                       href="<?php echo h($baseUrl) ?>/tag/<?php echo rawurlencode($ts !== '' ? $ts : $tn) ?>">
                      <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg><?php echo h($tn) ?>
                    </a>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </article>
      </section>

      <?php
        $newsId = (int)($post['id'] ?? 0);
        $ttsSource = ($gdyBodyForTts !== null) ? $gdyBodyForTts : $body;
        $ttsText = trim(gdy_regex_replace('~\s+~u',' ', html_entity_decode(strip_tags((string)$ttsSource), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
      ?>

      <section class="gdy-extras-wrap" aria-label="ميزات المقال الإضافية">
        <div class="gdy-extras-grid">
          <div class="gdy-extras-card">
            <div class="gdy-extras-head">
              <div class="gdy-extras-title"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> <?php echo h(__('الاستماع للمقال')) ?></div>
            </div>

            <div id="gdy-tts" class="gdy-tts" data-news-id="<?php echo (int)$newsId ?>">
              <button type="button" id="gdy-tts-play" class="gdy-tts-btn"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> <?php echo h(__('استماع')) ?></button>
              <button type="button" id="gdy-tts-stop" class="gdy-tts-btn"><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#toggle"></use></svg> <?php echo h(__('إيقاف')) ?></button>

              <label class="gdy-tts-rate">
                <span><?php echo h(__('السرعة')) ?></span>
                <input id="gdy-tts-rate" type="range" min="0.7" max="1.3" step="0.1" value="1">
              </label>

              <button type="button" id="gdy-tts-download" class="gdy-tts-btn" title="<?php echo h(__('تحميل الصوت')) ?>">
                <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> <?php echo h(__('تحميل')) ?>
              </button>

              <div id="gdy-tts-text" style="display:none;"><?php echo h($ttsText) ?></div>
            </div>
          </div>

          <div class="gdy-extras-card">
            <div id="gdy-reactions" data-news-id="<?php echo (int)$newsId ?>"></div>
          </div>
        </div>
      </section>

      <?php
        // بعد عرض المحتوى: اترك التعليقات/أسئلة القرّاء للإضافات عبر Hook واحد فقط
        $newsIdHook = (int)($newsId ?? ($postId ?? (($post['id'] ?? 0))));
        $newsArr = is_array($post ?? null) ? $post : [];
        if (function_exists('g_do_hook')) {
            g_do_hook('news.after_content', $newsIdHook, $newsArr);
        }
      ?>
    </div>

    <aside class="gdy-right gdy-sidebar">
      <div class="gdy-card">
        <div class="gdy-card-h">
          <strong><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> فهرس المحتوى</strong>
          <span style="color:#64748b;font-size:.78rem;">قفز سريع</span>
        </div>
        <div class="gdy-card-b gdy-toc">
          <?php if (!empty($toc)): ?>
            <?php foreach ($toc as $item): ?>
              <div class="<?php echo $item['level'] === 3 ? 'lv3' : 'lv2' ?>">
                <a href="#<?php echo h($item['id']) ?>"><?php echo h($item['text']) ?></a>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="color:#64748b;font-size:.9rem;">لا توجد عناوين داخل المحتوى لعرض فهرس.</div>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($related) && is_array($related)): ?>
        <div class="gdy-card" style="margin-bottom:14px;">
          <div class="gdy-card-h"><strong><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#news"></use></svg> تقارير ذات صلة</strong></div>
          <div class="gdy-card-b gdy-side-list">
            <?php foreach ($related as $r): ?>
              <?php
                $rid = (int)($r['id'] ?? 0);
                $rt = (string)($r['title'] ?? '');
                $rd = (string)($r['published_at'] ?? ($r['created_at'] ?? ''));
                $rurl = $rid > 0 ? ($baseUrl . '/news/id/' . $rid) : '#';
              ?>
              <a class="gdy-side-item" href="<?php echo h($rurl) ?>">
                <div class="t"><?php echo h($rt) ?></div>
                <div class="m">
                  <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg>
                  <span><?php echo $rd ? h(date('Y/m/d', strtotime($rd))) : '' ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($mostReadNews) && is_array($mostReadNews)): ?>
        <div class="gdy-card">
          <div class="gdy-card-h"><strong><svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="#more-h"></use></svg> الأكثر قراءة</strong></div>
          <div class="gdy-card-b gdy-side-list">
            <?php foreach ($mostReadNews as $r): ?>
              <?php
                $rid = (int)($r['id'] ?? 0);
                $rt = (string)($r['title'] ?? '');
                $rurl = $rid > 0 ? ($baseUrl . '/news/id/' . $rid) : '#';
              ?>
              <a class="gdy-side-item" href="<?php echo h($rurl) ?>">
                <div class="t"><?php echo h($rt) ?></div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</main>

<!-- News extras (TTS / Poll / Translate / Reactions / Q&A) -->
<?php
if (!defined('GDY_TPL_WRAPPED') && is_file($footer)) {
    require $footer;
}
?>