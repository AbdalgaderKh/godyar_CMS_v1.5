<?php
declare(strict_types=1);

/**
 * Simple migrations runner (optional)
 * - Safe: does not run unless you execute it explicitly.
 * - Uses .env if present.
 */

function env_get(string $key, string $default = ''): string {
    // 1) $_ENV
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($v !== false && $v !== null && $v !== '') return (string)$v;

    // 2) .env (very small parser)
    $envFile = __DIR__ . '/../.env';
    if (is_file($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;
            $k = trim($parts[0]);
            $val = trim($parts[1]);
            $val = trim($val, "\"'");
            if ($k === $key && $val !== '') return $val;
        }
    }

    return $default;
}

$dbHost = env_get('DB_HOST', 'localhost');
$dbName = env_get('DB_NAME', '');
$dbUser = env_get('DB_USER', '');
$dbPass = env_get('DB_PASS', '');
$dbCharset = env_get('DB_CHARSET', 'utf8mb4');

if ($dbName === '' || $dbUser === '') {
    fwrite(STDERR, "Missing DB_NAME/DB_USER in .env\n");
    exit(1);
}

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "DB connection failed: {$e->getMessage()}\n");
    exit(1);
}

// Ensure migrations table
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (\n  id VARCHAR(191) PRIMARY KEY,\n  ran_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$migrationsDir = __DIR__ . '/../database/migrations';
$files = glob($migrationsDir . '/*.php') ?: [];
sort($files);

$ran = $pdo->query('SELECT id FROM migrations')->fetchAll();
$ranIds = array_flip(array_map(fn($r) => (string)$r['id'], $ran));

$applied = 0;
foreach ($files as $file) {
    $mig = require $file;
    if (!is_array($mig) || empty($mig['id'])) continue;
    $id = (string)$mig['id'];
    if (isset($ranIds[$id])) continue;

    echo "Applying: {$id}\n";
    $pdo->beginTransaction();
    try {
        if (isset($mig['up']) && is_string($mig['up']) && trim($mig['up']) !== '') {
            $pdo->exec($mig['up']);
        } elseif (isset($mig['up']) && is_callable($mig['up'])) {
            ($mig['up'])($pdo);
        }
        $stmt = $pdo->prepare('INSERT INTO migrations (id) VALUES (?)');
        $stmt->execute([$id]);
        $pdo->commit();
        $applied++;
    } catch (Throwable $e) {
        $pdo->rollBack();
        fwrite(STDERR, "Failed: {$id} => {$e->getMessage()}\n");
        exit(1);
    }
}

echo "Done. Applied {$applied} migration(s).\n";
