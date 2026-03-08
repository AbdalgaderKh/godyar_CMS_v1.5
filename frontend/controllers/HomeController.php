<?php
// /frontend/controllers/HomeController .php
//
// كنترولر مساعد للواجهة الأمامية (التصنيفات – الترند – الأرشيف – إعدادات الموقع)

require_once __DIR__ . '/../../includes/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    gdy_session_start();
}

/**
 * دالة هروب نصوص آمنة للاستخدام في HTML (عند الحاجة)
 */
if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * كنترولر رئيسي للصفحات العامة
 */
final class HomeController
{
    /**
     * إرجاع كائن PDO من المتغيّر العام
     *
     * @return \PDO | null
     */
    public static function db(): ?\PDO
    {
        $pdo = gdy_pdo_safe();
        return ($pdo instanceof \PDO) ? $pdo : null;
    }

    /**
     * فحص وجود عمود في جدول معيّن
     * يُستخدم لتفادي الأخطاء عندما لا يكون العمود موجوداً
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    /**
 * فحص وجود عمود في جدول معيّن (متوافق مع الاستدعاءات القديمة) .
 *
 * دعم طريقتين:
 *-columnExists($pdo, 'table', 'column')
 *-columnExists('table', 'column') // سيجلب الاتصال من self::db()
 */
protected static function columnExists($pdoOrTable, $tableOrColumn, $column = null): bool
{
    $pdo = null;
    $table = null;
    $col = null;

    if ($pdoOrTable instanceof \PDO) {
        $pdo = $pdoOrTable;
        $table = is_string($tableOrColumn) ? $tableOrColumn : null;
        $col = is_string($column) ? $column : null;
    } else {
        $pdo = self::db();
        $table = is_string($pdoOrTable) ? $pdoOrTable : null;
        $col = is_string($tableOrColumn) ? $tableOrColumn : null;
    }

    if (!$pdo || !$table || !$col) {
        return false;
    }

    try {
        if (function_exists('db_column_exists')) {
            return db_column_exists($pdo, $table, $col);
        }

        $safeTable = str_replace('`', '', $table);
        $stmt = gdy_db_stmt_column_like($pdo, $safeTable, $col);
        return (bool)($stmt && $stmt->fetchColumn());
    } catch (\Throwable $e) {
        error_log('[HomeController] columnExists error: ' . $e->getMessage());
        return false;
    }
}


