/* GDY environment bootstrap (CSP-safe, no inline scripts) */
(function () {
  try {
    var d = document.documentElement;
    var body = document.body || {};
    var base = (d.getAttribute('data-base-url') || body.getAttribute && body.getAttribute('data-base-url')) || '';
    var sw   = (d.getAttribute('data-sw-url')   || body.getAttribute && body.getAttribute('data-sw-url'))   || '/sw.js';
    if (!base) {
      // fallback: derive from <base> tag or location
      base = (document.querySelector('base') && document.querySelector('base').href) || (location.origin || '');
      base = (base || '').replace(/\/$/, '');
    }
    window.GDY_BASE = base;
    window.GDY_SW_URL = sw || '/sw.js';
  } catch (e) {}
})();
