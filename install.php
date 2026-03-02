<?php
/**
 * Godyar CMS v1.5 - Installer
 *
 * Security:
 * - Installer creates install.lock to prevent re-run.
 * - Delete install.php and install/ after installation.
 */

declare(strict_types=1);

define('GODYAR_INSTALL', true);

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ROOT = __DIR__;
$LOCK = $ROOT . '/install.lock';
if (is_file($LOCK) && !isset($_GET['force'])) {
    http_response_code(403);
    die('Already installed. Delete install.lock to reinstall (not recommended).');
}

const STEP_WELCOME = 1;
const STEP_REQUIREMENTS = 2;
const STEP_DATABASE = 3;
const STEP_SITE = 4;
const STEP_ADMIN = 5;
const STEP_INSTALL = 6;
const STEP_DONE = 7;

$step = isset($_GET['step']) ? (int)$_GET['step'] : STEP_WELCOME;
if ($step < STEP_WELCOME || $step > STEP_DONE) $step = STEP_WELCOME;

$_SESSION['installer'] = $_SESSION['installer'] ?? [
    'db' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'geqzylcq_myar',
        'user' => '',
        'pass' => '',
        'charset' => 'utf8mb4',
        // Existing DB mode: if database contains tables, require explicit overwrite
        'overwrite' => false,
    ],
    'site' => [
        'url' => '',
        'name' => 'Godyar CMS',
        'lang' => 'ar',
        'timezone' => 'Asia/Riyadh',
        'email' => '',
        'description' => '',
    ],
    'admin' => [
        'username' => 'admin',
        'email' => '',
        'password' => '',
    ],
    'errors' => [],
    'log' => [],
];

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function add_error(string $msg): void {
    $_SESSION['installer']['errors'][] = $msg;
}

function pop_errors(): array {
    $e = $_SESSION['installer']['errors'] ?? [];
    $_SESSION['installer']['errors'] = [];
    return $e;
}

function requirements(): array {
    $req = [];
    $req[] = ['PHP >= 8.1', PHP_VERSION, version_compare(PHP_VERSION, '8.1.0', '>=')];

    foreach (['pdo_mysql','mbstring','json','curl','gd','zip'] as $ext) {
        $req[] = ["Extension: $ext", extension_loaded($ext) ? 'Loaded' : 'Missing', extension_loaded($ext)];
    }

    $wdirs = ['uploads','cache','logs','tmp','backup'];
    foreach ($wdirs as $d) {
        $p = __DIR__ . '/' . $d;
        if (!is_dir($p)) @mkdir($p, 0755, true);
        $req[] = ["Writable: /$d", is_writable($p) ? 'Yes' : 'No', is_writable($p)];
    }

    // root writable for .env
    $req[] = ["Writable: project root (to create .env)", is_writable(__DIR__) ? 'Yes' : 'No', is_writable(__DIR__)];
    return $req;
}

function pdo_connect(array $db): PDO {
    $host = $db['host'];
    $port = (string)($db['port'] ?? '3306');
    $name = $db['name'];
    $charset = $db['charset'] ?: 'utf8mb4';
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    return new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
}

/**
 * Drop all tables & views in the current database (dangerous).
 * Used only when user explicitly selects "overwrite".
 */
function drop_all_objects(PDO $pdo): void {
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type='BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $pdo->exec('DROP TABLE IF EXISTS `' . str_replace('`', '``', $t) . '`');
    }

    $views = $pdo->query("SELECT table_name FROM information_schema.views WHERE table_schema = DATABASE()")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($views as $v) {
        $pdo->exec('DROP VIEW IF EXISTS `' . str_replace('`', '``', $v) . '`');
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

/**
 * Execute an SQL file with support for DELIMITER (procedures/triggers).
 * NOTE: This is intentionally simple and works for phpMyAdmin-like dumps.
 */
function exec_sql_file(PDO $pdo, string $path): void {
    if (!is_file($path)) throw new RuntimeException("SQL file not found: $path");

    $sql = file($path, FILE_IGNORE_NEW_LINES);
    if (!is_array($sql)) throw new RuntimeException("Unable to read SQL file: $path");

    $delimiter = ';';
    $buffer = '';

    foreach ($sql as $line) {
        $trim = trim($line);

        // Skip comments
        if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '#')) {
            continue;
        }

        // Handle DELIMITER
        if (preg_match('/^DELIMITER\s+(.+)$/i', $trim, $m)) {
            $delimiter = trim($m[1]);
            continue;
        }

        $buffer .= $line . "\n";

        // End of statement?
        if ($delimiter !== '' && str_ends_with(rtrim($trim), $delimiter)) {
            $stmt = rtrim($buffer);
            // remove last delimiter
            $stmt = preg_replace('/' . preg_quote($delimiter, '/') . '\s*$/', '', $stmt);
            $stmt = trim($stmt);
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
            $buffer = '';
        }
    }

    $tail = trim($buffer);
    if ($tail !== '') {
        // best-effort
        $pdo->exec($tail);
    }
}