    /**
     * جلب إعدادات عامة للموقع من جدول settings
     *
     * ترجع مصفوفة جاهزة للاستخدام في الهيدر / الفوتر:
     * site_name, site_tagline, site_url, site_email, site_phone, site_address,
     * site_logo, layout_sidebar_mode, extra_head_code, extra_body_code ... إلخ
     */
    public static function getSiteSettings(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $pdo = self::db();
        $cache = [];

        if (!$pdo) {
            // قيم افتراضية في حال عدم توفر الاتصال
            // Theme selection: support legacy keys so the homepage matches other pages.
            $themeCandidate = (string)($raw['theme.front']
                ?? $raw['theme_front']
                ?? $raw['frontend_theme']
                ?? $raw['front_theme']
                ?? 'default');

            // If a generic "default" is stored but a specific front_theme exists, prefer it.
            if ($themeCandidate === 'default' && !empty($raw['front_theme']) && (string)$raw['front_theme'] !== 'default') {
                $themeCandidate = (string)$raw['front_theme'];
            }

            $cache = [
                'site_name' => 'Godyar News',
                'site_tagline' => 'منصة إخبارية متكاملة',
                'layout_sidebar_mode' => 'visible',
            ];
            return $cache;
        }

        try {
            // التأكد من وجود جدول settings
            $check = gdy_db_stmt_table_exists($pdo, 'settings');
            if (!$check || !$check->fetchColumn()) {
                $cache = [
                    'site_name' => 'Godyar News',
                    'site_tagline' => 'منصة إخبارية متكاملة',
                    'layout_sidebar_mode' => 'visible',
                ];
                return $cache;
            }

            // Compatibility: some DBs use `setting_value` instead of `value`
            $valueCol = self::columnExists($pdo, 'settings', 'value') ? 'value' : null;
            if ($valueCol === null && self::columnExists($pdo, 'settings', 'setting_value')) {
                $valueCol = 'setting_value';
            }

            // If we still couldn't detect a usable value column, don't query (avoid SQLSTATE 42S22)
            if ($valueCol === null) {
                $cache = [
                    'site_name' => 'Godyar News',
                    'site_tagline' => 'منصة إخبارية متكاملة',
                    'layout_sidebar_mode' => 'visible',
                ];
                return $cache;
            }

            $stmt = $pdo->query("SELECT setting_key, `{$valueCol}` AS `value` FROM `settings`");
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

            $raw = [];
	            foreach ($rows as $row) {
	                $k = (string)($row['setting_key'] ?? '');
                if ($k === '') {
                    continue;
                }
                $raw[$k] = (string)($row['value'] ?? '');
            }

            
            // Resolve selected front theme (CSS path) safely.
            // Accept either:
            // - "assets/css/themes/theme-red.css" (preferred)
            // - "theme-red" (theme slug)
            // - "default"
            $themeCandidate = (string)($raw['theme.front'] ?? $raw['theme_front'] ?? $raw['front_theme'] ?? 'default');
            $themeCandidate = trim($themeCandidate);

            if ($themeCandidate === '' || $themeCandidate === 'default') {
                $themeCandidate = 'assets/css/themes/theme-core.css';
            } else {
                // If admin stored a slug like "theme-red", convert to expected path.
                if (preg_match('/^theme-[a-z0-9_-]+$/i', $themeCandidate)) {
                    $themeCandidate = 'assets/css/themes/' . $themeCandidate . '.css';
                }
                // Final safety: only allow loading from assets/css/themes/
                if (strpos($themeCandidate, 'assets/css/themes/') !== 0) {
                    $themeCandidate = 'assets/css/themes/theme-core.css';
                }
            }

// خريطة مفاتيح جاهزة للقالب
            $cache = [
                'site_name' => $raw['site.name'] ?? 'Godyar News',
                'site_tagline' => $raw['site.desc'] ?? 'منصة إخبارية متكاملة',
                'site_url' => $raw['site.url'] ?? '',
                'site_locale' => $raw['site.locale'] ?? 'ar',
                'site_timezone' => $raw['site.timezone'] ?? 'Asia/Riyadh',
                'site_email' => $raw['site.email'] ?? '',
                'site_phone' => $raw['site.phone'] ?? '',
                'site_address' => $raw['site.address'] ?? '',
                'site_logo' => $raw['site.logo'] ?? ($raw['site_logo'] ?? ($raw['logo'] ?? '')),
                'site.logo' => $raw['site.logo'] ?? ($raw['site_logo'] ?? ($raw['logo'] ?? '')),
                'logo' => $raw['logo'] ?? ($raw['site.logo'] ?? ($raw['site_logo'] ?? '')),

                'social_facebook' => $raw['social.facebook'] ?? '',
                'social_twitter' => $raw['social.twitter'] ?? '',
                'social_youtube' => $raw['social.youtube'] ?? '',
                'social_telegram' => $raw['social.telegram'] ?? '',
                'social_instagram' => $raw['social.instagram'] ?? '',

                'layout_sidebar_mode' => $raw['layout.sidebar_mode'] ?? 'visible',

                'theme_front' => $themeCandidate,
                'theme_primary' => $raw['theme.primary'] ?? '#111111',
                'theme_accent' => $raw['theme.accent'] ?? '#111111',
                'theme_header_bg_enabled' => $raw['theme.header_bg_enabled'] ?? '0',
                'theme_header_bg_source' => $raw['theme.header_bg_source'] ?? 'upload',
                'theme_header_bg_url' => $raw['theme.header_bg_url'] ?? '',
                'theme_header_bg_image' => $raw['theme.header_bg_image'] ?? '',
                'theme_header' => $raw['theme.header_style'] ?? 'dark',
                'theme_footer' => $raw['theme.footer_style'] ?? 'dark',
                'theme_container' => $raw['theme.container'] ?? 'boxed',

                'extra_head_code' => $raw['advanced.extra_head'] ?? '',
                'extra_body_code' => $raw['advanced.extra_body'] ?? '',
            ];

            // احتفاظ بالخام أيضًا لو احتجته لاحقًا
            $cache['raw'] = $raw;
        } catch (\Throwable $e) {
            error_log('[HomeController] getSiteSettings error: ' . $e->getMessage());
            $cache = [
                'site_name' => 'Godyar News',
                'site_tagline' => 'منصة إخبارية متكاملة',
                'layout_sidebar_mode' => 'visible',
            ];
        }

        return $cache;
    }

