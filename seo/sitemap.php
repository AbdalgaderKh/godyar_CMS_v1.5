<?php

declare(strict_types=1);

// SEO sitemap generator (dynamic) with light caching.
// Routes: /sitemap.xml (see app.php)

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');

$cacheFile = __DIR__ . '/../cache/sitemap.xml';
$cacheTtl  = 600; // 10 minutes

if (is_file($cacheFile) && (time() - (int)@filemtime($cacheFile)) < $cacheTtl) {
    readfile($cacheFile);
    exit;
}

$base = rtrim((string)base_url(), '/');
if ($base === '') {
    // Fallback (best-effort)
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $host !== '' ? ($scheme . '://' . $host) : '';
}

$urls = [];
$add = static function(string $loc, ?string $lastmod = null, string $changefreq = 'daily', string $priority = '0.6') use (&$urls): void {
    $loc = trim($loc);
    if ($loc === '') return;
    $urls[] = [
        'loc' => $loc,
        'lastmod' => $lastmod,
        'changefreq' => $changefreq,
        'priority' => $priority,
    ];
};

$add(($base ?: '') . '/', date('c'), 'hourly', '1.0');

// Try DB-powered URLs (news/pages/categories)
try {
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        /** @var PDO $pdo */
        $pdo = $GLOBALS['pdo'];

        // Published pages
        try {
            $stmt = $pdo->query("SELECT slug, COALESCE(updated_at, created_at) AS lm FROM pages WHERE COALESCE(status,'published')='published' ORDER BY lm DESC LIMIT 5000");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $slug = (string)($row['slug'] ?? '');
                if ($slug === '' || $slug === 'home') continue;
                $lm = (string)($row['lm'] ?? '');
                $add(($base ?: '') . '/page/' . rawurlencode($slug), $lm ? date('c', strtotime($lm)) : null, 'weekly', '0.7');
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Categories
        try {
            $stmt = $pdo->query("SELECT slug, COALESCE(updated_at, created_at) AS lm FROM categories ORDER BY lm DESC LIMIT 5000");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $slug = (string)($row['slug'] ?? '');
                if ($slug === '') continue;
                $lm = (string)($row['lm'] ?? '');
                $add(($base ?: '') . '/category/' . rawurlencode($slug), $lm ? date('c', strtotime($lm)) : null, 'weekly', '0.7');
            }
        } catch (Throwable $e) {
            // ignore
        }

        // News (published)
        try {
            $stmt = $pdo->query("SELECT slug, COALESCE(updated_at, published_at, created_at) AS lm FROM news WHERE COALESCE(status,'published')='published' ORDER BY lm DESC LIMIT 20000");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $slug = (string)($row['slug'] ?? '');
                if ($slug === '') continue;
                $lm = (string)($row['lm'] ?? '');
                $add(($base ?: '') . '/news/' . rawurlencode($slug), $lm ? date('c', strtotime($lm)) : null, 'daily', '0.8');
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
} catch (Throwable $e) {
    // ignore
}

// XML output
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $u) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($u['loc'], ENT_QUOTES | ENT_XML1, 'UTF-8') . "</loc>\n";
    if (!empty($u['lastmod'])) {
        $xml .= "    <lastmod>" . htmlspecialchars((string)$u['lastmod'], ENT_QUOTES | ENT_XML1, 'UTF-8') . "</lastmod>\n";
    }
    $xml .= "    <changefreq>" . htmlspecialchars($u['changefreq'], ENT_QUOTES | ENT_XML1, 'UTF-8') . "</changefreq>\n";
    $xml .= "    <priority>" . htmlspecialchars($u['priority'], ENT_QUOTES | ENT_XML1, 'UTF-8') . "</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= "</urlset>\n";

@file_put_contents($cacheFile, $xml);

echo $xml;