function create_env(array $db, array $site): void {
    $envPath = __DIR__ . '/.env';
    if (is_file($envPath)) return;

    $encryption = bin2hex(random_bytes(32));
    $reinstall  = bin2hex(random_bytes(24));

    $lines = [];
    $lines[] = "APP_ENV=production";
    $lines[] = "APP_DEBUG=false";
    $lines[] = "APP_URL=" . ($site['url'] ?: 'https://example.com');
    $lines[] = "TIMEZONE=" . ($site['timezone'] ?: 'Asia/Riyadh');
    $lines[] = "DB_DRIVER=mysql";
    $lines[] = "DB_HOST=" . $db['host'];
    $lines[] = "DB_PORT=" . ($db['port'] ?? '3306');
    $lines[] = "DB_NAME=" . $db['name'];
    $lines[] = "DB_USER=" . $db['user'];
    $lines[] = "DB_PASS=" . $db['pass'];
    $lines[] = "DB_CHARSET=" . ($db['charset'] ?: 'utf8mb4');
    $lines[] = "ENCRYPTION_KEY={$encryption}";
    $lines[] = "INSTALL_REINSTALL_TOKEN={$reinstall}";
    $lines[] = "SITE_LANG_DEFAULT=" . ($site['lang'] ?: 'ar');
    $lines[] = "GDY_INSTALLED=1";
    file_put_contents($envPath, implode("\n", $lines) . "\n");
}

function seed_settings(PDO $pdo, array $site): void {
    // Table: settings(setting_key PK, setting_value)
    $settings = [
        'site_name' => $site['name'] ?: 'Godyar CMS',
        'site_url'  => $site['url'] ?: '',
        'site_lang' => $site['lang'] ?: 'ar',
        'site_timezone' => $site['timezone'] ?: 'Asia/Riyadh',
        'site_email' => $site['email'] ?: '',
        'site_description' => $site['description'] ?: '',
        'site_version' => '1.5.1',
        'site_installed_at' => date('Y-m-d H:i:s'),
    ];

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    foreach ($settings as $k => $v) {
        $stmt->execute([$k, (string)$v]);
    }
}

