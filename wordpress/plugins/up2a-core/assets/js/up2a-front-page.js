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
 * Compte à rebours de la rentrée académique (wordpress/plugins/up2a-core/
 * inc/front-page.php). Indépendant de GSAP : doit continuer à s'afficher
 * même si le CDN GSAP échoue.
 */
(function () {
  "use strict";

  var el = document.querySelector(".js-hero-countdown");
  if (!el) {
    return;
  }

  var target = new Date(el.dataset.target).getTime();
  var daysEl = el.querySelector(".js-countdown-days");
  var hoursEl = el.querySelector(".js-countdown-hours");
  var minutesEl = el.querySelector(".js-countdown-minutes");
  var secondsEl = el.querySelector(".js-countdown-seconds");
  var timer = null;

  function pad(n) {
    return String(n).padStart(2, "0");
  }

  function tick() {
    var diff = target - Date.now();
    if (isNaN(target) || diff <= 0) {
      daysEl.textContent = "00";
      hoursEl.textContent = "00";
      minutesEl.textContent = "00";
      secondsEl.textContent = "00";
      if (timer) {
        clearInterval(timer);
      }
      return;
    }
    var totalSeconds = Math.floor(diff / 1000);
    daysEl.textContent = pad(Math.floor(totalSeconds / 86400));
    hoursEl.textContent = pad(Math.floor((totalSeconds % 86400) / 3600));
    minutesEl.textContent = pad(Math.floor((totalSeconds % 3600) / 60));
    secondsEl.textContent = pad(totalSeconds % 60);
  }

  tick();
  timer = setInterval(tick, 1000);
})();

/**
 * Modale de détail d'une formation (wordpress/plugins/up2a-core/inc/
 * front-page.php). Chaque carte clone le contenu de son <template>
 * associé (même `data-formation`) dans la modale — indépendant de GSAP.
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
 * Galerie photo — cadre agrandi (wordpress/plugins/up2a-core/inc/
 * front-page.php). La grande case de la grille fait défiler toutes les
 * photos de la galerie toutes les 10s (fondu enchaîné), indépendamment
 * des vignettes fixes. Met aussi à jour les données de la carte
 * (data-full/alt/legende) pour que la lightbox ouvre toujours la photo
 * actuellement affichée. Indépendant de GSAP.
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
 * Décor de section — suivi léger du curseur (wordpress/plugins/up2a-core/
 * inc/front-page.php, up2a_core_decor()). Indépendant de GSAP : purement
 * cosmétique, desktop uniquement (voir CLAUDE.md §7), respecte
 * prefers-reduced-motion. Déplace le conteneur `.js-decor` en entier (pas
 * les formes individuelles, animées séparément au scroll par GSAP plus
 * bas) pour ne jamais entrer en conflit avec le parallaxe de scroll.
 */
(function () {
  "use strict";

  var decorSections = document.querySelectorAll(".js-decor");
  if (!decorSections.length) {
    return;
  }

  var prefersReducedMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var isDesktop = window.matchMedia && window.matchMedia("(min-width: 960px)").matches;

  if (prefersReducedMotion || !isDesktop) {
    return;
  }

  decorSections.forEach(function (decor) {
    var parent = decor.parentElement;
    if (!parent) {
      return;
    }

    var raf = null;

    parent.addEventListener("mousemove", function (event) {
      if (raf) {
        return;
      }
      raf = requestAnimationFrame(function () {
        var rect = parent.getBoundingClientRect();
        var x = (event.clientX - rect.left) / rect.width - 0.5;
        var y = (event.clientY - rect.top) / rect.height - 0.5;
        decor.style.transform = "translate(" + (x * 24).toFixed(1) + "px, " + (y * 24).toFixed(1) + "px)";
        raf = null;
      });
    });

    parent.addEventListener("mouseleave", function () {
      decor.style.transform = "";
    });
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

  // Scène 1 — Hero : arrière-plan en parallaxe (défile plus lentement que
  // le contenu). Desktop uniquement — voir CLAUDE.md §7, la couche
  // `.up2a-hero__slides` est surdimensionnée en CSS pour ce déplacement
  // ne révèle jamais de bord vide.
  var heroBg = document.querySelector(".js-hero-bg");
  var heroSection = document.querySelector(".js-hero");
  if (heroBg && heroSection && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        gsap.to(heroBg, {
          y: 90,
          ease: "none",
          scrollTrigger: {
            trigger: heroSection,
            start: "top top",
            end: "bottom top",
            scrub: true,
          },
        });
      },
      mobile: function () {},
    });
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

  // Scène 3 ter — Documents : bloc brochure en fondu + translation.
  var documents = document.querySelector(".js-documents");
  if (documents && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        gsap.from(documents.querySelectorAll(".up2a-documents__cover, .up2a-documents__text > *"), {
          opacity: 0,
          y: 20,
          duration: 0.5,
          stagger: 0.1,
          ease: "power2.out",
          scrollTrigger: { trigger: documents, start: "top 75%" },
        });
      },
    });
  }

  // Décor de fond par section : léger parallaxe au scroll, purement
  // décoratif — desktop uniquement (voir CLAUDE.md §7 : effets lourds
  // désactivés sur mobile), aucun effet statique de repli nécessaire
  // puisque le décor reste visible sans mouvement.
  var decorSections = document.querySelectorAll(".js-decor");
  if (decorSections.length && registerScrollEffect) {
    registerScrollEffect({
      desktop: function () {
        decorSections.forEach(function (decor) {
          var shapes = decor.querySelectorAll(".js-decor-shape");
          gsap.to(shapes[0] || [], {
            y: -60,
            ease: "none",
            scrollTrigger: {
              trigger: decor.closest("section"),
              start: "top bottom",
              end: "bottom top",
              scrub: true,
            },
          });
          gsap.to(shapes[1] || [], {
            y: 50,
            ease: "none",
            scrollTrigger: {
              trigger: decor.closest("section"),
              start: "top bottom",
              end: "bottom top",
              scrub: true,
            },
          });
        });
      },
      mobile: function () {},
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
