<?php
declare(strict_types=1);

// Legacy endpoint (kept for backward compatibility).
// New endpoint: POST /api/push/subscribe (handled by App\Http\Controllers\Api\NewsExtrasController)

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => true, 'proxied' => false, 'hint' => 'Use /api/push/subscribe'], JSON_UNESCAPED_UNICODE);
