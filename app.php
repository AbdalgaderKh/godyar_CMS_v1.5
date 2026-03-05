<?php


/**
 * app . php — Front Controller (routing)
 *
 * • يعمل مع Apache/Nginx عبر rewrite (انظر . htaccess و deploy/nginx . conf . snippet)
 * • يوجّه المسارات إلى الكنترولرات الموجودة في frontend/controllers
 * • يحافظ على توافق الصفحات القديمة قدر الإمكان .
 *
 * IMPORTANT (Language):
 *-This file expects: public_html/language_prefix_router . php (R4) to be present
 * and included BEFORE bootstrap .
 *-language_prefix_router . php must be BOM-free and must NOT output anything .
 */

// Step 17: Class NewsController + Services extraction
use App\Core\Router;
use App\Core\FrontendRenderer;
use App\Http\Presenters\SeoPresenter;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LegacyIncludeController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\Api\NewsExtrasController;

// ---------------------------------------------------------
// ✅ Language prefix router (NO output, NO redirects) BEFORE bootstrap
// ---------------------------------------------------------
// ---------------------------------------------------------
// ✅ Language prefix router (NO output, NO redirects) BEFORE bootstrap
// ---------------------------------------------------------
$lpFile = __DIR__ . '/language_prefix_router.php';
if (is_file($lpFile)) {
    require_once $lpFile;
}

// Now load bootstrap (it loads lang_prefix . php/lang . php/translation . php)
require_once __DIR__ . '/includes/bootstrap.php';

// Allow installer to run even before DB is configured
$__uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
if (preg_match('~^/install(?:/|$)~', $__uri)) {
    require_once __DIR__ . '/install/index.php';
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    gdy_session_start();
}

// ---------------------------------------------------------
// Fallback routes (defense-in-depth)
// If rewrite rules fail or are bypassed, keep common endpoints working .
// ---------------------------------------------------------
$__path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__path = trim($__path, '/');

// Normalize for subdirectory installs: strip base prefix if present (e . g . /godyar)
if (function_exists('godyar_route_base_prefix')) {
    $__bp = trim((string)godyar_route_base_prefix(), '/');
    if ($__bp !== '') {
        if ($__path === $__bp) { $__path = ''; }
        elseif (str_starts_with($__path, $__bp . '/')) { $__path = substr($__path, strlen($__bp) + 1); }
    }
}

// Support optional language prefixes: /ar, /en, /fr
if ($__path !== '') {
    $parts = explode('/', $__path);
    if (count($parts) >= 1 && in_array($parts[0], ['ar', 'en', 'fr'], true)) {
        array_shift($parts);
        $__path = implode('/', $parts);
    }
}

// Normalize legacy direct PHP entrypoints: /admin/login.php -> /admin/login
if ($__path !== '' && str_ends_with($__path, '.php')) {
    $__path = substr($__path, 0, -4);
}

// Public auth routes (avoid requiring missing legacy entrypoints on shared hosting)
if (in_array($__path, ['login', 'register', 'logout'], true)) {
    $map = [
        'login'    => __DIR__ . '/frontend/controllers/Auth/LoginController.php',
        'register' => __DIR__ . '/frontend/controllers/Auth/RegisterController.php',
        'logout'   => __DIR__ . '/frontend/controllers/Auth/LogoutController.php',
    ];
    $target = $map[$__path] ?? '';
    if ($target !== '' && is_file($target)) {
        require $target;
        exit;
    }

    // Fallback (shouldn't happen): show 404 rather than fatal.
    http_response_code(404);
    echo 'Not Found';
    exit;
}

/**
 * Cron endpoints (optional).
 * NOTE: Protect with CRON_KEY in .env and pass ?key=... when calling over web.
 */
if ($__path === 'cron/publish-scheduled') {
    require __DIR__ . '/tools/publish_scheduled.php';
    exit;
}

if ($__path === 'admin/login') {
    require __DIR__ . '/admin/login.php';
    exit;
}


