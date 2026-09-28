/**
 * Module "Formations" — extrait de wordpress/plugins/up2a-core/assets/js/
 * up2a-front-page.js (scission documentée dans docs/06-storyboard.md).
 */

/**
 * Modale de détail d'une formation. Chaque carte clone le contenu de son
 * <template> associé (même `data-formation`) dans la modale — indépendant
 * de GSAP : ouverture/fermeture/navigation doivent fonctionner même si le
 * CDN/vendor GSAP échoue.
 */
(function () {
  "use strict";

  var cards = document.querySelectorAll(".js-formations-card");
  var modal = document.querySelector(".js-formation-modal");

  if (!cards.length || !modal) {
    return;
  }

  var content = modal.querySelector(".js-formation-modal-content");
  var mainImg = modal.querySelector(".js-formation-modal-mainimg");
  var thumbs = modal.querySelectorAll(".js-formation-modal-thumb");
  var prevBtn = modal.querySelector(".js-formation-modal-prev");
  var nextBtn = modal.querySelector(".js-formation-modal-next");
  var lastFocused = null;
  var currentPhoto = 0;

  var photos = [];
  thumbs.forEach(function (thumb) {
    photos.push({ src: thumb.dataset.src, alt: thumb.dataset.alt });
  });

  function setPhoto(index) {
    if (!mainImg || !photos.length) {
      return;
    }
    currentPhoto = (index + photos.length) % photos.length;
    var photo = photos[currentPhoto];
    mainImg.src = photo.src;
    mainImg.alt = photo.alt;
    thumbs.forEach(function (thumb, i) {
      thumb.classList.toggle("is-active", i === currentPhoto);
    });
  }

  function open(card) {
    var slug = card.dataset.formation;
    var tpl = document.querySelector('.js-formation-template[data-formation="' + slug + '"]');
    if (!tpl) {
      return;
    }
    content.innerHTML = "";
    content.appendChild(tpl.content.cloneNode(true));

    var startIndex = photos.findIndex(function (photo) {
      return photo.src === card.dataset.image;
    });
    setPhoto(startIndex >= 0 ? startIndex : 0);

    lastFocused = document.activeElement;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
  }

  function close() {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    if (lastFocused && typeof lastFocused.focus === "function") {
      lastFocused.focus();
    }
  }

  cards.forEach(function (card) {
    card.addEventListener("click", function (event) {
      if (card.tagName === "A") {
        event.preventDefault();
      }
      open(card);
    });
  });

  thumbs.forEach(function (thumb, index) {
    thumb.addEventListener("click", function () {
      setPhoto(index);
    });
  });

  if (prevBtn) {
    prevBtn.addEventListener("click", function () {
      setPhoto(currentPhoto - 1);
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", function () {
      setPhoto(currentPhoto + 1);
    });
  }

  modal.addEventListener("click", function (event) {
    if (event.target.closest(".js-formation-modal-close") || event.target.closest(".js-formation-modal-cta")) {
      close();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal.classList.contains("is-open")) {
      close();
    }
  });
})();

/**
 * Cartes Formations en fondu + translation au scroll. S'appuie sur
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

  var formationCards = document.querySelectorAll(".js-formations-card");
  if (formationCards.length && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        gsap.from(formationCards, {
          opacity: 0,
          y: 24,
          duration: 0.5,
          stagger: 0.1,
          ease: "power2.out",
          scrollTrigger: { trigger: ".js-formations", start: "top 75%" },
        });
      },
    });
  }
})();
