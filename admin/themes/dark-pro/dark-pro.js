(function(){
  function on(sel, ev, fn){
    var el=document.querySelector(sel);
    if(el) el.addEventListener(ev, fn);
  }
  // Mobile sidebar toggle -> re-use existing #sidebarToggle if present
  on('#gdyTopbarBurger','click', function(){
    var t=document.getElementById('sidebarToggle');
    if(t) t.click();
  });

  // Topbar search -> go to admin search
  var s=document.getElementById('gdyTopbarSearch');
  if(s){
    s.addEventListener('keydown', function(e){
      if(e.key==='Enter'){
        var base=(window.GDY_ADMIN_URL||'')+'';
        if(!base){ base='/admin'; }
        location.href= base.replace(/\/$/,'') + '/search/?q=' + encodeURIComponent(s.value||'');
      }
    });
  }
})();