// ---------------------------------------------------------
// Input helpers (reduce direct superglobal access & harden)
// ---------------------------------------------------------
if (!function_exists('gdy_qs_int')) {
    function gdy_qs_int(string $key, int $default = 0): int {
        $v = $_GET[$key] ?? null;
        if ($v === null) return $default;
        $s = is_scalar($v) ? (string)$v : '';
        return (ctype_digit($s) ? (int)$s : $default);
    }
}
if (!function_exists('gdy_qs_str')) {
    function gdy_qs_str(string $key, string $default = ''): string {
        $v = $_GET[$key] ?? null;
        if ($v === null) return $default;
        return is_scalar($v) ? trim((string)$v) : $default;
    }
}
if (!function_exists('gdy_allowlist')) {
    function gdy_allowlist(string $value, array $allowed, string $default): string {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
if (!function_exists('gdy_slug_clean')) {
    function gdy_slug_clean(string $slug, int $maxLen = 190): string {
        $slug = trim($slug);
        if ($slug === '') return '';
        // allow letters/numbers/hyphen/underscore only (unicode-safe)
        $slug = preg_replace('/[^\pL\pN\-_]/u', '', $slug) ?? '';
        if (strlen($slug) > $maxLen) $slug = substr($slug, 0, $maxLen);
        return $slug;
    }
}

if (!function_exists('godyar_route_base_prefix')) {
    /**
     * يحاول تحديد Prefix المشروع لو كان داخل مجلد فرعي (مثلاً /godyar)
     * بناءً على SCRIPT_NAME الخاص بـ app . php .
     */
    function godyar_route_base_prefix(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname((string)$script));
        if ($dir === '/' || $dir === '.' || $dir === '\\') {
            return '';
        }
        return rtrim($dir, '/');
    }
}

if (!function_exists('godyar_request_path')) {
    function godyar_request_path(): string
    {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') $path = '/';
        return $path;
    }
}

if (!function_exists('godyar_render_404')) {
    function godyar_render_404(): void
    {
        http_response_code(404);

        $siteTitle = '404-الصفحة غير موجودة';
        $siteDescription = 'الصفحة التي طلبتها غير موجودة.';

        $headerFile = __DIR__ . '/frontend/templates/header.php';
        if (is_file($headerFile)) {
            require __DIR__ . '/frontend/templates/header.php';
        }

        echo '<main class="container my-5">';
        echo '<h1 style="margin-bottom:12px;">الصفحة غير موجودة (404)</h1>';
        echo '<p>قد يكون الرابط غير صحيح أو تم نقل الصفحة.</p>';
        $home = rtrim((string)($GLOBALS['baseUrl'] ?? ''), '/');
        echo '<p><a href="' . htmlspecialchars($home ?: '/', ENT_QUOTES, 'UTF-8') . '">العودة للصفحة الرئيسية</a></p>';
        echo '</main>';

        $footerFile = __DIR__ . '/frontend/templates/footer.php';
        if (is_file($footerFile)) {
            require __DIR__ . '/frontend/templates/footer.php';
        }

        exit;
    }
}

// ---------------------------------------------------------
// Normalize path (remove base prefix if any)
// ---------------------------------------------------------
$basePrefix = godyar_route_base_prefix(); // e . g . /godyar
$requestPath = godyar_request_path(); // e . g . /godyar/news/slug

if ($basePrefix !== '' && str_starts_with($requestPath, $basePrefix . '/')) {
    $requestPath = substr($requestPath, strlen($basePrefix));
}
$requestPath = '/' . ltrim($requestPath, '/');

// ---------------------------------------
// Strip optional language prefix from request path: /ar, /en, /fr
// This ensures /ar/ (and similar) routes to the homepage and other routes work.
// ---------------------------------------
if (preg_match('#^/(ar|en|fr)(?:/|$)#i', $requestPath, $__m)) {
    $lp = strtolower($__m[1]);
    if (empty($_GET['lang'])) { $_GET['lang'] = $lp; }
    $requestPath = substr($requestPath, 1 + strlen($lp));
    if ($requestPath === '' ) { $requestPath = '/'; }
    if ($requestPath[0] !== '/') { $requestPath = '/' . $requestPath; }
}


// ---------------------------------------------------------
// 🔒 Legacy query param hardening: block LFI/Traversal via ?page = // ---------------------------------------------------------
if ($requestPath === '/' && isset($_GET['page'])) {
    $legacyPage = trim((string)$_GET['page']);

    if ($legacyPage === '') {
        unset($_GET['page']);
    } elseif (ctype_digit($legacyPage)) {
        $_GET['page'] = (int)$legacyPage;
    } else {
        $slug = rawurldecode($legacyPage);

        if (!preg_match('/^[\p{L}\p{N}_-]{1,80}$/u', $slug)) {
            godyar_render_404();
            exit;
        }

        $exists = false;
        try {
            if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
                $stmt = $GLOBALS['pdo']->prepare("SELECT 1 FROM pages WHERE slug = :slug LIMIT 1");
                $stmt->execute([':slug' => $slug]);
                $exists = (bool)$stmt->fetchColumn();
            }
        } catch (\Throwable $e) {
            $exists = true;
        }

        if ($exists) {
            $prefix = rtrim($basePrefix, '/');
            header('Location: ' . ($prefix === '' ? '' : $prefix) . '/page/' . rawurlencode($slug), true, 301);
            exit;
        }

        godyar_render_404();
        exit;
    }
}

