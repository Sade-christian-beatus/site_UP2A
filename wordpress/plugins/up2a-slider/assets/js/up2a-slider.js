/**
 * Scène d'accueil "Hero" — extrait de wordpress/plugins/up2a-core/
 * assets/js/up2a-front-page.js (scission documentée dans
 * docs/06-storyboard.md, 2026-09-28).
 */

/**
 * Slider du Hero. Indépendant de GSAP (même logique que up2a-header.js) :
 * la rotation des diapositives ne doit pas dépendre d'un CDN externe qui
 * peut échouer.
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
 * Compte à rebours de la rentrée académique. Indépendant de GSAP : doit
 * continuer à s'afficher même si le vendor GSAP échoue.
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
 * Animations GSAP du Hero (titre en SplitText + parallaxe d'arrière-plan).
 * S'appuie sur window.UP2A (voir up2a-core.js) : ne fait rien si GSAP n'a
 * pas pu se charger — le contenu reste visible et statique, ce n'est
 * qu'une dégradation, jamais un blocage.
 */
(function () {
  "use strict";

  if (typeof window === "undefined" || !window.UP2A || typeof gsap === "undefined") {
    return;
  }

  var registerScrollEffect = window.UP2A.registerScrollEffect;

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

  // Arrière-plan en parallaxe (défile plus lentement que le contenu).
  // Desktop uniquement — voir CLAUDE.md §7, la couche `.up2a-hero__slides`
  // est surdimensionnée en CSS pour que ce déplacement ne révèle jamais
  // de bord vide.
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
})();
