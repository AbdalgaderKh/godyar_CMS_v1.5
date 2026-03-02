/* Godyar Admin - Hotfix
   - Normalizes <svg><use href> to work with inline sprites
   - Falls back to external sprite if configured
*/
(function(){
  function normalizeUses(){
    try{
      var sprite = window.GDY_ICON_SPRITE || '';
      var uses = document.querySelectorAll('svg use');
      uses.forEach(function(u){
        var href = u.getAttribute('href') || u.getAttribute('xlink:href') || '';
        if(!href) return;

        // If it's an absolute URL like https://site/icons.svg#id
        // and we have the sprite in DOM, switch to #id.
        var hash = href.indexOf('#');
        if(hash > -1){
          var id = href.slice(hash);
          // Prefer inline sprite (no URL) to avoid CORS/MIME issues
          u.setAttribute('href', id);
          u.setAttribute('xlink:href', id);
          return;
        }

        // If it's already #id, keep.
        if(href.charAt(0) === '#') return;

        // If it's bare id, make it #id
        u.setAttribute('href', '#' + href);
        u.setAttribute('xlink:href', '#' + href);
      });
    }catch(e){}
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', normalizeUses);
  }else{
    normalizeUses();
  }
})();
