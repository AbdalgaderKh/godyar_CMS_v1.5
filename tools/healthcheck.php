<?php
// Simple healthcheck - safe to delete after debugging.
// Access: /tools/healthcheck.php  (restrict via .htaccess if needed)
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$checks = [];

$checks['php_version'] = PHP_VERSION;
$checks['app_bootstrap_loaded'] = true;

// Autoload check
$checks['redirect_controller_exists'] = class_exists('App\\Http\\Controllers\\RedirectController');

$checks['session_ini'] = [
  'gc_probability' => ini_get('session.gc_probability'),
  'gc_divisor' => ini_get('session.gc_divisor'),
  'gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
];

echo json_encode(['ok' => true, 'checks' => $checks], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