// ---------------------------------------------------------
// ---------------------------------------------------------
// Legacy filename normalization (dash/underscore)
// ---------------------------------------------------------
if ($requestPath === "/ad-click.php" || $requestPath === "/ad-click") {
    $qs = (string)($_SERVER["QUERY_STRING"] ?? "");
    $target = "/ad_click.php" . ($qs !== "" ? ("?" . $qs) : "");
    header("Location: " . $target, true, 301);
    exit;
}

// Social OAuth endpoints (front-end)
// ---------------------------------------------------------
if ($requestPath === '/oauth/github') { $__gdy_oauth_file = __DIR__ . '/oauth/github.php'; if (is_file($__gdy_oauth_file)) { require $__gdy_oauth_file; } else { http_response_code(404); echo 'OAuth provider not installed'; } exit; }
if ($requestPath === '/oauth/github/callback') { $__gdy_oauth_file = __DIR__ . '/oauth/github_callback.php'; if (is_file($__gdy_oauth_file)) { require $__gdy_oauth_file; } else { http_response_code(404); echo 'OAuth provider not installed'; } exit; }

if ($requestPath === '/oauth/google') { require __DIR__ . '/oauth/google.php'; exit; }
if ($requestPath === '/oauth/google/callback') { require __DIR__ . '/oauth/google_callback.php'; exit; }

if ($requestPath === '/oauth/facebook') { require __DIR__ . '/oauth/facebook.php'; exit; }
if ($requestPath === '/oauth/facebook/callback') { require __DIR__ . '/oauth/facebook_callback.php'; exit; }

// ---------------------------------------------------------
// Homepage support
// (After language_prefix_router . php strips prefix: /ar /en /fr become /)
// ---------------------------------------------------------
if ($requestPath === '/' || $requestPath === '') {
    // IMPORTANT: never require the front controller (index.php) here.
    // Doing so creates an infinite recursion when index.php itself requires app.php.
    // The homepage is rendered by the frontend entry file.
    require __DIR__ . '/frontend/index.php';
    exit;
}

