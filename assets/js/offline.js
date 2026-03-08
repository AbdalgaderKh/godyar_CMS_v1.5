/* Godyar CMS — Offline page helpers (safe, no inline JS) */
(function () {
  function qs(sel) { return document.querySelector(sel); }

  function setStatus(isOnline) {
    var el = qs('.status');
    if (!el) return;

    if (isOnline) {
      el.textContent = 'تم الاتصال. جارٍ إعادة التحميل...';
      setTimeout(function () {
        try { window.location.reload(); } catch (e) {}
      }, 400);
    } else {
      el.textContent = 'أنت غير متصل بالإنترنت.';
    }
  }

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  onReady(function () {
    var btn = document.getElementById('btnReload');
    if (btn) {
      btn.addEventListener('click', function (ev) {
        ev.preventDefault();
        try { window.location.reload(); } catch (e) {}
      });
    }

    // Initial status
    setStatus(navigator.onLine);

    // Live updates
    window.addEventListener('online', function () { setStatus(true); });
    window.addEventListener('offline', function () { setStatus(false); });
  });
})();
