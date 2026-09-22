/**
 * Slider du Hero (wordpress/plugins/up2a-core/inc/front-page.php).
 * Indépendant de GSAP (même logique que up2a-header.js) : la rotation des
 * diapositives ne doit pas dépendre d'un CDN externe qui peut échouer.
 */
(function () {
  "use strict";

  var slides = document.querySelectorAll(".up2a-hero__slide");
  var dots = document.querySelectorAll(".js-hero-dots button");

  if (slides.length < 2) {
    return;
  }

  var current = 0;
  var prefersReducedMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function goTo(index) {
    slides[current].classList.remove("is-active");
    dots[current] && dots[current].classList.remove("is-active");
    current = index % slides.length;
    slides[current].classList.add("is-active");
    dots[current] && dots[current].classList.add("is-active");
  }

  dots.forEach(function (dot) {
    dot.addEventListener("click", function () {
      goTo(parseInt(dot.dataset.slide, 10) || 0);
    });
  });

  if (!prefersReducedMotion) {
    setInterval(function () {
      goTo(current + 1);
    }, 6500);
  }
})();

/**
 * Cartes flip des formations (wordpress/plugins/up2a-core/inc/front-page.php).
 * Le survol (:hover) suffit sur desktop (CSS pur) ; ce script ajoute le
 * support tactile (tap pour retourner) — indépendant de GSAP.
 */
(function () {
  "use strict";

  var cards = document.querySelectorAll(".up2a-formations__card");

  cards.forEach(function (card) {
    card.addEventListener("click", function (event) {
      if (event.target.closest("a")) {
        return; // laisse le lien de la face arrière fonctionner normalement
      }
      card.classList.toggle("is-flipped");
    });
  });
})();

/**
 * Galerie photo — grille + lightbox (wordpress/plugins/up2a-core/inc/front-page.php).
 * Indépendant de GSAP : ouverture/fermeture/navigation doivent fonctionner
 * même si le CDN GSAP est indisponible.
 */
(function () {
  "use strict";

  var items = document.querySelectorAll(".js-galerie-item");
  var lightbox = document.querySelector(".js-galerie-lightbox");

  if (!items.length || !lightbox) {
    return;
  }

  var img = lightbox.querySelector(".js-galerie-lightbox-img");
  var closeBtn = lightbox.querySelector(".js-galerie-close");
  var prevBtn = lightbox.querySelector(".js-galerie-prev");
  var nextBtn = lightbox.querySelector(".js-galerie-next");
  var current = 0;

  function open(index) {
    current = (index + items.length) % items.length;
    var item = items[current];
    img.src = item.dataset.full;
    img.alt = item.dataset.alt || "";
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
 * Animations du template "Accueil UP-2A (onepage)".
 * S'appuie sur window.UP2A (voir up2a-core.js) : ne fait rien si GSAP n'a
 * pas pu se charger (ex. CDN indisponible) — le contenu reste visible et
 * statique, ce n'est qu'une dégradation, jamais un blocage.
 */
(function () {
  "use strict";

  if (typeof window === "undefined" || !window.UP2A || typeof gsap === "undefined") {
    return;
  }

  var registerScrollEffect = window.UP2A.registerScrollEffect;

  // Scène 1 — Hero : titre en SplitText, puis sous-titre et CTA en stagger.
  var heroTitle = document.querySelector(".js-hero-title");
  if (heroTitle) {
    var run = function () {
      var split =
        typeof SplitText !== "undefined"
          ? new SplitText(heroTitle, { type: "words" })
          : null;
      var targets = split ? split.words : [heroTitle];

      gsap.set(targets, { opacity: 0, y: 24 });
      gsap.set(".js-hero-cta", { opacity: 0, y: 12 });

      var tl = gsap.timeline({ delay: 0.2 });
      tl.to(targets, {
        opacity: 1,
        y: 0,
        duration: 0.6,
        stagger: 0.06,
        ease: "power2.out",
      }).to(
        ".js-hero-cta",
        { opacity: 1, y: 0, duration: 0.5, ease: "power2.out" },
        "-=0.2"
      );
    };
    run();
  }

  // Scène 1 bis — Pourquoi choisir l'UP-2A : texte + média en fondu.
  var pourquoi = document.querySelector(".js-pourquoi");
  if (pourquoi && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        gsap.from(pourquoi.querySelectorAll(".up2a-pourquoi__text > *, .up2a-pourquoi__media"), {
          opacity: 0,
          y: 20,
          duration: 0.5,
          stagger: 0.1,
          ease: "power2.out",
          scrollTrigger: { trigger: pourquoi, start: "top 75%" },
        });
      },
    });
  }

  // Scène 2 — Valeurs : cartes en stagger au scroll.
  var valueCards = document.querySelectorAll(".js-values-card");
  if (valueCards.length && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        gsap.from(valueCards, {
          opacity: 0,
          y: 24,
          duration: 0.5,
          stagger: 0.1,
          ease: "power2.out",
          scrollTrigger: { trigger: ".js-values", start: "top 75%" },
        });
      },
      mobile: function () {
        gsap.from(valueCards, {
          opacity: 0,
          y: 12,
          duration: 0.4,
          stagger: 0.08,
          ease: "power2.out",
          scrollTrigger: { trigger: ".js-values", start: "top 85%" },
        });
      },
    });
  }

  // Scène 3 — Formations : même logique, cartes en fondu + translation.
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

  // Scène 3 bis — Galerie : vignettes en fondu + translation.
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

  // Scène 5 — Admissions : étapes en stagger.
  var steps = document.querySelectorAll(".js-admissions-step");
  if (steps.length && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        gsap.from(steps, {
          opacity: 0,
          y: 16,
          duration: 0.4,
          stagger: 0.12,
          ease: "power2.out",
          scrollTrigger: { trigger: ".js-admissions", start: "top 75%" },
        });
      },
    });
  }
})();
