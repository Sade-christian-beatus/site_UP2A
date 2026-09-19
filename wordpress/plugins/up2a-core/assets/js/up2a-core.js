/**
 * up2a-core — bootstrap de la couche d'animation.
 *
 * Ne contient AUCUNE scène spécifique à la home (ça viendra en phase 3,
 * voir docs/03-roadmap.md et docs/06-storyboard.md). Ce fichier :
 *   1. respecte prefers-reduced-motion ;
 *   2. initialise Lenis (scroll fluide) sauf si reduced-motion ;
 *   3. enregistre les plugins GSAP ;
 *   4. expose window.UP2A pour que les scripts de scène (phase 3) ciblent
 *      les classes `js-*` sans redéclarer cette configuration.
 */
(function () {
  "use strict";

  var prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  if (typeof gsap !== "undefined") {
    if (typeof ScrollTrigger !== "undefined") {
      gsap.registerPlugin(ScrollTrigger);
    }
    if (typeof SplitText !== "undefined") {
      gsap.registerPlugin(SplitText);
    }
  }

  var lenis = null;
  if (!prefersReducedMotion && typeof Lenis !== "undefined") {
    lenis = new Lenis();

    lenis.on("scroll", function () {
      if (typeof ScrollTrigger !== "undefined") {
        ScrollTrigger.update();
      }
    });

    function raf(time) {
      lenis.raf(time);
      requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

    if (typeof gsap !== "undefined") {
      gsap.ticker.add(function (time) {
        lenis.raf(time * 1000);
      });
      gsap.ticker.lagSmoothing(0);
    }
  }

  /**
   * Enregistre un effet scroll avec des variantes desktop/mobile via
   * ScrollTrigger.matchMedia(), et un fallback statique si
   * prefers-reduced-motion (voir docs/02-design-system.md "Motion").
   *
   * @param {{desktop?: Function, mobile?: Function, reduced?: Function}} variants
   */
  function registerScrollEffect(variants) {
    if (prefersReducedMotion) {
      if (typeof variants.reduced === "function") {
        variants.reduced();
      }
      return;
    }

    if (typeof ScrollTrigger === "undefined") {
      return;
    }

    ScrollTrigger.matchMedia({
      "(min-width: 768px)": variants.desktop || function () {},
      "(max-width: 767px)": variants.mobile || variants.desktop || function () {},
    });
  }

  window.UP2A = {
    prefersReducedMotion: prefersReducedMotion,
    lenis: lenis,
    registerScrollEffect: registerScrollEffect,
  };
})();
