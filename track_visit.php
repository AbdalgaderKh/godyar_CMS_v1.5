<?php
/**
 * Godyar CMS — track_visit.php
 * ------------------------------------------------------------
 * Endpoint لتسجيل الزيارات/المشاهدات (AJAX-safe).
 *
 * لماذا هذا الملف؟
 * - بعض الصفحات قد تكون مخزنة (cache) أو يتم عرضها بدون تحديث عدّاد المشاهدات.
 * - هذا endpoint يعمل بشكل مستقل ويسجل الزيارة في جدول visits (مع توافق الإصدارات).
 *
 * الطلب (POST):
 * - news_id | post_id | id : رقم الخبر
 * - page (اختياري): مسار الصفحة (مثال: /news/id/123)
 * - source (اختياري): direct|ref|social|search
 */

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Boot the project (DB + sessions + helpers)
$bootstrap = __DIR__ . '/includes/bootstrap.php';
if (!is_file($bootstrap)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'bootstrap_missing'], JSON_UNESCAPED_UNICODE);
    exit;
}
require_once $bootstrap;

if (session_status() !== PHP_SESSION_ACTIVE && function_exists('gdy_session_start')) {
    gdy_session_start();
}

// Best-effort: skip tracking for admins (common session keys)
$adminKeys = ['admin_id', 'is_admin', 'user_type', 'gdy_admin', 'gdy_is_admin', 'godyar_admin_id'];
foreach ($adminKeys as $k) {
    if (!empty($_SESSION[$k])) {
        echo json_encode(['ok' => true, 'skipped' => 'admin_session'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$newsId = 0;
foreach (['news_id', 'post_id', 'id'] as $key) {
    if (isset($_POST[$key]) && is_scalar($_POST[$key]) && ctype_digit((string)$_POST[$key])) {
        $newsId = (int)$_POST[$key];
        break;
    }
}
if ($newsId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_news_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$page = '';
if (isset($_POST['page']) && is_scalar($_POST['page'])) {
    $page = trim((string)$_POST['page']);
}
if ($page === '') {
    // fallback: use referer path if present
    $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
    $refPath = parse_url($ref, PHP_URL_PATH);
    $page = is_string($refPath) && $refPath !== '' ? $refPath : ('/news/id/' . $newsId);
}
// normalize & limit
if ($page !== '' && $page[0] !== '/') {
    $page = '/' . $page;
}
$page = substr($page, 0, 500);

$source = '';
if (isset($_POST['source']) && is_scalar($_POST['source'])) {
    $source = strtolower(trim((string)$_POST['source']));
}
$allowedSources = ['direct', 'ref', 'social', 'search'];
if (!in_array($source, $allowedSources, true)) {
    $source = 'direct';
}

$ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64);
$ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
$ref = substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 255);

/** @var PDO|null $pdo */
$pdo = function_exists('gdy_pdo_safe') ? gdy_pdo_safe() : ($GLOBALS['pdo'] ?? null);
if (!($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'pdo_not_available'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Detect visits table schema (support multiple historical variants)
function gdy_table_columns(PDO $pdo, string $table): array
{
    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`');
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $cols = [];
        foreach ($rows as $r) {
            if (!empty($r['Field'])) {
                $cols[] = (string)$r['Field'];
            }
        }
        return $cols;
    } catch (Throwable $e) {
        return [];
    }
}

$cols = gdy_table_columns($pdo, 'visits');
if (!$cols) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'visits_table_missing'], JSON_UNESCAPED_UNICODE);
    exit;
}
$has = array_fill_keys($cols, true);

$fields = [];
$params = [];

// page
if (isset($has['page'])) {
    $fields[] = '`page`';
    $params[':page'] = $page;
}

// news id
if (isset($has['news_id'])) {
    $fields[] = '`news_id`';
    $params[':news_id'] = $newsId;
} elseif (isset($has['post_id'])) {
    $fields[] = '`post_id`';
    $params[':post_id'] = $newsId;
}

// source
if (isset($has['source'])) {
    $fields[] = '`source`';
    $params[':source'] = $source;
}

// ip
if (isset($has['ip_address'])) {
    $fields[] = '`ip_address`';
    $params[':ip'] = $ip;
} elseif (isset($has['user_ip'])) {
    $fields[] = '`user_ip`';
    $params[':ip'] = $ip;
}

// ua
if (isset($has['user_agent'])) {
    $fields[] = '`user_agent`';
    $params[':ua'] = $ua;
}

// referrer
if (isset($has['referrer'])) {
    $fields[] = '`referrer`';
    $params[':ref'] = $ref;
}

// timestamps (use NOW() only if the column exists and has no default is unknown).
// Usually schema provides DEFAULT CURRENT_TIMESTAMP, so we omit it.

if (count($fields) === 0) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'visits_schema_unsupported'], JSON_UNESCAPED_UNICODE);
    exit;
}

$placeholders = [];
foreach ($fields as $f) {
    $key = trim($f, '`');
    if ($key === 'page') $placeholders[] = ':page';
    elseif ($key === 'news_id') $placeholders[] = ':news_id';
    elseif ($key === 'post_id') $placeholders[] = ':post_id';
    elseif ($key === 'source') $placeholders[] = ':source';
    elseif ($key === 'ip_address' || $key === 'user_ip') $placeholders[] = ':ip';
    elseif ($key === 'user_agent') $placeholders[] = ':ua';
    elseif ($key === 'referrer') $placeholders[] = ':ref';
}

$sql = 'INSERT INTO `visits` (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';

try {
    $st = $pdo->prepare($sql);
    $st->execute($params);

    // Optional: increment views if column exists
    try {
        if (isset($has['news_id']) && gdy_db_stmt_table_exists($pdo, 'news')) {
            // "views" column exists in newer versions (INT DEFAULT 0)
            $pdo->exec('UPDATE `news` SET `views` = COALESCE(`views`,0) + 1 WHERE `id` = ' . (int)$newsId . ' LIMIT 1');
        }
    } catch (Throwable $e) {
        // ignore
    }

    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[track_visit] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'insert_failed'], JSON_UNESCAPED_UNICODE);
}