function create_admin(PDO $pdo, array $admin): void {
    $username = $admin['username'];
    $email    = $admin['email'];
    $pass     = $admin['password'];

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Schema in dump: users(password_hash, password, role, is_admin)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->execute([$username, $email]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $up = $pdo->prepare("UPDATE users SET email=?, password_hash=?, password=NULL, role='admin', is_admin=1, status='active' WHERE id=?");
        $up->execute([$email, $hash, $exists]);
        return;
    }

    $ins = $pdo->prepare("INSERT INTO users (name, username, email, password_hash, role, is_admin, status, created_at)
        VALUES (?, ?, ?, ?, 'admin', 1, 'active', NOW())");
    $ins->execute([$username, $username, $email, $hash]);
}

function log_line(string $s): void { $_SESSION['installer']['log'][] = $s; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data =& $_SESSION['installer'];

    if ($step === STEP_WELCOME) {
        header('Location: ?step=' . STEP_REQUIREMENTS); exit;
    }

    if ($step === STEP_REQUIREMENTS) {
        $req = requirements();
        foreach ($req as $r) { if (!$r[2]) { add_error("Requirement failed: {$r[0]}"); } }
        if (!pop_errors()) { header('Location: ?step=' . STEP_DATABASE); exit; }
    }

    if ($step === STEP_DATABASE) {
        $data['db']['host'] = trim($_POST['db_host'] ?? 'localhost');
        $data['db']['port'] = trim($_POST['db_port'] ?? '3306');
        $data['db']['name'] = trim($_POST['db_name'] ?? '');
        $data['db']['user'] = trim($_POST['db_user'] ?? '');
        $data['db']['pass'] = (string)($_POST['db_pass'] ?? '');
        $data['db']['charset'] = trim($_POST['db_charset'] ?? 'utf8mb4');
        $data['db']['overwrite'] = !empty($_POST['db_overwrite']);

        if ($data['db']['name'] === '' || $data['db']['user'] === '') {
            add_error('Database name and username are required.');
        } else {
            try {
                $pdo = new PDO(
                    "mysql:host={$data['db']['host']};port={$data['db']['port']};charset={$data['db']['charset']}",
                    $data['db']['user'],
                    $data['db']['pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                // create DB if not exists (if permissions allow)
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$data['db']['name']}` CHARACTER SET {$data['db']['charset']} COLLATE {$data['db']['charset']}_unicode_ci");

                // If DB already contains tables, require explicit overwrite
                $pdoDb = pdo_connect($data['db']);
                $tablesCount = (int)$pdoDb->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type='BASE TABLE'")->fetchColumn();
                if ($tablesCount > 0 && !$data['db']['overwrite']) {
                    add_error("Database is not empty ({$tablesCount} tables). Enable 'Overwrite existing tables' to continue, or choose an empty database.");
                }
            } catch (Throwable $e) {
                add_error('Database connection failed: ' . $e->getMessage());
            }
        }
        if (!pop_errors()) { header('Location: ?step=' . STEP_SITE); exit; }
    }

    if ($step === STEP_SITE) {
        $data['site']['name'] = trim($_POST['site_name'] ?? 'Godyar CMS');
        $data['site']['url']  = trim($_POST['site_url'] ?? '');
        $data['site']['lang'] = trim($_POST['site_lang'] ?? 'ar');
        $data['site']['timezone'] = trim($_POST['site_timezone'] ?? 'Asia/Riyadh');
        $data['site']['email'] = trim($_POST['site_email'] ?? '');
        $data['site']['description'] = trim($_POST['site_description'] ?? '');

        if ($data['site']['url'] === '') add_error('Site URL is required.');
        if ($data['site']['email'] !== '' && !filter_var($data['site']['email'], FILTER_VALIDATE_EMAIL)) add_error('Invalid site email.');
        if (!pop_errors()) { header('Location: ?step=' . STEP_ADMIN); exit; }
    }

    if ($step === STEP_ADMIN) {
        $data['admin']['username'] = trim($_POST['admin_user'] ?? 'admin');
        $data['admin']['email']    = trim($_POST['admin_email'] ?? '');
        $data['admin']['password'] = (string)($_POST['admin_pass'] ?? '');
        $confirm = (string)($_POST['admin_pass_confirm'] ?? '');

        if (strlen($data['admin']['username']) < 3) add_error('Admin username must be at least 3 characters.');
        if ($data['admin']['email'] === '' || !filter_var($data['admin']['email'], FILTER_VALIDATE_EMAIL)) add_error('Valid admin email is required.');
        if (strlen($data['admin']['password']) < 8) add_error('Admin password must be at least 8 characters.');
        if ($data['admin']['password'] !== $confirm) add_error('Passwords do not match.');

        if (!pop_errors()) { header('Location: ?step=' . STEP_INSTALL); exit; }
    }

    if ($step === STEP_INSTALL) {
        $data['errors'] = [];
        $data['log'] = [];
        try {
            log_line('Connecting to database...');
            $pdo = pdo_connect($data['db']);

            if (!empty($data['db']['overwrite'])) {
                log_line('Overwrite enabled: dropping existing tables/views...');
                drop_all_objects($pdo);
            }

            log_line('Running database schema (install/schema.sql)...');
            exec_sql_file($pdo, __DIR__ . '/install/schema.sql');

            log_line('Seeding settings...');
            seed_settings($pdo, $data['site']);

            log_line('Creating admin user...');
            create_admin($pdo, $data['admin']);

            log_line('Creating .env ...');
            create_env($data['db'], $data['site']);

            log_line('Creating install.lock ...');
            file_put_contents($LOCK, date('c'));

            log_line('Done ✅');
            header('Location: ?step=' . STEP_DONE);
            exit;
        } catch (Throwable $e) {
            add_error('Installation failed: ' . $e->getMessage());
        }
    }
}

function step_title(int $s): string {
    return [
        STEP_WELCOME => 'Welcome',
        STEP_REQUIREMENTS => 'Requirements',
        STEP_DATABASE => 'Database',
        STEP_SITE => 'Site',
        STEP_ADMIN => 'Admin',
        STEP_INSTALL => 'Install',
        STEP_DONE => 'Done',
    ][$s] ?? 'Installer';
}

$errors = pop_errors();
$data = $_SESSION['installer'];

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Godyar CMS - Installer</title>
<style>
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial; background:#0f172a; color:#e2e8f0; margin:0; padding:24px;}
.card{max-width:860px;margin:0 auto;background:#111827;border:1px solid #1f2937;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.35)}
.header{padding:22px 26px;background:linear-gradient(135deg,#6366f1,#8b5cf6)}
.header h1{margin:0;font-size:22px}
.header small{opacity:.9}
.body{padding:24px 26px}
.badge{display:inline-block;padding:4px 10px;border-radius:999px;background:#1f2937;margin-left:8px}
hr{border:0;border-top:1px solid #1f2937;margin:18px 0}
input,select,textarea{width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e2e8f0}
label{display:block;margin:12px 0 8px;color:#cbd5e1}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.btn{background:#6366f1;color:white;border:0;border-radius:10px;padding:12px 16px;cursor:pointer;font-weight:700}
.btn:disabled{opacity:.5;cursor:not-allowed}
.err{background:#7f1d1d;border:1px solid #991b1b;color:#fee2e2;padding:12px;border-radius:10px;margin-bottom:14px}
.ok{background:#064e3b;border:1px solid #065f46;color:#d1fae5;padding:12px;border-radius:10px;margin-top:14px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #1f2937;padding:10px;text-align:right}
.k{opacity:.8}
.log{background:#0b1220;border:1px solid #1f2937;border-radius:12px;padding:12px;max-height:260px;overflow:auto;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
.pass{color:#34d399}.fail{color:#fb7185}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <h1>🚀 مُثبّت Godyar CMS <span class="badge">v1.5.1</span></h1>
    <small>بعد التثبيت احذف install.php و install/</small>
  </div>
  <div class="body">
    <div class="k">الخطوة: <b><?php echo h(step_title($step)); ?></b></div>
    <hr>

    <?php if ($errors): ?>
      <div class="err"><b>حدثت أخطاء:</b><ul><?php foreach ($errors as $e): ?><li><?php echo h($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($step === STEP_WELCOME): ?>
      <p>هذا المعالج سيقوم بتهيئة قاعدة البيانات وإنشاء ملف <code>.env</code> وحساب المدير.</p>
      <form method="post"><button class="btn">ابدأ التثبيت</button></form>

    <?php elseif ($step === STEP_REQUIREMENTS): ?>
      <?php $req = requirements(); $all = true; ?>
      <table class="table">
        <thead><tr><th>المتطلب</th><th>الحالة</th></tr></thead>
        <tbody>
        <?php foreach ($req as $r): $ok = (bool)$r[2]; $all = $all && $ok; ?>
          <tr>
            <td><?php echo h($r[0]); ?> <span class="k">(<?php echo h($r[1]); ?>)</span></td>
            <td><?php echo $ok ? '<span class="pass">✓</span>' : '<span class="fail">✗</span>'; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <form method="post" style="margin-top:14px">
        <button class="btn" <?php echo $all ? '' : 'disabled'; ?>>متابعة</button>
      </form>

    <?php elseif ($step === STEP_DATABASE): ?>
      <form method="post">
        <div class="grid">
          <div>
            <label>DB Host</label>
            <input name="db_host" value="<?php echo h($data['db']['host']); ?>" required>
          </div>
          <div>
            <label>DB Port</label>
            <input name="db_port" value="<?php echo h($data['db']['port']); ?>" required>
          </div>
        </div>
        <label>DB Name</label>
        <input name="db_name" value="<?php echo h($data['db']['name']); ?>" required>
        <div class="grid">
          <div>
            <label>DB User</label>
            <input name="db_user" value="<?php echo h($data['db']['user']); ?>" required>
          </div>
          <div>
            <label>DB Password</label>
            <input type="password" name="db_pass" value="">
          </div>
        </div>
        <label>Charset</label>
        <select name="db_charset">
          <?php foreach (['utf8mb4','utf8'] as $c): ?>
            <option value="<?php echo h($c); ?>" <?php echo $data['db']['charset']===$c?'selected':''; ?>><?php echo h($c); ?></option>
          <?php endforeach; ?>
        </select>

        <div style="margin-top:12px; padding:12px; border:1px solid #334155; border-radius:12px; background:#0b1220">
          <label style="margin:0; display:flex; gap:10px; align-items:center; cursor:pointer;">
            <input type="checkbox" name="db_overwrite" value="1" <?php echo !empty($data['db']['overwrite']) ? 'checked' : ''; ?> style="width:auto;">
            <span>
              <strong>Overwrite existing tables</strong>
              <span class="k">(يحذف كل الجداول/العروض داخل قاعدة البيانات المحددة ثم يعيد إنشاءها)</span>
            </span>
          </label>
          <div class="k" style="margin-top:8px; line-height:1.6">
            استخدم هذا الخيار فقط إذا كانت قاعدة البيانات تحتوي بقايا تثبيت سابق وتريد إعادة التثبيت من الصفر على نفس الـ DB.
          </div>
        </div>
        <div style="margin-top:14px"><button class="btn">حفظ والمتابعة</button></div>
      </form>

    <?php elseif ($step === STEP_SITE): ?>
      <form method="post">
        <label>اسم الموقع</label>
        <input name="site_name" value="<?php echo h($data['site']['name']); ?>" required>
        <label>رابط الموقع (URL)</label>
        <input name="site_url" value="<?php echo h($data['site']['url']); ?>" placeholder="https://example.com" required>
        <div class="grid">
          <div>
            <label>اللغة الافتراضية</label>
            <select name="site_lang">
              <?php foreach (['ar'=>'العربية','en'=>'English','fr'=>'Français'] as $k=>$v): ?>
                <option value="<?php echo h($k); ?>" <?php echo $data['site']['lang']===$k?'selected':''; ?>><?php echo h($v); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label>المنطقة الزمنية</label>
            <input name="site_timezone" value="<?php echo h($data['site']['timezone']); ?>">
          </div>
        </div>
        <label>بريد الموقع (اختياري)</label>
        <input name="site_email" value="<?php echo h($data['site']['email']); ?>" placeholder="admin@example.com">
        <label>وصف الموقع (اختياري)</label>
        <textarea name="site_description" rows="3"><?php echo h($data['site']['description']); ?></textarea>
        <div style="margin-top:14px"><button class="btn">متابعة</button></div>
      </form>

    <?php elseif ($step === STEP_ADMIN): ?>
      <form method="post">
        <div class="grid">
          <div>
            <label>اسم المستخدم</label>
            <input name="admin_user" value="<?php echo h($data['admin']['username']); ?>" required>
          </div>
          <div>
            <label>البريد الإلكتروني</label>
            <input type="email" name="admin_email" value="<?php echo h($data['admin']['email']); ?>" required>
          </div>
        </div>
        <div class="grid">
          <div>
            <label>كلمة المرور</label>
            <input type="password" name="admin_pass" required>
          </div>
          <div>
            <label>تأكيد كلمة المرور</label>
            <input type="password" name="admin_pass_confirm" required>
          </div>
        </div>
        <div style="margin-top:14px"><button class="btn">متابعة للتثبيت</button></div>
      </form>

    <?php elseif ($step === STEP_INSTALL): ?>
      <p>سيتم تنفيذ سكربت قاعدة البيانات من <code>install/schema.sql</code> ثم إنشاء المدير وملف <code>.env</code>.</p>
      <form method="post"><button class="btn">تنفيذ التثبيت الآن</button></form>

      <?php if (!empty($data['log'])): ?>
        <div class="log" style="margin-top:14px"><?php foreach ($data['log'] as $l): echo h($l) . "\n"; endforeach; ?></div>
      <?php endif; ?>

    <?php elseif ($step === STEP_DONE): ?>
      <div class="ok">
        ✅ تم التثبيت بنجاح.<br>
        <b>مهم:</b> احذف <code>install.php</code> و <code>install/</code> الآن.
      </div>
      <?php if (!empty($data['log'])): ?>
        <h3>سجل التثبيت</h3>
        <div class="log"><?php foreach ($data['log'] as $l): echo h($l) . "\n"; endforeach; ?></div>
      <?php endif; ?>
      <p style="margin-top:12px">
        <a class="btn" href="<?php echo h($data['site']['url'] ?: '/'); ?>" style="text-decoration:none;display:inline-block">فتح الموقع</a>
        <a class="btn" href="<?php echo h(($data['site']['url'] ?: '') . '/admin'); ?>" style="text-decoration:none;display:inline-block;background:#22c55e">لوحة التحكم</a>
      </p>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