// ---------------------------------------------------------
// Legacy endpoints handling (when legacy PHP files are removed)
// ---------------------------------------------------------
if (in_array($requestPath, ['/article.php', '/category.php', '/page.php', '/archive.php', '/trending.php'], true)) {
    $base = rtrim(godyar_route_base_prefix(), '/');
    $qs = (string)($_SERVER['QUERY_STRING'] ?? '');

    if ($requestPath === '/article.php') {
        $preview = isset($_GET['preview']) && (string)$_GET['preview'] === '1';

        if ($preview) {
            if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
                $id = (int)$_GET['id'];
                header('Location: ' . $base . '/preview/news/' . $id, true, 302);
                exit;
            }
            if (!empty($_GET['slug'])) {
        $slug = gdy_slug_clean((string)$_GET['slug']);
                header('Location: ' . $base . '/news/' . rawurlencode($slug) . '?preview=1', true, 302);
                exit;
            }
            http_response_code(410);
            echo 'Gone';
            exit;
        }

        if (!empty($_GET['slug'])) {
        $slug = gdy_slug_clean((string)$_GET['slug']);
            header('Location: ' . $base . '/news/' . rawurlencode($slug) . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
            exit;
        }

        if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
            $id = (int)$_GET['id'];
            header('Location: ' . $base . '/news/id/' . $id . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
            exit;
        }

        http_response_code(410);
        echo 'Gone';
        exit;
    }

    if ($requestPath === '/category.php') {
        if (!empty($_GET['slug'])) {
        $slug = gdy_slug_clean((string)$_GET['slug']);
            if (!empty($_GET['page']) && ctype_digit((string)$_GET['page'])) {
                $page = (int)$_GET['page'];
                header('Location: ' . $base . '/category/' . rawurlencode($slug) . '/page/' . $page . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
                exit;
            }
            header('Location: ' . $base . '/category/' . rawurlencode($slug) . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
            exit;
        }
        if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
            $id = (int)$_GET['id'];
            header('Location: ' . $base . '/category/id/' . $id . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
            exit;
        }

        http_response_code(410);
        echo 'Gone';
        exit;
    }

    if ($requestPath === '/page.php') {
        if (!empty($_GET['slug'])) {
        $slug = gdy_slug_clean((string)$_GET['slug']);
            header('Location: ' . $base . '/page/' . rawurlencode($slug) . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
            exit;
        }
        http_response_code(410);
        echo 'Gone';
        exit;
    }

    if ($requestPath === '/archive.php') {
        header('Location: ' . $base . '/archive' . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
        exit;
    }

    if ($requestPath === '/trending.php') {
        header('Location: ' . $base . '/trending' . ($qs !== '' ? ('?' . $qs) : ''), true, 301);
        exit;
    }
}

// ---------------------------------------------------------
// Routing table (مرحلة 1)
// ---------------------------------------------------------

// Shared instances
$container = $GLOBALS['container'] ?? null;
if (($container instanceof \Godyar\Container) === false) {
    $pdoBoot = \Godyar\DB::pdoOrNull();
    if (!$pdoBoot instanceof \PDO) {
        // DB not available: show a friendly, CSP-safe setup page
        http_response_code(503);

        $base = function_exists('base_url') ? rtrim(base_url(), '/') : '';
        $cssCore = $base . '/assets/css/app-core.bundle.css?v=' . rawurlencode((string)time());
        $cssLayout = $base . '/assets/css/app-layout.bundle.css?v=' . rawurlencode((string)time());

        echo "<!doctype html><html lang=\"ar\" dir=\"rtl\"><head><meta charset=\"utf-8\">";
        echo "<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>Godyar CMS</title>";
        echo "<link rel=\"stylesheet\" href=\"" . htmlspecialchars($cssCore, ENT_QUOTES, 'UTF-8') . "\">";
        echo "<link rel=\"stylesheet\" href=\"" . htmlspecialchars($cssLayout, ENT_QUOTES, 'UTF-8') . "\">";
        echo "</head><body class=\"theme-default rtl\">";
        echo "<main class=\"gdy-main\" role=\"main\"><div class=\"container p-4\">";
        echo "<div class=\"alert alert-warning rounded-4\">";
        echo "<h2 class=\"h4 mb-2\">لم يتم الاتصال بقاعدة البيانات</h2>";
        echo "<p class=\"mb-0\">يرجى ضبط بيانات الاتصال داخل ملف <code>.env</code> ثم تشغيل المُثبت من هنا: <a href=\"/install/\">/install/</a></p>";
        echo "</div></div></main></body></html>";
        exit;
    }
    $container = new \Godyar\Container($pdoBoot);
    $GLOBALS['container'] = $container;
}

// Safe PDO reference for closures
$pdo = null;
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
    $pdo = $GLOBALS['pdo'];
} elseif (function_exists('gdy_pdo_safe')) {
    $tmp = gdy_pdo_safe();
    if ($tmp instanceof PDO) $pdo = $tmp;
}

$redirectController = new \App\Http\Controllers\RedirectController(
    $container->news(),
    $container->categories(),
    godyar_route_base_prefix()
);

$categoryController = new CategoryController(
    $container->categories(),
    godyar_route_base_prefix()
);

$newsController = new NewsController(
    $container->pdo(),
    $container->news(),
    $container->categories(),
    $container->tags(),
    $container->ads(),
    godyar_route_base_prefix()
);

$basePrefix = godyar_route_base_prefix();
$renderer = new FrontendRenderer(__DIR__, $basePrefix);
$seo = new SeoPresenter($basePrefix);

$tagController = new TagController($container->tags(), $renderer, $seo);
$archiveController = new ArchiveController($container->news(), $renderer, $seo);
$searchController = new SearchController($container->news(), $container->categories(), $seo, __DIR__, $basePrefix);

$topicController = new TopicController($container->tags(), $renderer, $seo, $container->pdo());
$extrasApi = new NewsExtrasController($container->pdo(), $container->news(), $container->tags(), $container->categories());

$legacy = new LegacyIncludeController(__DIR__);
$router = new Router();

// ---------------------------------------------------------
// Core auth routes (defense-in-depth)
// Keep /login working even if rewrite rules are bypassed .
// ---------------------------------------------------------
$router->get('#^/login/?$#', fn() => require __DIR__ . '/frontend/controllers/Auth/LoginController.php');
$router->post('#^/login/?$#', fn() => require __DIR__ . '/frontend/controllers/Auth/LoginController.php');
$router->get('#^/register/?$#', fn() => require __DIR__ . '/frontend/controllers/Auth/RegisterController.php');
$router->post('#^/register/?$#', fn() => require __DIR__ . '/frontend/controllers/Auth/RegisterController.php');
$router->get('#^/profile/?$#', fn() => require __DIR__ . '/profile.php');
$router->post('#^/profile/?$#', fn() => require __DIR__ . '/profile.php');
$router->get('#^/logout/?$#', fn() => require __DIR__ . '/logout.php');
$router->post('#^/logout/?$#', fn() => require __DIR__ . '/logout.php');
$router->get('#^/admin/login/?$#', fn() => require __DIR__ . '/admin/login.php');
$router->post('#^/admin/login/?$#', fn() => require __DIR__ . '/admin/login.php');

// SEO endpoints
$router->get('#^/sitemap\.xml$#', function () : void { require __DIR__ . '/seo/sitemap.php'; });
$router->get('#^/sitemap-news\.xml$#', function () : void { require __DIR__ . '/seo/sitemap_news.php'; });
$router->get('#^/rss\.xml$#', function () : void { require __DIR__ . '/seo/rss.php'; });
$router->get('#^/rss/category/([^/]+)\.xml$#', function (array $m) : void { $slug = rawurldecode((string)$m[1]); require __DIR__ . '/seo/rss_category.php'; });
$router->get('#^/rss/category/([^/]+)\.xml$#', function (array $m) : void { $slug = rawurldecode((string)$m[1]); require __DIR__ . '/seo/rss_category.php'; });
$router->get('#^/rss/tag/([^/]+)\.xml$#', function (array $m) : void { $slug = rawurldecode((string)$m[1]); require __DIR__ . '/seo/rss_tag.php'; });
$router->get('#^/rss/tag/([^/]+)\.xml$#', function (array $m) : void { $slug = rawurldecode((string)$m[1]); require __DIR__ . '/seo/rss_tag.php'; });

$router->get('#^/og/news/([0-9]+)\.png$#', function (array $m) : void {
    $id = (int)$m[1];
    require __DIR__ . '/og_news.php';
});

// /category/{slug}[/page/{n}]
$router->get('#^/category/([^/]+)/page/([0-9]+)/?$#', function (array $m) use ($categoryController): void {
    $sort = gdy_allowlist(gdy_qs_str('sort', 'latest'), ['latest','oldest','popular'], 'latest');
    $period = gdy_allowlist(gdy_qs_str('period', 'all'), ['all','day','week','month','year'], 'all');
    $categoryController->show(rawurldecode((string)$m[1]), (int)$m[2], $sort, $period);
});
$router->get('#^/category/([^/]+)/?$#', function (array $m) use ($categoryController): void {
    $sort = gdy_allowlist(gdy_qs_str('sort', 'latest'), ['latest','oldest','popular'], 'latest');
    $period = gdy_allowlist(gdy_qs_str('period', 'all'), ['all','day','week','month','year'], 'all');
    $categoryController->show(rawurldecode((string)$m[1]), 1, $sort, $period);
});

// /news/print/{id} — صفحة طباعة/ PDF (GDY v8)
$router->get('#^/news/print/([0-9]+)/?$#', function (array $m) use ($newsController): void { $newsController->print((int)$m[1]); });
$router->get('#^/news/pdf/([0-9]+)/?$#', function (array $m) use ($newsController): void { $newsController->print((int)$m[1]); });

// /news/id/{id}
$router->get('#^/news/id/([0-9]+)/?$#', function (array $m) use ($newsController): void {
    $id = (int)$m[1];
    $newsController->show((string)$id, false);
});

// /category/id/{id}
$router->get('#^/category/id/([0-9]+)/?$#', fn(array $m) => $redirectController->categoryIdToSlug((int)$m[1]));

// /preview/news/{id}
$router->get('#^/preview/news/([0-9]+)/?$#', fn(array $m) => $newsController->preview((int)$m[1]));

// /news/{slug} و /article/{slug}
$router->get('#^/(?:news|article)/([^/]+)/?$#', function (array $m) use ($container, $newsController): void {
    $slug = rawurldecode((string)$m[1]);
    $id = $container->news()->idBySlug($slug);
    if ($id !== null && $id > 0) {
        $prefix = rtrim(godyar_route_base_prefix(), '/');
        header('Location: ' . $prefix . '/news/id/' . $id, true, 301);
        exit;
    }
    $newsController->show($slug, false);
});

// /page/{slug}
$router->get('#^/page/([^/]+)/?$#', fn(array $m) => $legacy->include('frontend/controllers/PageController.php', [
    'slug' => rawurldecode((string)$m[1]),
]));

// /topic/{slug}
$router->get('#^/topic/([^/]+)/page/([0-9]+)/?$#', function (array $m) use ($topicController): void {
    $topicController->show(urldecode((string)$m[1]), (int)$m[2]);
});
$router->get('#^/topic/([^/]+)/?$#', function (array $m) use ($topicController): void {
    $topicController->show(urldecode((string)$m[1]), 1);
});

// /tag/{slug}
$router->get('#^/tag/([^/]+)/page/([0-9]+)/?$#', fn(array $m) => $tagController->show(rawurldecode((string)$m[1]), (int)$m[2]));
$router->get('#^/tag/([^/]+)/?$#', fn(array $m) => $tagController->show(gdy_slug_clean(rawurldecode((string)$m[1])), gdy_qs_int('page', 1)));

// /trending
$router->get('#^/trending/?$#', fn() => $legacy->include('frontend/views/trending.php'));

// Auth
$router->get('#^/login/?$#', fn() => require __DIR__ . '/frontend/controllers/Auth/LoginController.php');
$router->get('#^/register/?$#', fn() => require __DIR__ . '/frontend/controllers/Auth/RegisterController.php');
$router->get('#^/profile/?$#', fn() => $legacy->include('profile.php'));
$router->get('#^/logout/?$#', fn() => $legacy->include('logout.php'));
$router->get('#^/my/?$#', fn() => $legacy->include('my.php'));

// /categories
$router->get('#^/categories/?$#', fn() => $legacy->include('categories_list.php'));

// /saved
$router->get('#^/saved/?$#', fn() => $legacy->include('saved.php'));

// /archive
$router->get('#^/archive/page/([0-9]+)/?$#', fn(array $m) => $archiveController->index((int)$m[1]));
$router->get('#^/archive/([0-9]{4})/page/([0-9]+)/?$#', fn(array $m) => $archiveController->index((int)$m[2], (int)$m[1], null));
$router->get('#^/archive/([0-9]{4})/([0-9]{1,2})/page/([0-9]+)/?$#', fn(array $m) => $archiveController->index((int)$m[3], (int)$m[1], (int)$m[2]));
$router->get('#^/archive/([0-9]{4})/([0-9]{1,2})/?$#', fn(array $m) => $archiveController->index(1, (int)$m[1], (int)$m[2]));
$router->get('#^/archive/([0-9]{4})/?$#', fn(array $m) => $archiveController->index(1, (int)$m[1], null));
$router->get('#^/archive/?$#', fn() => $archiveController->index(gdy_qs_int('page', 1)));
// Alias routes: /news -> /archive (back-compat)
$router->get('#^/news/page/([0-9]+)/?$#', fn(array $m) => $archiveController->index((int)$m[1]));
$router->get('#^/news/([0-9]{4})/page/([0-9]+)/?$#', fn(array $m) => $archiveController->index((int)$m[2], (int)$m[1], null));
$router->get(
    '#^/news/([0-9]{4})/([0-9]{1,2})/page/([0-9]+)/?$#',
    fn(array $m) => $archiveController->index((int)$m[3], (int)$m[1], (int)$m[2])
);
$router->get(
    '#^/news/([0-9]{4})/([0-9]{1,2})/?$#',
    fn(array $m) => $archiveController->index(1, (int)$m[1], (int)$m[2])
);
$router->get('#^/news/([0-9]{4})/?$#', fn(array $m) => $archiveController->index(1, (int)$m[1], null));
$router->get('#^/news/?$#', fn() => $archiveController->index(gdy_qs_int('page', 1)));


// API
$router->get('#^/api/capabilities/?$#', fn() => $extrasApi->capabilities());

// Slider (Homepage hero) — public read-only
// Data source: admin/slider (table: slider)
$router->get('#^/api/slider/?$#', function () use ($pdo): void {
    header('Content-Type: application/json; charset=utf-8');

    if (($pdo instanceof PDO) === false) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'pdo_unavailable'], JSON_UNESCAPED_UNICODE);
        return;
    }

    try {
        $stmt = $pdo->query(
            "SELECT id, title, subtitle, image_path, link_url, is_active, sort_order, created_at\n" .
            "FROM slider\n" .
            "WHERE is_active = 1\n" .
            "ORDER BY sort_order ASC, id DESC\n" .
            "LIMIT 10"
        );
        $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'id'       => (int)($r['id'] ?? 0),
                'title'    => (string)($r['title'] ?? ''),
                'subtitle' => (string)($r['subtitle'] ?? ''),
                'image'    => (string)($r['image_path'] ?? ''),
                'url'      => (string)($r['link_url'] ?? ''),
                'order'    => (int)($r['sort_order'] ?? 0),
                'date'     => (string)($r['created_at'] ?? ''),
            ];
        }

        echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'slider_failed'], JSON_UNESCAPED_UNICODE);
    }
});

