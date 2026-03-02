/* Godyar: categories + mobile search toggles (CSP-safe, no inline) */
(function(){
  'use strict';

  function ready(fn){
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    // Categories dropdown (desktop)
    var btn = document.querySelector('[data-cats-toggle]');
    var nav = document.querySelector('[data-cats-nav]');
    if (btn && nav) {
      btn.addEventListener('click', function(){
        var expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        nav.classList.toggle('is-open', !expanded);
      });

      // Close when clicking outside
      document.addEventListener('click', function(e){
        if (!nav.classList.contains('is-open')) return;
        if (btn.contains(e.target) || nav.contains(e.target)) return;
        btn.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
      });

      // Close on ESC
      document.addEventListener('keydown', function(e){
        if (e.key !== 'Escape') return;
        if (!nav.classList.contains('is-open')) return;
        btn.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
      });
    }

    // Mobile search button: focus the header search input
    var mobileSearchBtn = document.querySelector('[data-mobile-search-btn]');
    var searchInput = document.getElementById('hdrSearchQ');
    if (mobileSearchBtn && searchInput) {
      mobileSearchBtn.addEventListener('click', function(){
        try { searchInput.focus({ preventScroll: false }); } catch(_) { searchInput.focus(); }
      });
    }
  });
})();
