<?php
/**
 * Publish scheduled news items (publish_at <= NOW()).
 *
 * Safe for:
 * - Web (include/run)
 * - CLI (php tools/publish_scheduled.php)
 *
 * Optional protection for web usage:
 * - Provide ?key=... and set CRON_KEY in .env
 */
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$cronKey = getenv('CRON_KEY') ?: '';
if (PHP_SAPI !== 'cli' && $cronKey !== '') {
    $key = (string)($_GET['key'] ?? '');
    if (!hash_equals($cronKey, $key)) {
        http_response_code(403);
        echo "Forbidden\n";
        exit;
    }
}

try {
    /** @var PDO $pdo */
    $pdo = gdy_pdo();

    // Only publish items that are not deleted and are scheduled.
    // Many installs use: status='scheduled' or is_published=0 + publish_at not null.
    // We'll handle both patterns safely.
    $now = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

    // 1) If status column exists and 'scheduled' is used
    $hasStatus = false;
    $hasPublishAt = false;

    $cols = $pdo->query("SHOW COLUMNS FROM news")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        $name = strtolower($c['Field'] ?? '');
        if ($name === 'status') $hasStatus = true;
        if ($name === 'publish_at') $hasPublishAt = true;
    }
    if (!$hasPublishAt) {
        echo "No publish_at column; nothing to do.\n";
        exit;
    }

    $updated = 0;

    if ($hasStatus) {
        $stmt = $pdo->prepare("
            UPDATE news
            SET status='published', is_published=1, published_at=COALESCE(published_at, publish_at)
            WHERE deleted_at IS NULL
              AND publish_at IS NOT NULL
              AND publish_at <= ?
              AND (status='scheduled' OR is_published=0)
        ");
        $stmt->execute([$now]);
        $updated = $stmt->rowCount();
    } else {
        $stmt = $pdo->prepare("
            UPDATE news
            SET is_published=1, published_at=COALESCE(published_at, publish_at)
            WHERE deleted_at IS NULL
              AND publish_at IS NOT NULL
              AND publish_at <= ?
              AND is_published=0
        ");
        $stmt->execute([$now]);
        $updated = $stmt->rowCount();
    }

    echo "Published: {$updated}\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "ERROR: " . $e->getMessage() . "\n";
}
