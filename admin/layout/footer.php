<?php

// Admin footer (no additional file includes here)
// If site_setting() exists (loaded by bootstrap), we can show the site logo .
$__siteLogo = '';
if (function_exists('site_setting')) {
  // site_setting() signature changed in newer builds to: site_setting(PDO $pdo, string $key, mixed $default = null)
  // To avoid fatal errors across versions, detect parameter count and call appropriately .
  $__pc = 0;
  try {
    $__rf = new ReflectionFunction('site_setting');
    $__pc = $__rf->getNumberOfParameters();
  } catch (Exception $__e) {
    $__pc = 0;
  }

  if ($__pc >= 2) {
    // New signature: needs PDO as first arg
    global $pdo;
    if (isset($pdo) && ($pdo instanceof PDO)) {
      $__siteLogo = (string)site_setting($pdo, 'site_logo', '');
      if ($__siteLogo === '') { $__siteLogo = (string)site_setting($pdo, 'site.logo', ''); } // legacy fallback
    }
  } else {
    // Old signature: site_setting(string $key)
    $__siteLogo = (string)site_setting('site_logo');
    if ($__siteLogo === '') { $__siteLogo = (string)site_setting('site.logo'); } // legacy fallback
  }
}
?>
<footer class = "gdy-admin-footer" aria-label = "footer">
  <div class = "gdy-admin-footer__left">
    <span class = "gdy-admin-footer__logo" aria-hidden = "true">
      <?php if ($__siteLogo !== ''): ?>
        <img src = "<?php echo h($__siteLogo); ?>" alt = "">
      <?php else: ?>
        <span style = "font-weight:900;font-size:.95rem;opacity:.7;">G</span>
      <?php endif; ?>
    </span>
    <div style = "display:flex;flex-direction:column;line-height:1.1;">
      <span class = "gdy-admin-footer__brand">Godyar CMS</span>
      <span class = "gdy-admin-footer__muted">© <?php echo date('Y'); ?> جميع الحقوق محفوظة</span>
    </div>
  </div>

  <div class = "gdy-admin-badge">Godyar CMS <?php echo defined('GDY_VERSION') ? h((string)GDY_VERSION) : 'v1.11'; ?></div>
</footer>
