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

  // Scène 3 — Formations et Scène 3 bis — Galerie : leurs animations de
  // scroll-reveal vivent maintenant dans les plugins up2a-formations et
  // up2a-galerie respectivement (voir docs/06-storyboard.md "Scission
  // Formations/Galerie") — chacun dépend du handle `up2a-core` et de
  // `window.UP2A`, comme ce fichier.

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
