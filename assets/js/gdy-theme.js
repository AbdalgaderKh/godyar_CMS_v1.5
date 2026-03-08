/*!
 * Godyar Theme Toggle (CSP-safe, no inline)
 * - Syncs theme across: <html data-theme>, <body class="dark-mode">
 * - Supports legacy keys: gdy_theme, godyar-theme
 * - Updates toggle icon <use href="...#moon|#sun">
 */
(function () {
  "use strict";

  function getStoredTheme() {
    return localStorage.getItem("gdy_theme") ||
           localStorage.getItem("godyar-theme") ||
           localStorage.getItem("gdy-theme") ||
           "light";
  }

  function storeTheme(t) {
    localStorage.setItem("gdy_theme", t);
    localStorage.setItem("godyar-theme", t);
    localStorage.setItem("gdy-theme", t);
  }

  function setDocTheme(t) {
    var html = document.documentElement;
    var body = document.body;

    html.setAttribute("data-theme", t);
    if (t === "dark") body.classList.add("dark-mode");
    else body.classList.remove("dark-mode");

    // ARIA state (pressed = dark)
    var btn = document.querySelector('[data-gdy-theme-toggle], #gdyThemeToggle, #gdyTabTheme');
    if (btn) btn.setAttribute("aria-pressed", t === "dark" ? "true" : "false");

    // Swap icon (moon -> sun)
    var useEl = btn ? btn.querySelector("use") : null;
    if (useEl) {
      // Modern SVG2: use href فقط
      var href = useEl.getAttribute("href") || "";
      // Normalize: keep same sprite path but switch fragment
      var base = href.split("#")[0] || "";
      var frag = (t === "dark") ? "sun" : "moon";
      var next = (base ? (base + "#"+frag) : ("#"+frag));
      useEl.setAttribute("href", next);
      try { useEl.setAttributeNS("http://www.w3.org/1999/xlink", "href", next); } catch (e) {}
    }
  }

  function init() {
    // Apply stored theme
    var t = getStoredTheme();
    if (t !== "dark") t = "light";
    setDocTheme(t);

    // Bind click
    var btns = document.querySelectorAll('[data-gdy-theme-toggle], #gdyThemeToggle, #gdyTabTheme');
    btns.forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        var current = (document.documentElement.getAttribute("data-theme") || "light");
        var next = (current === "dark") ? "light" : "dark";
        storeTheme(next);
        setDocTheme(next);
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
