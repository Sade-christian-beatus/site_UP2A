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
