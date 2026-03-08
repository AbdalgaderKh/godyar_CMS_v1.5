<?php
require_once __DIR__ . '/../includes/smart_translation_engine.php';

$key = isset($_GET['key']) ? (string)$_GET['key'] : '';
$expected = gdy_env_value('TRANSLATION_CRON_KEY', '');

if ($expected === '' || !hash_equals($expected, $key)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$pdo = gdy_translation_db();
$stmt = $pdo->query("SELECT id FROM translation_jobs WHERE status='queued' ORDER BY id ASC LIMIT 5");
$jobs = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();

header('Content-Type: text/plain; charset=utf-8');

if (!$jobs) {
    echo "No queued jobs.";
    exit;
}

foreach ($jobs as $job) {
    try {
        $result = gdy_translation_process_job((int)$job['id']);
        echo "Job #" . (int)$job['id'] . ": " . $result['message'] . "\n";
    } catch (Exception $e) {
        echo "Job #" . (int)$job['id'] . ": ERROR - " . $e->getMessage() . "\n";
    }
}
