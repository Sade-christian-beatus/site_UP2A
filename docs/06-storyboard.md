# 06 — Storyboard de la home (scène par scène)

> Home "cinématique" façon zero.university, adaptée à la connexion locale
> (effets lourds désactivés/simplifiés sur mobile via
> `ScrollTrigger.matchMedia()`, voir docs/02 et docs/CLAUDE.md §7). Chaque
> scène décrit : le contenu (renvoi à docs/05), la mise en scène, et les
> classes `js-*` à poser dans Elementor pour que `up2a-core` s'y accroche.
>
> **Statut (2026-09-22)** : l'en-tête (hors scènes, persistant sur toutes
> les pages) est construit et testé dans `up2a-core` (voir
> `wordpress/plugins/up2a-core/inc/header.php`). La home elle-même est
> construite comme un **template de page fourni par `up2a-core`**
> (sélectionnable dans wp-admin), plutôt qu'assemblée manuellement dans
> Elementor — plus rapide et plus fiable pour des scènes animées
> complexes, voir `wordpress/README.md`. Les scènes 4 (chiffres clés) et
> 6 (actualités) sont **reportées** : elles dépendent de données réelles
> (chiffres validés, articles publiés) qui n'existent pas encore —
> inutile de publier des nombres inventés ou une actualité vide.

## Scène 0 — Chargement / intro (optionnel, léger)

- Court écran de garde avec le logo/wordmark UP-2A qui apparaît en
  `SplitText` (lettres), fondu vers la scène 1. **Désactivé sur mobile**
  (juste un fondu simple) pour ne pas retarder le rendu du contenu réel.
- Classes : `js-intro`, `js-intro-logo`.

## Scène 1 — Hero

- Visuel : rendu du bâtiment (asset existant) en arrière-plan, léger
  parallaxe 2.5D au scroll (translation + scale doux, jamais de vrai 3D
  navigable — voir non-objectifs).
- Contenu : nom complet de l'université, slogan *« Former aujourd'hui les
  élites de demain »*, CTA principal "Faire ma préinscription".
- Titre en `SplitText` (animation lettre/mot à l'entrée), CTA qui apparaît
  après le titre (`stagger`).
- Mobile : parallaxe désactivé (juste l'image statique + fondu), texte et
  CTA identiques.
- Classes : `js-hero`, `js-hero-bg` (couche parallaxe), `js-hero-title`
  (SplitText), `js-hero-cta`.

## Scène 2 — Valeurs / mission

- Contenu : 3 à 4 cartes courtes (excellence, savoir, intégrité, ouverture)
  — texte dans docs/05 §"Valeurs".
- Mise en scène : pin léger de la section pendant que les cartes
  apparaissent en `stagger` au scroll (desktop) ; sur mobile, simple
  apparition séquentielle sans pin (le pin coûte cher en perf/UX tactile).
- Classes : `js-values`, `js-values-card` (une par carte).

## Scène 3 — Formations en un coup d'œil

- Contenu : les 4 licences (nom court + faculté + lien "en savoir plus"),
  tirées de docs/05 / du CPT Formation (phase 4 de la roadmap).
- Mise en scène : défilement horizontal léger ou grille responsive selon
  arbitrage phase Home ; animation d'entrée en fondu + translation Y,
  pas de pin.
- Classes : `js-formations`, `js-formations-card`.

## Scène 4 — Chiffres clés / repères institutionnels

- Contenu : quelques repères factuels validés par le client (ex. année de
  création, nombre de facultés, taux d'encadrement...) — **à ne pas
  inventer**, cf. docs/05 (placeholders `[À CONFIRMER]` tant que le client
  n'a pas fourni les chiffres réels).
- Mise en scène : compteurs animés (count-up) au scroll, une seule fois
  (pas de re-déclenchement en re-scroll).
- Classes : `js-stats`, `js-stats-counter` (avec attribut `data-target`
  pour la valeur finale, lu par le JS — jamais la valeur codée en dur dans
  le script).

## Scène 5 — Admissions / comment candidater

- Contenu : les grandes étapes du parcours de préinscription (résumé, pas
  le formulaire lui-même), CTA vers la page/le formulaire de
  préinscription.
- Mise en scène : timeline horizontale (desktop) qui devient verticale sur
  mobile, apparition progressive des étapes.
- Classes : `js-admissions`, `js-admissions-step`.

## Scène 6 — Actualités / vie universitaire

- Contenu : 2-3 dernières actualités (CPT Actualité, hors périmètre de
  cette phase si le CPT n'existe pas encore — peut être une section
  statique en V1).
- Mise en scène : cartes en fondu + léger défilement, rien de coûteux.
- Classes : `js-news`, `js-news-card`.

## Scène 7 — CTA final + contact

- Contenu : rappel du CTA préinscription + informations de contact
  (adresse, téléphone, e-mail — docs/05).
- Mise en scène : simple, pas d'effet lourd (fin de parcours, on veut que
  l'action soit facile et rapide, surtout sur mobile).
- Classes : `js-cta-final`.

## Footer

- Liens légaux, mentions (autorisation MESRI n°2026-001647), réseaux
  sociaux, plan du site. Pas d'animation particulière.

## Règles transverses (toutes scènes)

- Toute scène avec pin/effet 3D/parallaxe lourd est déclarée dans un bloc
  `ScrollTrigger.matchMedia()` avec au minimum les branches `isDesktop` /
  `isMobile`, la branche mobile étant toujours la version la plus légère.
- `prefers-reduced-motion: reduce` désactive tous les effets d'entrée
  (le contenu apparaît directement à son état final, sans transition).
- Aucun texte de contenu n'est écrit dans le JS : le JS ne fait que cibler
  les classes `js-*` posées sur les éléments Elementor et lire les
  `data-*` nécessaires (ex. `data-target` pour les compteurs).
