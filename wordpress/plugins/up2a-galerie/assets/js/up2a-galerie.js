/**
 * Module "Galerie" — extrait de wordpress/plugins/up2a-core/assets/js/
 * up2a-front-page.js (scission documentée dans docs/06-storyboard.md).
 */

/**
 * Galerie photo — cadre agrandi. La grande case de la grille fait défiler
 * toutes les photos de la galerie toutes les 10s (fondu enchaîné),
 * indépendamment des vignettes fixes. Met aussi à jour les données de la
 * carte (data-full/alt/legende) pour que la lightbox ouvre toujours la
 * photo actuellement affichée. Indépendant de GSAP.
 *
 * Perf (CLAUDE.md §7) : seuls 2 calques <img> existent dans le DOM (pas
 * un par photo) — à chaque fondu enchaîné, le calque qui vient de passer
 * en arrière-plan reçoit la src de la photo SUIVANTE, chargée à l'avance
 * mais une seule à la fois, plutôt que les 8 photos d'un coup au
 * chargement de la page.
 */
(function () {
  "use strict";

  var featured = document.querySelector(".js-galerie-featured");
  if (!featured) {
    return;
  }

  var stack = featured.querySelector(".js-galerie-featured-stack");
  if (!stack) {
    return;
  }

  var layers = stack.querySelectorAll(".up2a-galerie__featured-slide");
  var captionText = featured.querySelector(".js-galerie-caption-text");

  var photos = [];
  try {
    photos = JSON.parse(stack.dataset.photos || "[]");
  } catch (e) {
    photos = [];
  }

  if (layers.length < 2 || photos.length < 2) {
    return;
  }

  var prefersReducedMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (prefersReducedMotion) {
    return;
  }

  var activeLayer = 0;
  var currentPhoto = 0;

  function updateMeta(photo) {
    featured.dataset.full = photo.src;
    featured.dataset.alt = photo.alt;
    featured.dataset.legende = photo.legende;
    if (captionText) {
      captionText.textContent = photo.legende || "";
    }
  }

  updateMeta(photos[0]);

  setInterval(function () {
    var nextLayer = (activeLayer + 1) % 2;
    var nextPhoto = (currentPhoto + 1) % photos.length;

    layers[activeLayer].classList.remove("is-active");
    layers[nextLayer].classList.add("is-active");
    updateMeta(photos[nextPhoto]);

    activeLayer = nextLayer;
    currentPhoto = nextPhoto;

    var hiddenLayer = layers[(activeLayer + 1) % 2];
    var upcomingPhoto = photos[(currentPhoto + 1) % photos.length];
    hiddenLayer.src = upcomingPhoto.src;
    hiddenLayer.alt = upcomingPhoto.alt;
  }, 10000);
})();

/**
 * Galerie photo — grille + lightbox. Indépendant de GSAP : ouverture/
 * fermeture/navigation doivent fonctionner même si le vendor GSAP est
 * indisponible.
 */
(function () {
  "use strict";

  var items = document.querySelectorAll(".js-galerie-item");
  var lightbox = document.querySelector(".js-galerie-lightbox");

  if (!items.length || !lightbox) {
    return;
  }

  var img = lightbox.querySelector(".js-galerie-lightbox-img");
  var caption = lightbox.querySelector(".js-galerie-lightbox-caption");
  var count = lightbox.querySelector(".js-galerie-lightbox-count");
  var closeBtn = lightbox.querySelector(".js-galerie-close");
  var prevBtn = lightbox.querySelector(".js-galerie-prev");
  var nextBtn = lightbox.querySelector(".js-galerie-next");
  var current = 0;

  function open(index) {
    current = (index + items.length) % items.length;
    var item = items[current];
    img.src = item.dataset.full;
    img.alt = item.dataset.alt || "";
    if (caption) {
      caption.textContent = item.dataset.legende || "";
    }
    if (count) {
      count.textContent = current + 1 + " / " + items.length;
    }
    lightbox.classList.add("is-open");
    lightbox.setAttribute("aria-hidden", "false");
  }

  function close() {
    lightbox.classList.remove("is-open");
    lightbox.setAttribute("aria-hidden", "true");
    img.src = "";
  }

  items.forEach(function (item, index) {
    item.addEventListener("click", function () {
      open(index);
    });
  });

  closeBtn.addEventListener("click", close);
  prevBtn.addEventListener("click", function () {
    open(current - 1);
  });
  nextBtn.addEventListener("click", function () {
    open(current + 1);
  });

  lightbox.addEventListener("click", function (event) {
    if (event.target === lightbox) {
      close();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (!lightbox.classList.contains("is-open")) {
      return;
    }
    if (event.key === "Escape") {
      close();
    } else if (event.key === "ArrowLeft") {
      open(current - 1);
    } else if (event.key === "ArrowRight") {
      open(current + 1);
    }
  });
})();

/**
 * Vignettes Galerie en fondu + translation au scroll. S'appuie sur
 * window.UP2A (voir up2a-core.js) : ne fait rien si GSAP n'a pas pu se
 * charger — le contenu reste visible et statique, ce n'est qu'une
 * dégradation, jamais un blocage.
 */
(function () {
  "use strict";

  if (typeof window === "undefined" || !window.UP2A || typeof gsap === "undefined") {
    return;
  }

  var registerScrollEffect = window.UP2A.registerScrollEffect;

  var galerieItems = document.querySelectorAll(".js-galerie-item");
  if (galerieItems.length && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        gsap.from(galerieItems, {
          opacity: 0,
          y: 24,
          duration: 0.5,
          stagger: 0.08,
          ease: "power2.out",
          scrollTrigger: { trigger: ".js-galerie", start: "top 75%" },
        });
      },
    });
  }
})();
