# 06 — Storyboard de la home (scène par scène)

> Home "cinématique" façon zero.university, adaptée à la connexion locale
> (effets lourds désactivés/simplifiés sur mobile via
> `ScrollTrigger.matchMedia()`, voir docs/02 et docs/CLAUDE.md §7). Chaque
> scène décrit : le contenu (renvoi à docs/05), la mise en scène, et les
> classes `js-*` à poser dans Elementor pour que `up2a-core` s'y accroche.
>
> **Statut (2026-09-22)** : l'en-tête (hors scènes, persistant sur toutes
> les pages) est construit et testé dans `up2a-core` (voir
> `wordpress/plugins/up2a-core/inc/header.php`) — logo agrandi pour une
> meilleure visibilité, palette officielle (primaire `#082E6C`, secondaire
> `#FE931A`, extra `#E83527`/`#4B81A0`). La home elle-même est construite
> comme un **template de page fourni par `up2a-core`** (sélectionnable
> dans wp-admin), plutôt qu'assemblée manuellement dans Elementor — plus
> rapide et plus fiable pour des scènes animées complexes, voir
> `wordpress/README.md`.
>
> **Ordre actuel de la page** (structure demandée par le client,
> 2026-09-22) : Hero (slider + compte à rebours de la rentrée), Pourquoi
> choisir l'UP-2A, Nos valeurs, Nos formations, Galerie, Comment
> candidater, Contact (avec carte de géolocalisation), Footer. Les
> sections "Documents" (brochure, voir plus bas) et "CTA final" existent
> toujours dans le code (`up2a_core_render_documents()` /
> `up2a_core_render_cta_final()`) mais **ne sont plus appelées** dans
> `templates/front-page-onepage.php` — à réactiver d'une ligne si le
> client les souhaite de nouveau dans le parcours.
>
> Le Hero utilise un **slider de vraies photos de campus** (fournies par
> le client) avec rotation automatique et points de navigation, complété
> d'un **compte à rebours vers la rentrée académique** (5 octobre par
> défaut, voir `up2a_core_rentree_date()`). Les cartes Formations sont
> désormais présentées façon "fiche" (photo de fond, badge de faculté,
> lien "Voir la formation" au survol) et **ouvrent une modale** avec la
> présentation complète au clic. La section "Pourquoi choisir l'UP-2A"
> utilise un collage photo (image principale + photo secondaire
> superposée) et une checklist à icônes rondes. "Comment candidater" met
> en avant la première étape dans une grande carte avec CTA, les 3
> suivantes dans des cartes plus petites. La section Contact intègre
> désormais une **carte Google Maps** sous les coordonnées (géolocalisation
> par adresse, aucune coordonnée GPS fixe n'ayant été fournie).

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

## Scène 3 bis — Galerie

- Contenu : grille de photos du campus et de la vie étudiante (les mêmes
  photos réelles que le Hero/Pourquoi, réutilisées ici), cliquables pour
  ouvrir en grand dans une lightbox (fermeture, navigation précédent/suivant
  au clic ou au clavier). Filtrable (`up2a_core_gallery_images`) pour
  accueillir de nouvelles photos sans toucher au code, au fil de leur
  arrivée (voir CLAUDE.md §3).
- Mise en scène : grille responsive (une vignette plus grande en tête),
  léger zoom au survol, apparition en fondu + translation Y au scroll —
  pas de pin, pas d'effet 3D.
- Classes : `js-galerie`, `js-galerie-item`, `js-galerie-lightbox`.

## Scène 3 ter — Documents (brochure)

- Contenu : mise en avant de la brochure institutionnelle (présentation
  des formations, admissions, coordonnées) avec un aperçu visuel stylisé
  de couverture (pas un faux scan) et un bouton de téléchargement — état
  "bientôt disponible" tant qu'aucun PDF réel n'est fourni (voir docs/05).
- Mise en scène : simple fondu + translation Y au scroll, pas de pin.
- Classes : `js-documents`.

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

## Décor de fond par section

- Chaque scène (hors Hero, qui n'a plus de décor propre depuis le retrait
  de la vague, voir plus bas) porte un léger décor purement visuel — un
  "blob" flouté et un anneau fin, aux couleurs de la palette de marque,
  positionnés différemment par section pour éviter la monotonie d'un fond
  plat. Toujours `aria-hidden`, jamais porteur de contenu.
- Mise en scène : léger parallaxe au scroll (`scrub`) sur les formes
  individuelles, **et** un suivi léger du curseur sur le conteneur entier
  (desktop uniquement dans les deux cas — désactivé sur mobile et sous
  `prefers-reduced-motion: reduce`, voir CLAUDE.md §7). Les deux effets
  ciblent des éléments différents (formes vs conteneur) pour ne jamais
  entrer en conflit.
- Classes : `js-decor` (conteneur par section, suivi du curseur),
  `js-decor-shape` (chaque forme, parallaxe au scroll).

## Hero — nettoyage (2026-09-22)

La vague décorative en bas à gauche et la liste de badges ("Préinscription
100% en ligne", etc.) ont été retirées du Hero à la demande du client
(redondantes avec le reste de la page, et chevauchaient visuellement le
nouveau compte à rebours). Le Hero se termine maintenant simplement sur
les CTA.

## Harmonisation des couleurs

- **Nos valeurs** : chaque icône a sa propre couleur de la palette
  (Excellence = accent, Savoir = teal, Intégrité = primaire, Ouverture =
  rouge) plutôt qu'une couleur unique répétée.
- **Nos formations** : le badge de faculté est coloré par faculté (SJPA =
  teal, SEG = rouge) au lieu d'accent partout — aide aussi à distinguer
  visuellement les deux facultés d'un coup d'œil.
- **Comment candidater / Nous contacter** (2026-09-22) : les fonds teintés,
  icônes et bouton CTA passent de l'accent (orange) à la couleur
  principale (bleu marine) — demande explicite du client, ces deux
  sections gardent une identité plus institutionnelle/"action" que
  promotionnelle.

## Modale Formation — refonte "fiche" (2026-09-22)

La modale de détail d'une formation adopte désormais une mise en page à
deux colonnes façon fiche immobilière (référence fournie par le client) :
galerie photo à gauche (image principale + bande de vignettes, flèches
précédent/suivant — réutilise les 7 photos de la section Galerie,
`up2a_core_gallery_images()`, la photo de la carte cliquée étant
sélectionnée en premier) et détails à droite (badge de faculté, titre,
ligne de mise en avant "Licence · 3 ans", faculté complète, description,
débouchés, CTA préinscription + lien "Retour aux formations"). Sur mobile,
les deux colonnes s'empilent.

## Corrections et ajouts (2026-09-22)

- **Formations** : les images de fond des cartes sont repassées en
  chargement immédiat (non lazy) — le lazy loading causait un affichage
  gris/manquant sur certaines cartes, signalé par le client.
- **Footer** : ajout du logo (sur fond blanc pour rester lisible), ligne
  d'accent en haut, séparateurs verticaux entre colonnes, meilleure
  hiérarchie typographique.
- **Compte à rebours** : ajout d'une icône horloge, séparateurs verticaux
  entre chaque statistique, ombre plus prononcée.
- **Hero** : arrière-plan (slider) en parallaxe au scroll (défile plus
  lentement que le contenu), desktop uniquement. La couche
  `.up2a-hero__slides` est surdimensionnée en CSS pour que ce déplacement
  ne révèle jamais de bord vide.
- **Galerie** : la grande case de la grille ("cadre agrandi") fait
  maintenant défiler toutes les photos de la galerie en fondu enchaîné
  toutes les 10 secondes, avec une bordure de couleur primaire pour la
  distinguer comme case "dynamique" — indépendant de GSAP, s'arrête sous
  `prefers-reduced-motion: reduce`.

## Formations — garde-fou contre une section vide (2026-09-22)

Diagnostiqué sur le site réel : toutes les sections de la home affichaient
la dernière version (HTML et CSS à jour, vérifié dans le code source),
sauf "Nos formations" qui restait totalement vide et ne réagissait pas au
clic. Comme le reste de la page (Galerie, Comment candidater, Contact,
Footer — tous rendus par la même requête, après Formations) s'affichait
correctement, ce n'était ni un cache ni une erreur PHP fatale, mais très
probablement une autre extension du site qui intercepte le filtre
`up2a_core_formations` et renvoie une liste vide. `up2a_core_formations()`
ignore désormais un résultat de filtre vide/invalide et retombe sur les 4
licences par défaut (voir `wordpress/README.md`, Dépannage §7).

## Nouveau logo footer + 8e photo galerie (2026-09-22)

Le client a fourni un nouveau lockup logo (fond marine, texte/emblème
blancs, `assets/img/logo-up2a-footer.webp`/`.png`) pensé pour un fond
sombre — remplace l'ancien logo + chip blanc dans le footer, qui utilisait
un fond blanc pour rester lisible (`up2a_core_render_footer()`). Fond
détouré (transparence) pour se fondre directement dans le footer marine,
sans bloc blanc autour.

Ajout d'une 8e photo à `up2a_core_gallery_images()` (groupe d'étudiants
devant le campus, fournie par le client), avec la même garde-fou anti-liste-vide
que `up2a_core_formations()` (voir plus haut) appliqué également ici.

## Footer enrichi (2026-09-23)

- **Liens rapides** : ajout de "Pourquoi l'UP-2A" et "Nos valeurs" (ces deux
  sections ont maintenant un `id` — `up2a-pourquoi` / `up2a-valeurs` — pour
  être ciblables en ancre, ce qui n'était pas le cas avant).
- **Nos formations** : les 4 liens ouvrent désormais directement la modale
  de détail de la formation (comme les cartes de la section Formations),
  au lieu de simplement faire défiler vers la section — réutilise le même
  mécanisme JS (`js-formations-card`), aucune duplication de logique.
  Dégradation propre : reste un vrai lien `#up2a-formations` si JS
  indisponible.
- **Style** : titres de colonne avec soulignement accent, puces animées sur
  les liens (léger décalage + couleur accent au survol).

## Règles transverses (toutes scènes)

- Toute scène avec pin/effet 3D/parallaxe lourd est déclarée dans un bloc
  `ScrollTrigger.matchMedia()` avec au minimum les branches `isDesktop` /
  `isMobile`, la branche mobile étant toujours la version la plus légère.
- `prefers-reduced-motion: reduce` désactive tous les effets d'entrée
  (le contenu apparaît directement à son état final, sans transition).
- Aucun texte de contenu n'est écrit dans le JS : le JS ne fait que cibler
  les classes `js-*` posées sur les éléments Elementor et lire les
  `data-*` nécessaires (ex. `data-target` pour les compteurs).