    /**
     * محاولة إيجاد ملفات الهيدر والفوتر الخاصة بالقالب
     *
     * ترجع مصفوفة [headerFile, footerFile]
     */
    public static function resolveLayout(): array
    {
        // نفترض أن هذا الملف في: /frontend/controllers/HomeController .php
        $frontendRoot = dirname(__DIR__); // /frontend
        $publicRoot = dirname($frontendRoot); // /public_html

        $headerCandidates = [
    $frontendRoot . '/views/partials/header.php',
    $frontendRoot . '/layout/header.php',
    $frontendRoot . '/templates/header.php',
    $publicRoot . '/layout/header.php',
    $publicRoot . '/templates/header.php',
    $publicRoot . '/includes/header.php',
    $publicRoot . '/header.php',
];

        $footerCandidates = [
            $frontendRoot . '/layout/footer.php',
            $frontendRoot . '/views/partials/footer.php', // ✅ هنا الفوتر الجديد
            $frontendRoot . '/templates/footer.php',
            $publicRoot . '/layout/footer.php',
            $publicRoot . '/templates/footer.php',
            $publicRoot . '/includes/footer.php',
            $publicRoot . '/footer.php',
        ];

        $headerFile = null;
        foreach ($headerCandidates as $path) {
            if (is_file($path)) {
                $headerFile = $path;
                break;
            }
        }

        $footerFile = null;
        foreach ($footerCandidates as $path) {
            if (is_file($path)) {
                $footerFile = $path;
                break;
            }
        }

        return [$headerFile, $footerFile];
    }

    /**
     * جلب قسم حسب الـ slug
     *
     * @param string $slug
     * @return array | null
     */
    public static function getCategoryBySlug(string $slug): ?array
    {
        $pdo = self::db();
        if (!$pdo) {
            return null;
        }

        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $sql = "SELECT id, name, slug, description
                FROM categories
                WHERE slug = :slug
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $row;
    }

    /**
     * جلب الأخبار الخاصة بقسم معيّن
     *
     * @param int $categoryId
     * @param int $limit
     * @return array
     */
    public static function getNewsByCategory(int $categoryId, int $limit = 30): array
    {
        $pdo = self::db();
        if (!$pdo) {
            return [];
        }

        $limit = max(1, min($limit, 100));

        $where = ["category_id = :cid"];
        $params = [':cid' => $categoryId];

        // Exclude soft-deleted rows when column exists
        if (self::columnExists('news', 'deleted_at')) {
            $where[] = "(deleted_at IS NULL OR deleted_at = '' OR deleted_at = '0000-00-00 00:00:00')";
        }

        // Publish visibility (be permissive, but prefer correct flags when available)
        $pubClauses = [];
        if (self::columnExists('news', 'is_published')) {
            $pubClauses[] = "is_published = 1";
        }
        if (self::columnExists('news', 'status')) {
            $pubClauses[] = "LOWER(TRIM(status)) IN ('published','publish','active','approved')";
        }
        if (self::columnExists('news', 'published_at')) {
            $pubClauses[] = "(published_at IS NULL OR published_at <= NOW())";
        }
        if ($pubClauses) {
            // combine: allow rows that meet the publishing flag, and also respect published_at if present
            // If published_at exists, it is already included; status/is_published are OR'ed.
            $flags = [];
            $hasPublishedAt = self::columnExists('news', 'published_at');
            foreach ($pubClauses as $c) {
                if (strpos($c, 'published_at') !== false) continue;
                $flags[] = $c;
            }
            if ($flags) {
                $where[] = '(' . implode(' OR ', $flags) . ')';
            }
            // published_at gate (AND) when present
            if ($hasPublishedAt) {
                $where[] = "(published_at IS NULL OR published_at <= NOW())";
            }
        }

        // Ordering: prefer published_at, then created_at, else id.
        // We deliberately avoid selecting non-existent columns to prevent empty results.
        $orderExpr = "id DESC";
        if (self::columnExists('news', 'published_at')) {
            $orderExpr = "published_at DESC, id DESC";
        } elseif (self::columnExists('news', 'created_at')) {
            $orderExpr = "created_at DESC, id DESC";
        }

        $whereSql = implode(' AND ', $where);

        // IMPORTANT: bind LIMIT safely (MySQL may require emulate prepares)
        $prevEmu = true;
        try { $prevEmu = (bool)$pdo->getAttribute(\PDO::ATTR_EMULATE_PREPARES); } catch (\Throwable $e) {}
        try { $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true); } catch (\Throwable $e) {}

