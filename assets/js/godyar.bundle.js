/* Godyar bundle (NEWSTRUCT) */
/* Godyar UI interactions (CSP-friendly, no inline)
   - Theme toggle (light/dark) stored in localStorage
   - Mobile search overlay
   - Back-to-top
   - Mobile bar actions
*/
(function(){
  'use strict';

  var LS_KEY = 'gdy_theme';

  function getRoot(){ return document.documentElement; }

  function setTheme(theme){
    var root = getRoot();
    if (!root) return;
    var t = (theme === 'dark') ? 'dark' : 'light';
    root.setAttribute('data-theme', t);
    // For older CSS hooks
    document.body && document.body.classList.toggle('is-dark', t === 'dark');

    try { localStorage.setItem(LS_KEY, t); } catch(_e) {}

    // Update aria-pressed when present
    document.querySelectorAll('[data-gdy-theme-toggle], [data-action="theme"], #gdyTabTheme').forEach(function(btn){
      try { btn.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false'); } catch(_e) {}
    });
  }

  function toggleTheme(){
    var root = getRoot();
    var cur = (root && root.getAttribute('data-theme')) || 'light';
    setTheme(cur === 'dark' ? 'light' : 'dark');
  }

  function initTheme(){
    var root = getRoot();
    if (!root) return;
    var saved = '';
    try { saved = localStorage.getItem(LS_KEY) || ''; } catch(_e) {}
    if (saved === 'dark' || saved === 'light') {
      setTheme(saved);
    }
  }

  function qs(id){ return document.getElementById(id); }

  function closeAllDropdowns(exceptEl){
    document.querySelectorAll('.hdr-dropdown.open').forEach(function(dd){
      if (exceptEl && dd === exceptEl) return;
      dd.classList.remove('open');
      var btn = dd.querySelector('[data-hdr-dd]');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  function toggleDropdownFromBtn(btn){
    var dd = btn.closest('.hdr-dropdown');
    if (!dd) return;
    var isOpen = dd.classList.contains('open');
    closeAllDropdowns(dd);
    dd.classList.toggle('open', !isOpen);
    btn.setAttribute('aria-expanded', (!isOpen) ? 'true' : 'false');
  }

  function toggleCats(){
    var btn = document.querySelector('[data-cats-toggle], #gdyCatsToggle');
    var nav = document.querySelector('[data-cats-nav], #gdyCatsNav');
    if (!btn || !nav) return;
    var isOpen = nav.classList.contains('open');
    nav.classList.toggle('open', !isOpen);
    btn.setAttribute('aria-expanded', (!isOpen) ? 'true' : 'false');
  }

  function openMobileSearch(){
    var box = qs('gdyMobileSearch');
    // Preferred overlay (if present)
    if (box) {
      box.hidden = false;
      box.classList.add('open');
      var input = qs('gdyMobileSearchInput');
      if (input) { try { input.focus(); } catch(_e) {} }
      return;
    }

    // Fallback: toggle header inline search
    document.body && document.body.classList.toggle('hdr-search-open', true);
    var hdrQ = qs('hdrSearchQ');
    if (hdrQ) { try { hdrQ.focus(); } catch(_e) {} }
  }

  function closeMobileSearch(){
    var box = qs('gdyMobileSearch');
    if (box) {
      box.hidden = true;
      box.classList.remove('open');
    }
    document.body && document.body.classList.toggle('hdr-search-open', false);
  }

  function backToTop(){
    try {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch(_e) {
      window.scrollTo(0, 0);
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    initTheme();

    // Prevent double submit (e.g., on slow shared hosting)
    document.addEventListener('submit', function(e){
      try{
        var f = e.target;
        if (!f || !f.getAttribute) return;
        if (String(f.getAttribute('data-gdy-once') || '') !== '1') return;
        var btn = f.querySelector && f.querySelector('button[type="submit"],input[type="submit"]');
        if (btn) {
          btn.disabled = true;
          btn.setAttribute('aria-disabled','true');
        }
      } catch(_e) {}
    }, true);

    // Push prompt (toast)
    (function initPushToast(){
      try{
        var enabled = !!window.GDY_PUSH_ENABLED;
        var vapid   = (window.GDY_VAPID_PUBLIC_KEY || '').trim();
        if(!enabled || !vapid) return;
        var toast = document.getElementById('gdy-push-toast');
        if(!toast) return;
        // Respect user dismissal
        var dismissed = false;
        try{ dismissed = (localStorage.getItem('gdy_push_toast_dismissed') === '1'); }catch(e){}
        if(dismissed) return;
        // Show toast on both mobile/desktop (CSS may hide on hover devices; class forces display)
        toast.classList.remove('hidden');
        toast.classList.add('is-visible');

        // Attach direct listeners (shared hosting setups sometimes ship older JS where delegated closest() fails)
        var btnEnable = toast.querySelector('[data-gdy-push-enable]');
        var btnLater  = toast.querySelector('[data-gdy-push-later]');
        function dismiss(){
          try{ localStorage.setItem('gdy_push_toast_dismissed', '1'); }catch(_e){}
          toast.classList.add('hidden');
          toast.classList.remove('is-visible');
        }
        if (btnLater && !btnLater.__gdy_bound) {
          btnLater.__gdy_bound = true;
          btnLater.addEventListener('click', function(ev){ ev.preventDefault(); dismiss(); });
        }
        if (btnEnable && !btnEnable.__gdy_bound) {
          btnEnable.__gdy_bound = true;
          btnEnable.addEventListener('click', function(ev){
            ev.preventDefault();
            dismiss();
            (async function(){
              try{
                if (!('Notification' in window)) return;
                var perm = Notification.permission;
                if (perm === 'default') perm = await Notification.requestPermission();
                if (perm !== 'granted') return;
                if (!('serviceWorker' in navigator)) return;
                var swUrl = (window.GDY_SW_URL || '/sw.js');
                // If installed under a subfolder, use that folder's /sw.js
                try{
                  if (!window.GDY_SW_URL && window.GDY_BASE) {
                    var _u = new URL(String(window.GDY_BASE), location.origin);
                    if (_u.pathname && _u.pathname !== '/' ) {
                      var _p = _u.pathname; if (_p.slice(-1) !== '/') _p += '/';
                      swUrl = _p + 'sw.js';
                    }
                  }
                }catch(__e){}
                var __GDY_SW_COMPUTE__ = 1;
                var scope = '/';
                try{
                  if (window.GDY_BASE) {
                    var u = new URL(String(window.GDY_BASE), location.origin);
                    scope = u.pathname || '/';
                    if (scope.slice(-1) !== '/') scope += '/';
                  }
                } catch(_e) {}
                var reg = await navigator.serviceWorker.register(swUrl, { scope: scope });
                if (reg && reg.pushManager && window.GDY_VAPID_PUBLIC_KEY) {
                  var key = String(window.GDY_VAPID_PUBLIC_KEY || '').trim();
                  if (!key) return;
                  function b64ToU8(b64){
                    b64 = b64.replace(/-/g,'+').replace(/_/g,'/');
                    var pad = b64.length % 4; if(pad){ b64 += '='.repeat(4-pad); }
                    var raw = atob(b64);
                    var arr = new Uint8Array(raw.length);
                    for (var i=0;i<raw.length;i++) arr[i]=raw.charCodeAt(i);
                    return arr;
                  }
                  try{ await reg.pushManager.subscribe({ userVisibleOnly:true, applicationServerKey: b64ToU8(key) }); }catch(_e){}
                }
              }catch(_e){}
            })();
          });
        }
      }catch(e){}
    })();

    // Delegated clicks
    document.addEventListener('click', function(e){
      // Header dropdowns (language/account)
      var ddBtn = e.target.closest && e.target.closest('[data-hdr-dd]');
      if (ddBtn) {
        e.preventDefault();
        toggleDropdownFromBtn(ddBtn);
        return;
      }

      // Categories toggle
      var catsBtn = e.target.closest && e.target.closest('[data-cats-toggle], #gdyCatsToggle');
      if (catsBtn) {
        e.preventDefault();
        toggleCats();
        return;
      }

      // Mobile bar categories button
      var mbCats = e.target.closest && e.target.closest('.gdy-mobile-bar [data-action="cats"]');
      if (mbCats) {
        e.preventDefault();
        toggleCats();
        return;
      }

      var tgl = e.target.closest && e.target.closest('[data-gdy-theme-toggle], [data-action="theme"], #gdyTabTheme');
      if (tgl) {
        e.preventDefault();
        toggleTheme();
        return;
      }

      var msBtn = e.target.closest && e.target.closest('[data-mobile-search-btn], #gdyMobileSearchBtn');
      if (msBtn) {
        e.preventDefault();
        openMobileSearch();
        return;
      }

      var msClose = e.target.closest && e.target.closest('#gdyMobileSearchClose, .gdy-search__close');
      if (msClose) {
        e.preventDefault();
        closeMobileSearch();
        return;
      }

      var bt = e.target.closest && e.target.closest('#gdyBackTop, .gdy-backtop');
      if (bt) {
        e.preventDefault();
        backToTop();
        return;
      }

      // Close mobile search when clicking backdrop area
      var box = qs('gdyMobileSearch');
      if (box && !box.hidden) {
        if (e.target === box) {
          closeMobileSearch();
          return;
        }
      }

      // Close dropdowns when clicking outside
      var openDd = document.querySelector('.hdr-dropdown.open');
      if (openDd && !(e.target.closest && e.target.closest('.hdr-dropdown.open'))) {
        closeAllDropdowns();
      }

      // Push toast actions
      var pushEnable = e.target.closest && e.target.closest('[data-gdy-push-enable]');
      var pushLater  = e.target.closest && e.target.closest('[data-gdy-push-later]');
      if (pushEnable || pushLater) {
        var toast = qs('gdy-push-toast');
        try{ localStorage.setItem('gdy_push_toast_dismissed', '1'); }catch(e){}
        if (toast){ toast.classList.add('hidden'); toast.classList.remove('is-visible'); }

        // If user clicked enable, request permission and (best-effort) register a service worker.
        if (pushEnable) {
          (async function(){
            try{
              if (!('Notification' in window)) return;
              var perm = Notification.permission;
              if (perm === 'default') {
                perm = await Notification.requestPermission();
              }
              if (perm !== 'granted') return;

              // Best-effort SW registration + subscription. Server-side storage is required to actually receive pushes.
              if (!('serviceWorker' in navigator)) return;
              // Respect configured SW URL / base path
              var swUrl = (window.GDY_SW_URL || '/sw.js');
                // If installed under a subfolder, use that folder's /sw.js
                try{
                  if (!window.GDY_SW_URL && window.GDY_BASE) {
                    var _u = new URL(String(window.GDY_BASE), location.origin);
                    if (_u.pathname && _u.pathname !== '/' ) {
                      var _p = _u.pathname; if (_p.slice(-1) !== '/') _p += '/';
                      swUrl = _p + 'sw.js';
                    }
                  }
                }catch(__e){}
                var __GDY_SW_COMPUTE__ = 1;
              var scope = '/';
              try{
                if (window.GDY_BASE) {
                  var u = new URL(String(window.GDY_BASE), location.origin);
                  scope = u.pathname || '/';
                  if (scope.slice(-1) !== '/') scope += '/';
                }
              } catch(_e) {}
              var reg = await navigator.serviceWorker.register(swUrl, { scope: scope });

              if (reg && reg.pushManager && window.GDY_VAPID_PUBLIC_KEY) {
                var key = window.GDY_VAPID_PUBLIC_KEY;
                function b64ToU8(b64){
                  b64 = b64.replace(/-/g,'+').replace(/_/g,'/');
                  var pad = b64.length % 4; if(pad){ b64 += '='.repeat(4-pad); }
                  var raw = atob(b64);
                  var arr = new Uint8Array(raw.length);
                  for (var i=0;i<raw.length;i++) arr[i]=raw.charCodeAt(i);
                  return arr;
                }
                try{
                  await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: b64ToU8(key)
                  });
                }catch(e){}
              }
            }catch(e){}
          })();
        }
      }
    }, true);


    // ESC closes mobile search
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape') {
        closeMobileSearch();
        closeAllDropdowns();
      }
    });
  });
})();