// bookmarks
$router->get('#^/api/bookmarks/list/?$#', fn() => $extrasApi->bookmarksList());
$router->get('#^/api/bookmarks/status/?$#', fn() => $extrasApi->bookmarkStatus());
$router->get('#^/api/bookmarks/toggle/?$#', fn() => $extrasApi->bookmarksToggle());
$router->get('#^/api/bookmarks/import/?$#', fn() => $extrasApi->bookmarksImport());

// reactions
$router->get('#^/api/news/reactions/?$#', fn() => $extrasApi->reactions());
$router->get('#^/api/news/react/?$#', fn() => $extrasApi->react());

// polls
$router->get('#^/api/news/poll/?$#', fn() => $extrasApi->poll());
$router->get('#^/api/news/poll/vote/?$#', fn() => $extrasApi->pollVote());

// Q&A (moved to plugins)
// Translation + TTS
$router->get('#^/api/news/tts/?$#', fn() => $extrasApi->tts());


$router->get('#^/api/news/pdf/?$#', fn() => $extrasApi->pdf());
// Search suggestions
$router->get('#^/api/search/suggest/?$#', fn() => $extrasApi->suggest());

// PWA helpers
$router->get('#^/api/latest/?$#', fn() => $extrasApi->latest());

// Push subscriptions (POST)
$router->post('#^/api/push/subscribe/?$#', fn() => $extrasApi->pushSubscribe());
$router->post('#^/api/push/unsubscribe/?$#', fn() => $extrasApi->pushUnsubscribe());

