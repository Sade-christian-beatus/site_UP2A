/**
 * Bascule du panneau de menu de l'en-tête (wordpress/plugins/up2a-core/inc/header.php).
 * Indépendant de up2a-core.js (GSAP/Lenis) pour rester utilisable même si
 * ce dernier échoue à charger (CDN indisponible, etc.).
 */
(function () {
  "use strict";

  var header = document.getElementById("up2a-header");
  var toggle = header && header.querySelector(".js-up2a-menu-toggle");

  if (!header || !toggle) {
    return;
  }

  toggle.addEventListener("click", function () {
    var isOpen = header.classList.toggle("is-nav-open");
    toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && header.classList.contains("is-nav-open")) {
      header.classList.remove("is-nav-open");
      toggle.setAttribute("aria-expanded", "false");
      toggle.focus();
    }
  });
})();
