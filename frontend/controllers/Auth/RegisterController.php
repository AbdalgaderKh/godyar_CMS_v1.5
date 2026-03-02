<?php

declare(strict_types=1);

/**
 * RegisterController.php (Production-safe)
 * - CSRF protection
 * - Validates + creates user in `users` table
 * - Works on shared hosting (no fancy routing requirements)
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 3));
}

require_once ROOT_PATH . '/includes/bootstrap.php';

// Load helper functions (CSRF/base_url) when available
$fn = ROOT_PATH . '/includes/functions.php';
if (is_file($fn)) {
    require_once $fn;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (function_exists('gdy_session_start')) {
        gdy_session_start();
    } else {
        if (!headers_sent()) { session_start(); }
    }
}

if (!function_exists('h')) {
    function h($v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

/** @var PDO|null $pdo */
$pdo = function_exists('gdy_pdo_safe') ? gdy_pdo_safe() : null;

$baseUrl = function_exists('base_url') ? rtrim((string)base_url(), '/') : '';

// If already logged in, send home
if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
    header('Location: ' . ($baseUrl !== '' ? $baseUrl . '/' : '/'));
    exit;
}

// CSRF token
$csrfToken = '';
if (function_exists('generate_csrf_token')) {
    $csrfToken = (string)generate_csrf_token();
} else {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time'] = time();
    }
    $csrfToken = (string)$_SESSION['csrf_token'];
}

$error = '';
$success = '';

/**
 * Create a unique username if needed
 */
function gdy_make_username(PDO $pdo, string $email, ?string $preferred = null): string
{
    $base = trim((string)$preferred);
    if ($base === '') {
        $base = strtolower(preg_replace('~[^a-z0-9_]+~i', '_', explode('@', $email)[0] ?? 'user'));
    }
    $base = trim($base, '_');
    if ($base === '') $base = 'user';

    $candidate = $base;
    $i = 0;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $candidate]);
        if (!$stmt->fetchColumn()) return $candidate;
        $i++;
        $candidate = $base . $i;
        if ($i > 9999) return $base . bin2hex(random_bytes(2));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string)($_POST['csrf_token'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['confirm_password'] ?? '');

    // CSRF validate
    if (function_exists('verify_csrf_token')) {
        if (!verify_csrf_token($postedToken)) {
            $error = 'انتهت صلاحية الجلسة أو حدث خطأ في التحقق. حدّث الصفحة وحاول مرة أخرى.';
        }
    } else {
        if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $postedToken)) {
            $error = 'انتهت صلاحية الجلسة أو حدث خطأ في التحقق. حدّث الصفحة وحاول مرة أخرى.';
        }
    }

    if ($error === '') {
        if ($email === '' || $password === '' || $confirm === '') {
            $error = 'يرجى تعبئة البريد الإلكتروني وكلمة المرور وتأكيدها.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'البريد الإلكتروني غير صالح.';
        } elseif (strlen($password) < 6) {
            $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
        } elseif (!hash_equals($password, $confirm)) {
            $error = 'كلمتا المرور غير متطابقتين.';
        } elseif (!($pdo instanceof PDO)) {
            $error = 'لا يمكن الاتصال بقاعدة البيانات حالياً.';
        }
    }

    if ($error === '') {
        try {
            // Ensure email unique
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
            $stmt->execute([':e' => $email]);
            if ($stmt->fetchColumn()) {
                $error = 'البريد مستخدم بالفعل. جرّب تسجيل الدخول.';
            } else {
                $finalUsername = gdy_make_username($pdo, $email, $username);
                $hash = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare(
                    'INSERT INTO users (name, username, email, password_hash, role, is_admin, status, created_at) '
                    . 'VALUES (:name, :username, :email, :ph, :role, 0, :status, NOW())'
                );
                $stmt->execute([
                    ':name' => ($name !== '' ? $name : null),
                    ':username' => $finalUsername,
                    ':email' => $email,
                    ':ph' => $hash,
                    ':role' => 'user',
                    ':status' => 'active',
                ]);

                $success = 'تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.';
            }
        } catch (Throwable $e) {
            $error = 'حدث خطأ أثناء إنشاء الحساب. الرجاء المحاولة لاحقاً.';
        }
    }
}

// Render via a dedicated view for consistent UI
$reg_error = $error;
$reg_success = $success;
$reg_csrf = $csrfToken;
$reg_old = [
    'name' => (string)($_POST['name'] ?? ''),
    'username' => (string)($_POST['username'] ?? ''),
    'email' => (string)($_POST['email'] ?? ''),
];

$meta_title = 'إنشاء حساب';
$meta_description = 'أنشئ حسابك للوصول إلى مزايا الموقع.';
$canonical_url = rtrim($baseUrl, '/') . '/register';

require ROOT_PATH . '/frontend/views/register.php';
exit;
