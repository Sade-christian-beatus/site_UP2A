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

  // Scène 1 — Hero : ses animations (titre SplitText, parallaxe
  // d'arrière-plan) vivent maintenant dans le plugin up2a-slider (voir
  // docs/06-storyboard.md "Extraction En-tête/Pied de page/Slider/
  // Actualités") — même dépendance à `up2a-core`/`window.UP2A`.

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