$router->get('#^/search/?$#', fn() => $searchController->index());

// Comments (POST)
$router->post('#^/comment/add/?$#', fn() => require __DIR__ . '/frontend/controllers/CommentAddController.php');

// /api/newsletter/subscribe (POST)
$router->post('#^/api/newsletter/subscribe/?$#', function () use ($pdo): void {
    if (($pdo instanceof PDO) === false) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'PDO غير متاح'], JSON_UNESCAPED_UNICODE);
        return;
    }

    if (!empty($_POST['csrf_token']) && function_exists('csrf_verify_or_die')) { csrf_verify_or_die(); }

    if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $email = '';
    if (isset($_POST['newsletter_email']) && is_scalar($_POST['newsletter_email']) && (string)$_POST['newsletter_email'] !== '') {
        $email = trim((string)$_POST['newsletter_email']);
    } else {
        $raw = (string)file_get_contents('php://input');
        if ($raw !== '') {
            $j = json_decode($raw, true);
            if (is_array($j) && !empty($j['newsletter_email'])) {
                $email = trim((string)$j['newsletter_email']);
            } elseif (is_array($j) && !empty($j['email'])) {
                $email = trim((string)$j['email']);
            }
        }
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'البريد الإلكتروني غير صحيح'], JSON_UNESCAPED_UNICODE);
        return;
    }

    try {
        if (function_exists('gdy_pdo_is_pgsql') && gdy_pdo_is_pgsql($pdo)) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                    id BIGSERIAL PRIMARY KEY,
                    email VARCHAR(190) NOT NULL UNIQUE,
                    status VARCHAR(30) NOT NULL DEFAULT 'active',
                    lang VARCHAR(10) NOT NULL DEFAULT 'ar',
                    ip VARCHAR(45) NULL,
                    ua VARCHAR(255) NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(190) NOT NULL UNIQUE,
                    status VARCHAR(30) NOT NULL DEFAULT 'active',
                    lang VARCHAR(10) NOT NULL DEFAULT 'ar',
                    ip VARCHAR(45) NULL,
                    ua VARCHAR(255) NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
            ");
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'خطأ في قاعدة البيانات'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $lang = '';
    if (!empty($_COOKIE['lang']) && is_scalar($_COOKIE['lang'])) {
        $lang = gdy_allowlist((string)$_COOKIE['lang'], ['ar','en','fr'], $lang);
    }
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    try {
        $now = date('Y-m-d H:i:s');
        gdy_db_upsert(
            $pdo,
            'newsletter_subscribers',
            [
                'email' => $email,
                'status' => 'active',
                'lang' => $lang,
                'ip' => $ip,
                'ua' => $ua,
                'updated_at' => $now,
            ],
            ['email'],
            ['status','lang','ip','ua','updated_at']
        );
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'تعذر حفظ الاشتراك'], JSON_UNESCAPED_UNICODE);
        return;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'message' => 'تم الاشتراك بنجاح ✅'], JSON_UNESCAPED_UNICODE);
});

if ($router->dispatch($requestPath)) {
    exit;
}

godyar_render_404();