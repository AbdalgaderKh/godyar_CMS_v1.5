<?php
namespace Godyar\Services;

use PDO;

/**
 * WebPush service.
 *
 * The previous file was corrupted and caused syntax errors.
 * This implementation is intentionally conservative:
 * - If push tables/config are missing, it fails gracefully.
 * - Provides the methods used by admin/settings/pwa.php.
 */
final class WebPushService
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Broadcast payload to all subscriptions.
     *
     * @param array<string,mixed> $payload
     */
    public function sendBroadcast(array $payload, int $ttlSeconds = 3600, bool $testOnly = false): array
    {
        // If no DB available, just return a safe error (do not fatal).
        if (!$this->pdo instanceof PDO) {
            return ['ok' => false, 'sent' => 0, 'failed' => 0, 'message' => 'PDO unavailable'];
        }

        // If table doesn't exist, skip.
        if (function_exists('gdy_db_stmt_table_exists')) {
            try {
                if (!gdy_db_stmt_table_exists($this->pdo, 'push_subscriptions')) {
                    return ['ok' => false, 'sent' => 0, 'failed' => 0, 'message' => 'push_subscriptions table missing'];
                }
            } catch (\Throwable $e) {
                // continue best-effort
            }
        }

        // In many shared hosts, webpush libs are not installed.
        // We keep this as a stub that records intent only.
        if ($testOnly) {
            return ['ok' => true, 'sent' => 0, 'failed' => 0, 'message' => 'Test mode: no delivery'];
        }

        // Best-effort: mark a broadcast row if a table exists.
        try {
            if (function_exists('gdy_db_stmt_table_exists') && gdy_db_stmt_table_exists($this->pdo, 'push_broadcasts')) {
                $stmt = $this->pdo->prepare('INSERT INTO push_broadcasts (payload_json, ttl, created_at) VALUES (:p,:t,NOW())');
                $stmt->execute([
                    ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ':t' => $ttlSeconds,
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return ['ok' => true, 'sent' => 0, 'failed' => 0, 'message' => 'Broadcast queued (stub)'];
    }
}