        // Use SELECT * so the query never fails if optional columns (slug/created_at/views) are missing.
        $sql = "SELECT *
                FROM news
                WHERE {$whereSql}
                ORDER BY {$orderExpr}
                LIMIT :lim";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':cid', (int)$categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit, \PDO::PARAM_INT);

        $ok = $stmt->execute();
        try { $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $prevEmu); } catch (\Throwable $e) {}

        if (!$ok) {
            error_log('[HomeController] getNewsByCategory SQL error: ' . json_encode($stmt->errorInfo(), JSON_UNESCAPED_UNICODE));
            return [];
        }

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fallback: if strict publish filters hide everything, show non-deleted rows for the category
        if (!$rows) {
            $fallbackWhere = ["category_id = :cid"];
            if (self::columnExists('news', 'deleted_at')) {
                $fallbackWhere[] = "(deleted_at IS NULL OR deleted_at = '' OR deleted_at = '0000-00-00 00:00:00')";
            }
            $fallbackSql = "SELECT *
                            FROM news
                            WHERE " . implode(' AND ', $fallbackWhere) . "
                            ORDER BY {$orderExpr}
                            LIMIT :lim";
            $st2 = $pdo->prepare($fallbackSql);
            $st2->bindValue(':cid', (int)$categoryId, \PDO::PARAM_INT);
            $st2->bindValue(':lim', (int)$limit, \PDO::PARAM_INT);
            $st2->execute();
            $rows = $st2->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        return $rows ?: [];
    }

    /**
     * أخبار الترند (الأعلى مشاهدة)
     *
     * @param int $limit
     * @return array
     */
    public static function getTrendingNews(int $limit = 10): array
    {
        $pdo = self::db();
        if (!$pdo) {
            return [];
        }

        $limit = max(1, min($limit, 50));

        $sql = "
            SELECT id, title, slug, created_at, views
            FROM news
            ORDER BY views DESC, created_at DESC
            LIMIT {$limit}
        ";
        $stmt = $pdo->query($sql);
        $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

        return $rows ?: [];
    }

    /**
     * آخر الأخبار مع إمكانية استبعاد مقالات كتّاب الرأي
     *
     * @param int $limit
     * @param bool $excludeOpinion لو true يستبعد الأخبار التي لها opinion_author_id
     * @return array
     */
    public static function getLatestNews(int $limit = 10, bool $excludeOpinion = true): array
    {
        $pdo = self::db();
        if (!$pdo) {
            return [];
        }

        $limit = max(1, min($limit, 100));
        $where = [];
        $params = [];

        // استبعاد الأخبار التي لها opinion_author_id إذا كان العمود موجوداً
        if ($excludeOpinion && self::columnExists('news', 'opinion_author_id')) {
            $where[] = "opinion_author_id IS NULL";
        }

        $whereSql = $where ? implode(' AND ', $where) : '1=1';

        $sql = "
            SELECT 
                id,
                title,
                slug,
                COALESCE(published_at, created_at) AS date,
                views
            FROM news
            WHERE {$whereSql}
            ORDER BY COALESCE(published_at, created_at) DESC, id DESC
            LIMIT {$limit}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $rows;
    }

    /**
     * جلب كتّاب الرأي لعرضهم في بلوك مخصص
     *
     * @param int $limit
     * @return array
     */
    public static function getOpinionAuthors(int $limit = 10): array
    {
        $pdo = self::db();
        if (!$pdo) {
            return [];
        }

        $limit = max(1, min($limit, 100));

        // Build a safe SELECT that works even if some optional columns are missing
        $cols = [];
        $cols[] = 'id';
        $cols[] = 'name';

        // slug
        $cols[] = (self::columnExists($pdo, 'opinion_authors', 'slug') ? 'slug' : "''") . ' AS slug';
        // avatar
        $cols[] = (self::columnExists($pdo, 'opinion_authors', 'avatar') ? 'avatar' : "''") . ' AS avatar';
        // page_title
        $cols[] = (self::columnExists($pdo, 'opinion_authors', 'page_title') ? 'page_title' : "''") . ' AS page_title';
        // email
        $cols[] = (self::columnExists($pdo, 'opinion_authors', 'email') ? 'email' : "''") . ' AS email';

        // social_facebook (fallback legacy `facebook`)
        $fb = self::columnExists($pdo, 'opinion_authors', 'social_facebook') ? 'social_facebook' : (self::columnExists($pdo, 'opinion_authors', 'facebook') ? 'facebook' : null);
        $cols[] = ($fb ? $fb : "''") . ' AS social_facebook';

        // social_twitter (fallback legacy `twitter`)
        $tw = self::columnExists($pdo, 'opinion_authors', 'social_twitter') ? 'social_twitter' : (self::columnExists($pdo, 'opinion_authors', 'twitter') ? 'twitter' : null);
        $cols[] = ($tw ? $tw : "''") . ' AS social_twitter';

        // social_website (fallback legacy `website`)
        $wb = self::columnExists($pdo, 'opinion_authors', 'social_website') ? 'social_website' : (self::columnExists($pdo, 'opinion_authors', 'website') ? 'website' : null);
        $cols[] = ($wb ? $wb : "''") . ' AS social_website';

        $cols[] = (self::columnExists($pdo, 'opinion_authors', 'created_at') ? 'created_at' : 'NULL') . ' AS created_at';
        $cols[] = (self::columnExists($pdo, 'opinion_authors', 'updated_at') ? 'updated_at' : 'NULL') . ' AS updated_at';

        $where = [];
        if (self::columnExists($pdo, 'opinion_authors', 'is_active')) {
            $where[] = 'is_active = 1';
        }
        $where[] = "TRIM(name) <> 'هيئة التحرير'";
        $whereSql = implode(' AND ', $where);

        $orderBy = self::columnExists($pdo, 'opinion_authors', 'updated_at') ? 'updated_at DESC, id DESC' : 'id DESC';

        $sql = "SELECT " .implode(",\n                ", $cols) . "\n            FROM opinion_authors\n            WHERE {$whereSql}\n            ORDER BY {$orderBy}\n            LIMIT {$limit}";

        try {
            $stmt = $pdo->query($sql);
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
            return $rows ?: [];
        } catch (\Throwable $e) {
            error_log('[HomeController] getOpinionAuthors error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * جلب الأخبار حسب سنة/شهر (أرشيف)
     *
     * @param int | null $year
     * @param int | null $month
     * @param int $limit
     * @return array
     */
    public static function getArchive(?int $year, ?int $month = null, int $limit = 50): array
    {
        $pdo = self::db();
        if (!$pdo) {
            return [];
        }

        $where = [];
        $params = [];
        $limit = max(1, min($limit, 200));

        if ($year !== null) {
            $where[] = "YEAR(created_at) = :year";
            $params[':year'] = $year;
        }

        if ($month !== null && $month >= 1 && $month <= 12) {
            $where[] = "MONTH(created_at) = :month";
            $params[':month'] = $month;
        }

        $whereSql = !empty($where) ? implode(' AND ', $where) : '1=1';

        $sql = "
            SELECT id, title, slug, created_at, views
            FROM news
            WHERE {$whereSql}
            ORDER BY created_at DESC
            LIMIT {$limit}
        ";
        $stmt = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $rows ?: [];
    }

    /**
     * عنوان افتراضي للصفحة مع اسم الموقع إن وُجد
     */
    public static function makePageTitle(string $titlePart): string
    {
        $settings = self::getSiteSettings();
        $siteName = $settings['site_name'] ?? 'Godyar';

        $titlePart = trim($titlePart);
        if ($titlePart === '') {
            return $siteName;
        }

        return $titlePart . '-' . $siteName;
    }
}