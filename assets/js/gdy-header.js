/* Godyar Header Interactions (CSP-safe, no deps)
   - Dropdowns (account/lang) + outside click + ESC
   - Mobile search toggle (shows header search on small screens)
*/
(function () {
  'use strict';

  function q(sel, root) { return (root || document).querySelector(sel); }
  function qa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function closeAllDropdowns(except) {
    qa('.hdr-dropdown.open').forEach(function (dd) {
      if (except && dd === except) return;
      dd.classList.remove('open');
      var btn = q('[data-hdr-dd]', dd);
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  function toggleDropdown(btn) {
    var dd = btn.closest('.hdr-dropdown');
    if (!dd) return;

    var willOpen = !dd.classList.contains('open');
    closeAllDropdowns(dd);

    if (willOpen) {
      dd.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
    } else {
      dd.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }
  }

  // Sanitize initial state (in case templates/cache render dropdowns as open)
  qa('.hdr-dropdown').forEach(function (dd) {
    dd.classList.remove('open');
    var btn = q('[data-hdr-dd]', dd);
    if (btn) btn.setAttribute('aria-expanded', 'false');
  });

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-hdr-dd]');
    if (btn) {
      e.preventDefault();
      e.stopPropagation();
      toggleDropdown(btn);
      return;
    }

    // Click outside: close all
    if (!e.target.closest('.hdr-dropdown')) closeAllDropdowns();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllDropdowns();
  });

  // Mobile search toggle
  function initMobileSearch() {
    var btn = q('[data-mobile-search-btn]') || q('#gdyMobileSearchBtn');
    var headerRoot = q('[data-hdr-root]') || q('.site-header');
    var form = q('.header-search', headerRoot);
    var input = form ? q('input[type="search"], input[name="q"], input', form) : null;

    if (!btn || !headerRoot || !form) return;

    btn.addEventListener('click', function () {
      headerRoot.classList.toggle('is-search-open');
      var isOpen = headerRoot.classList.contains('is-search-open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

      if (isOpen && input) {
        setTimeout(function () { try { input.focus(); } catch (_) {} }, 50);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileSearch);
  } else {
    initMobileSearch();
  }
})();
