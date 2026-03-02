<?php
declare(strict_types=1);

/**
 * Footer (Stable)
 * - يمنع أخطاء: u() / __() / asset_url() غير موجودة
 * - لا يكسر الصفحة حتى لو DB غير جاهزة
 * - CSP friendly
 */

if (defined('GDY_FOOTER_RENDERED')) { return; }
define('GDY_FOOTER_RENDERED', true);

if (!function_exists('h')) {
  function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Safe URL helper
if (!function_exists('u')) {
  function u(string $url): string {
    $url = trim($url);
    if ($url === '') return '';
    if (preg_match('~^https?://~i', $url)) return h($url);
    if (preg_match('~^(//|javascript:)~i', $url)) return '#';
    if ($url[0] === '/') {
      $base = function_exists('base_url') ? rtrim((string)base_url(), '/') : '';
      return h($base . $url);
    }
    return h($url);
  }
}

// Translation fallback
if (!function_exists('__')) {
  function __(string $key, array $vars = []): string {
    $out = $key;
    foreach ($vars as $k => $v) {
      $out = str_replace('{' . $k . '}', (string)$v, $out);
    }
    return $out;
  }
}

// asset_url fallback (لو functions.php لم يُحمّل في بعض الصفحات)
if (!function_exists('asset_url')) {
  function asset_url(string $path = ''): string {
    $path = ltrim($path, '/');
    $base = function_exists('base_url') ? rtrim((string)base_url(), '/') : '';
    return $base . '/' . $path;
  }
}

$baseUrl = function_exists('base_url') ? rtrim((string)base_url(), '/') : '';
$_gdy_lang = function_exists('gdy_lang') ? (string)gdy_lang() : (string)($_SESSION['lang'] ?? 'ar');
$_gdy_lang = trim($_gdy_lang, '/');
$_gdy_navBaseUrl = $baseUrl . '/' . $_gdy_lang;

// Site settings (لا تكسر إن DB غير جاهزة)
$siteSettings = [];
if (class_exists('HomeController')) {
  try {
    $tmp = HomeController::getSiteSettings();
    $siteSettings = is_array($tmp) ? $tmp : [];
  } catch (Throwable $e) {
    $siteSettings = [];
  }
}

$siteName    = (string)($siteSettings['site_name'] ?? 'Godyar News');
$siteTagline = (string)($siteSettings['site_tagline'] ?? 'منصة إخبارية متكاملة');
$desc        = trim((string)($siteSettings['site_description'] ?? ''));

$logoRaw = trim((string)($siteSettings['site_logo'] ?? ''));
$logoUrl = '';
if ($logoRaw !== '') {
  $logoUrl = preg_match('~^https?://~i', $logoRaw) ? $logoRaw : ($baseUrl . '/' . ltrim($logoRaw, '/'));
}

$siteEmail = (string)($siteSettings['site_email'] ?? '');
$sitePhone = (string)($siteSettings['site_phone'] ?? '');
$siteAddr  = (string)($siteSettings['site_address'] ?? '');

$iconsSprite = asset_url('assets/icons/godyar-icons.svg');
$gdyIsUser = (!empty($_SESSION['user']) || !empty($_SESSION['user_id']) || !empty($_SESSION['user_email']));

$social = [
  'facebook'  => trim((string)($siteSettings['facebook_url'] ?? $siteSettings['social_facebook'] ?? '')),
  'twitter'   => trim((string)($siteSettings['twitter_url'] ?? $siteSettings['x_url'] ?? $siteSettings['social_twitter'] ?? '')),
  'instagram' => trim((string)($siteSettings['instagram_url'] ?? $siteSettings['social_instagram'] ?? '')),
  'youtube'   => trim((string)($siteSettings['youtube_url'] ?? $siteSettings['social_youtube'] ?? '')),
  'telegram'  => trim((string)($siteSettings['telegram_url'] ?? $siteSettings['social_telegram'] ?? '')),
  'whatsapp'  => trim((string)($siteSettings['whatsapp_url'] ?? $siteSettings['social_whatsapp'] ?? '')),
];

?>

<?php if (defined('GDY_HEADER_RENDERED')): ?>
      </div>
    </div>
  </main>
<?php endif; ?>

<footer class="gdy-footer">
  <div class="gdy-footer-top">
    <div class="container">
      <div class="gdy-footer-grid">

        <section class="gdy-footer-card">
          <a class="gdy-footer-brand__link" href="<?php echo u($_gdy_navBaseUrl . '/'); ?>" aria-label="<?php echo h($siteName); ?>">
            <span class="gdy-footer-brand__logo" aria-hidden="true">
              <?php if ($logoUrl !== ''): ?>
                <img src="<?php echo h($logoUrl); ?>" alt="<?php echo h($siteName); ?>" loading="lazy" decoding="async">
              <?php else: ?>
                <span class="gdy-footer-brand__logo-fallback"><?php echo h(mb_substr($siteName, 0, 1)); ?></span>
              <?php endif; ?>
            </span>
            <span>
              <div><?php echo h($siteName); ?></div>
              <div><?php echo h($siteTagline); ?></div>
            </span>
          </a>
          <!-- Badge logo (theme-aware contrast) -->
          <div class="gdy-footer-badge" aria-hidden="true">
            <?php if ($logoUrl !== ''): ?>
              <img src="<?php echo h($logoUrl); ?>" alt="" loading="lazy" decoding="async">
            <?php else: ?>
              <span class="gdy-footer-brand__logo-fallback"><?php echo h(mb_substr($siteName, 0, 1)); ?></span>
            <?php endif; ?>
          </div>

          <?php if ($desc !== '' && $desc !== $siteTagline): ?>
            <div><p><?php echo h($desc); ?></p></div>
          <?php endif; ?>
        </section>

        <section class="gdy-footer-card">
          <h4>روابط سريعة</h4>
          <ul class="gdy-footer-links">
            <li><a href="<?php echo u($_gdy_navBaseUrl . '/page/about'); ?>">من نحن</a></li>
            <li><a href="<?php echo u($_gdy_navBaseUrl . '/page/privacy'); ?>">سياسة الخصوصية</a></li>
            <li><a href="<?php echo u($_gdy_navBaseUrl . '/page/terms'); ?>">الشروط والأحكام</a></li>
            <li><a href="<?php echo u($_gdy_navBaseUrl . '/contact'); ?>">اتصل بنا</a></li>
            <li><a href="<?php echo u($_gdy_navBaseUrl . '/sitemap.xml'); ?>">خريطة الموقع</a></li>
          </ul>
        </section>

        <section class="gdy-footer-card">
          <h4>تواصل معنا</h4>
          <div>
            <?php if ($siteEmail !== ''): ?>
	              <div><svg class="gdy-icon ms-1" aria-hidden="true"><use href="<?php echo h($iconsSprite); ?>#mail" xlink:href="<?php echo h($iconsSprite); ?>#mail"></use></svg> <?php echo h($siteEmail); ?></div>
            <?php endif; ?>
            <?php if ($sitePhone !== ''): ?>
	              <div><svg class="gdy-icon ms-1" aria-hidden="true"><use href="<?php echo h($iconsSprite); ?>#phone" xlink:href="<?php echo h($iconsSprite); ?>#phone"></use></svg> <?php echo h($sitePhone); ?></div>
            <?php endif; ?>
            <?php if ($siteAddr !== ''): ?>
	              <div><svg class="gdy-icon ms-1" aria-hidden="true"><use href="<?php echo h($iconsSprite); ?>#more-h" xlink:href="<?php echo h($iconsSprite); ?>#more-h"></use></svg> <?php echo h($siteAddr); ?></div>
            <?php endif; ?>
          </div>

          <div class="gdy-social">
            <?php
              $iconMap = [
                'facebook'=>'facebook','twitter'=>'twitter','instagram'=>'instagram','youtube'=>'youtube','telegram'=>'telegram','whatsapp'=>'whatsapp'
              ];
              $printed = 0;
              foreach ($social as $k => $v) {
                if ($v === '') continue;
                $printed++;
                $iconId = $iconMap[$k] ?? 'more-h';
	                $ref = h($iconsSprite) . '#' . h($iconId);
	                echo '<a href="' . u($v) . '" target="_blank" rel="noopener noreferrer" aria-label="' . h($k) . '">'
	                  . '<svg class="gdy-icon" aria-hidden="true"><use href="' . $ref . '" xlink:href="' . $ref . '"></use></svg>'
                  . '</a>';
              }
              if ($printed === 0) {
                echo '<div class="gdy-footer-muted">أضف روابط التواصل الاجتماعي من لوحة التحكم</div>';
              }
            ?>
          </div>
        </section>

      </div>
    </div>
  </div>

  <div class="gdy-footer-bottom">
    <div class="container">
      <div class="inner">
        <div>© <?php echo (int)date('Y'); ?> <?php echo h($siteName); ?> — جميع الحقوق محفوظة</div>
        <div>تصميم وتطوير: <span>Godyar</span></div>
      </div>
    </div>
  </div>

  <button type="button" id="gdyBackTop" class="gdy-backtop" aria-label="العودة للأعلى" title="العودة للأعلى">
	    <svg class="gdy-icon" aria-hidden="true"><use href="<?php echo h($iconsSprite); ?>#arrow-up" xlink:href="<?php echo h($iconsSprite); ?>#arrow-up"></use></svg>
  </button>
</footer>

<nav class="gdy-mobile-bar" id="gdyMobileBar" aria-label="التنقل">
  <a class="mb-item" href="<?php echo u($_gdy_navBaseUrl . '/'); ?>" data-tab="home" aria-label="الرئيسية">
	    <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="<?php echo h($iconsSprite); ?>#home" xlink:href="<?php echo h($iconsSprite); ?>#home"></use></svg><span>الرئيسية</span>
  </a>
  <button class="mb-item" type="button" data-action="cats" data-tab="cats" aria-label="الأقسام">
	    <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="<?php echo h($iconsSprite); ?>#menu" xlink:href="<?php echo h($iconsSprite); ?>#menu"></use></svg><span>الأقسام</span>
  </button>
  <a class="mb-item" href="<?php echo u($_gdy_navBaseUrl . '/saved'); ?>" data-tab="saved" aria-label="محفوظاتي">
	    <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="<?php echo h($iconsSprite); ?>#bookmark" xlink:href="<?php echo h($iconsSprite); ?>#bookmark"></use></svg><span>محفوظاتي</span>
  </a>
  <?php if ($gdyIsUser): ?>
    <a class="mb-item" href="<?php echo u($_gdy_navBaseUrl . '/profile.php'); ?>" data-tab="profile" aria-label="حسابي">
	      <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="<?php echo h($iconsSprite); ?>#user" xlink:href="<?php echo h($iconsSprite); ?>#user"></use></svg><span>حسابي</span>
    </a>
  <?php else: ?>
    <a class="mb-item" href="<?php echo u($_gdy_navBaseUrl . '/login'); ?>" data-tab="login" aria-label="دخول">
      <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="<?php echo h($iconsSprite); ?>#user"></use></svg><span>دخول</span>
    </a>
  <?php endif; ?>
  <button class="mb-item" type="button" data-action="theme" aria-label="الوضع الليلي">
    <svg class="gdy-icon" aria-hidden="true" focusable="false"><use href="<?php echo h($iconsSprite); ?>#moon"></use></svg><span>ليلي</span>
  </button>
</nav>

<?php
  // ✅ تحميل سكربتات الواجهة
    // Expose VAPID public key for web push (used by assets/js/modules/notifications.js)
  $pushEnabled = function_exists('settings_get') ? (string)settings_get('push.enabled', '0') : '0';
  $vapidPublic = function_exists('settings_get') ? (string)settings_get('push.vapid_public', '') : '';
  $cspNonce = defined('GDY_CSP_NONCE') ? (string)GDY_CSP_NONCE : '';
  echo '<script' . ($cspNonce !== '' ? ' nonce="' . h($cspNonce) . '"' : '') . '>';
  echo 'window.GDY_PUSH_ENABLED=' . json_encode(($pushEnabled === '1'), JSON_UNESCAPED_SLASHES) . ';';
  echo 'window.GDY_VAPID_PUBLIC_KEY=' . json_encode($vapidPublic, JSON_UNESCAPED_SLASHES) . ';';
  echo 'window.GDY_ASSET_VER=' . json_encode((string)($siteSettings['assets_version'] ?? (defined('GODYAR_VERSION') ? GODYAR_VERSION : '20260226')), JSON_UNESCAPED_SLASHES) . ';';
  echo '</script>' . "\n";

  $bundle = '/assets/js/godyar.bundle.js';
  $ver = preg_replace('~[^0-9A-Za-z._-]~','',(string)($siteSettings['assets_version'] ?? (defined('GODYAR_VERSION') ? GODYAR_VERSION : '20260226')));
  if ($ver === '') $ver = '20260226';
  echo '<script defer src="' . h($baseUrl . $bundle . '?v=' . $ver) . '"></script>' . "\n";
  // Report/News tools (copy/share/bookmark/tts/reading mode)
  $rt = '/assets/js/news-report-tools.js';
  echo '<script defer src="' . h($baseUrl . $rt . '?v=' . $ver) . '"></script>' . "\n";
  $fix = '/assets/js/push-fix.js';
  echo '<script defer src="' . h($baseUrl . $fix . '?v=' . $ver) . '"></script>' . "\n";
?>

<div id="gdy-push-toast" class="gdy-push-toast" role="dialog" aria-live="polite" aria-label="Push Prompt">
  <div class="gdy-push-toast__title">تفعيل إشعارات الأخبار</div>
  <div class="gdy-push-toast__desc">وصل تنبيه لأهم الأخبار على جهازك . يمكنك إيقافها في أي وقت .</div>
  <div class="gdy-push-toast__actions">
    <button type="button" class="gdy-btn gdy-btn-primary" data-gdy-push-enable>تفعيل</button>
    <button type="button" class="gdy-btn gdy-btn-ghost" data-gdy-push-later>لاحقاً</button>
  </div>
</div>

</body>
</html>
