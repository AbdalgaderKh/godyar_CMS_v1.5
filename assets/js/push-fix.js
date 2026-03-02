/* Godyar Push Fix (shared-host safe) */
(function(){
  function qs(sel, root){ return (root||document).querySelector(sel); }
  function on(el, ev, fn){ if(el) el.addEventListener(ev, fn, {passive:false}); }

  function toast(){ return qs('#gdy-push-toast'); }
  function hideToast(){ var t=toast(); if(!t) return; t.classList.remove('is-visible'); t.style.display='none'; }

  async function ensureSW(){
    if(!('serviceWorker' in navigator)) return null;
    try{
      // Always try root sw.js
      return await navigator.serviceWorker.register('/sw.js', { scope: '/' });
    }catch(e){
      // If site is installed in subfolder, fallback
      try{
        var base = (document.querySelector('link[rel="canonical"]')?.href || location.origin);
        var u = new URL(base);
        return await navigator.serviceWorker.register(u.origin + '/sw.js', { scope: '/' });
      }catch(_){
        return null;
      }
    }
  }

  async function requestPermission(){
    if(!('Notification' in window)) return 'unsupported';
    if(Notification.permission === 'granted') return 'granted';
    if(Notification.permission === 'denied') return 'denied';
    try{ return await Notification.requestPermission(); }catch(e){
      // Some browsers still use callback form
      return await new Promise(function(res){
        try{ Notification.requestPermission(function(p){ res(p); }); }
        catch(_){ res('default'); }
      });
    }
  }

  async function enablePush(){
    // If disabled in settings, just hide
    if(window.GDY_PUSH_ENABLED === false){ hideToast(); return; }

    var perm = await requestPermission();
    if(perm !== 'granted'){
      // User denied or dismissed
      hideToast();
      return;
    }

    // Ensure SW exists (needed for push)
    await ensureSW();

    // If your bundle already handles subscription, let it continue.
    // We just guarantee the UI works and the permission flow is triggered.
    hideToast();
  }

  function bind(){
    var t = toast();
    if(!t) return;
    // Force visible state to be clickable
    t.style.pointerEvents = 'auto';

    var btnEnable = qs('[data-gdy-push-enable]', t);
    var btnLater  = qs('[data-gdy-push-later]', t);

    on(btnEnable, 'click', function(ev){ ev.preventDefault(); ev.stopPropagation(); enablePush(); });
    on(btnLater,  'click', function(ev){ ev.preventDefault(); ev.stopPropagation(); hideToast(); });
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();
