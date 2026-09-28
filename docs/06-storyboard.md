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

> **Statut (2026-09-28)** : implémentée, voir "Extraction En-tête/Pied de
> page/Slider/Actualités" plus bas. Pas de CPT — un écran de réglages
> (répéteur) dans le plugin dédié `up2a-actualites`, shortcode
> `[up2a_actualites]`. **Aucune actualité n'est préremplie** (contenu
> réel à préserver = aucun, aucune donnée à inventer) : la section reste
> absente de la home jusqu'à ce qu'une vraie actualité soit ajoutée
> depuis le tableau de bord.

- Contenu : les actualités ajoutées par l'admin (titre, date, extrait,
  image, lien "En savoir plus" optionnel), dans l'ordre choisi sur
  l'écran de réglages (pas un tri automatique par date).
- Mise en scène : grille de cartes (3 colonnes desktop, 2 puis 1 en
  responsive), légère élévation au survol pour les cartes cliquables.
- Classes : `js-decor` (décor de section, même mécanisme que les autres
  scènes) — pas d'animation GSAP dédiée pour l'instant (grille statique,
  rien de coûteux).

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

## Vrais CTA de préinscription (2026-09-23)

Tous les boutons "Faire ma préinscription" / "S'inscrire maintenant" de
la home (Hero, modale Formation, section Comment candidater — qui n'en
avait pas jusqu'ici) pointent désormais vers `up2a_core_preinscription_url()`
(par défaut `/preinscription/`, filtrable), au lieu de simplement défiler
vers `#up2a-admissions`. Le CTA de la modale Formation passe en plus le
slug de la licence cliquée en paramètre d'URL (`?formation=...`) pour
présélectionner la bonne formation dans le formulaire — voir
`wordpress/plugins/up2a-preinscription`. Les slugs de formation de
`up2a_core_formations()` ont été alignés sur ceux de
`supabase/migrations/0003_seed.sql` (préfixe `licence-`) pour que le
formulaire puisse résoudre la bonne ligne côté Supabase.

## Formations en 2×2, survol Valeurs, perf galerie (2026-09-23)

- **Nos formations** : grille fixée à 2 colonnes × 2 lignes (au lieu
  d'un `auto-fit` qui passait à 4 colonnes sur grand écran) — 1 colonne
  sous 480px.
- **Nos valeurs** : effet de survol (léger soulèvement + ombre + icône
  qui pivote/grossit) sur chaque carte.
- **Cause racine enfin identifiée pour "Formations/Galerie vides"** :
  ce n'était ni un bug de données ni un bug JS — Elementor mettait en
  cache son propre CSS "optimisé" à partir d'un instantané antérieur à
  l'ajout de ces sections, donc leur style ne s'appliquait jamais tant
  que ce cache n'était pas régénéré (**Elementor → Outils → Régénérer
  les fichiers CSS et les données**). Documenté dans
  `wordpress/README.md` Dépannage.
- **Perf galerie** : la case "cadre agrandi" ne charge plus les 8
  photos d'un coup au chargement de la page — seuls 2 calques `<img>`
  existent dans le DOM, le JS charge la photo suivante à l'avance (une
  seule à la fois) juste avant chaque fondu enchaîné de 10s. Les cartes
  Formations repassent en `loading="lazy"` (le vrai bug d'affichage
  n'ayant jamais été le lazy loading, contrairement à ce qu'on pensait
  lors d'un précédent diagnostic — voir ci-dessus).

## Pages détail formation (2026-09-25)

- Décision technique (phase 4, docs/03-roadmap.md) : ni CPT WordPress, ni
  montage Elementor — une règle de réécriture `/formations/{slug}/`
  (`up2a-core/inc/formation-detail.php`) + un template codé
  (`templates/formation-detail.php`) qui relit `up2a_core_formations()`,
  déjà la source unique du contenu des 4 licences (nom, faculté, intro,
  débouchés, image), synchronisée avec `supabase/migrations/0003_seed.sql`.
  Un CPT aurait dupliqué cette source pour seulement 4 pages fixes.
- Contenu affiché : uniquement ce qui est confirmé (voir
  docs/05-contenus.md) — nom, faculté, description, débouchés (dérivés de
  la même phrase confirmée). Le programme détaillé (matières, volumes
  horaires) n'existe pas encore : affiché honnêtement comme "sera publié
  prochainement" plutôt qu'inventé (voir CLAUDE.md §3).
- Les "conditions d'admission" reprises sur la page sont le même
  processus générique déjà affiché sur la home (section Admissions), pas
  un contenu spécifique par licence.
- Modale Formation (home) : bouton "Voir la fiche complète" ajouté à côté
  de "Faire ma préinscription", vers la page dédiée.
- Filet anti-404 : comme pour `/preinscription/` (voir README
  "Dépannage"), un changement de règle de réécriture nécessite un flush
  WordPress. Un filet de sécurité compare le numéro de version du plugin
  à une option stockée et relance `flush_rewrite_rules()` automatiquement
  si elle a changé, en plus du flush à l'activation.

## Correctif ScrollTrigger — Formations/Galerie invisibles au scroll normal (2026-09-25)

- **Symptôme rapporté** : sur desktop, la section Formations ne
  s'affichait qu'en arrivant directement sur `#up2a-formations`, jamais
  en scrollant normalement depuis le haut de la page ; la Galerie ne
  s'affichait pas du tout. Sur mobile, tout s'affichait normalement.
- **Cause** : `up2a-core.js` enregistre Lenis (scroll fluide) et relie
  son événement `scroll` à `ScrollTrigger.update()`, mais ne
  recalculait jamais les positions de déclenchement une fois la page
  entièrement chargée. Les triggers de "Formations" et "Galerie" (créés
  tôt, avec `scrollTrigger: { start: "top 75%" }`) pouvaient donc rester
  calés sur un seuil obsolète si la mise en page bougeait après leur
  création — un scroll normal ne le recroisait alors jamais, alors
  qu'un saut direct via ancre (`#up2a-formations`) pouvait, lui,
  satisfaire immédiatement la condition.
- **Correctif** : `ScrollTrigger.refresh()` appelé sur l'événement
  `window.load`, qui recalcule toutes les positions de déclenchement
  une fois la page (et ses ressources visibles) chargée — pratique
  standard recommandée par GSAP/Lenis pour ce type d'intégration. Voir
  `up2a-core.js`.
- **Si le problème persiste après mise à jour du plugin** : vérifier
  d'abord le cache CSS "optimisé" d'Elementor (cause historique de ce
  projet pour des sections qui n'apparaissent pas du tout, voir
  wordpress/README.md "Dépannage") — les deux causes peuvent se
  superposer.

## Scission Formations/Galerie en plugins dédiés (2026-09-26)

- **Pourquoi** : demande explicite de scinder les modules Formations et
  Galerie hors du plugin `up2a-core`, chacun dans son propre plugin
  (`up2a-formations`, `up2a-galerie`) — pour pouvoir les gérer/désactiver
  indépendamment sans toucher au reste de la couche d'animation/en-tête.
- **Ce qui a été déplacé** :
  - `up2a-formations` : les 4 licences (`up2a_formations_formations()`),
    les cartes + la modale de détail (`up2a_formations_render()`), la
    règle de réécriture `/formations/{slug}/` et son template
    (`templates/formation-detail.php`), le CSS/JS propres à ces deux
    écrans.
  - `up2a-galerie` : les photos (`up2a_galerie_images()`), la grille +
    lightbox (`up2a_galerie_render()`), le CSS/JS propres à cette
    section.
- **Ce qui reste dans `up2a-core`** (dépendance obligatoire des deux
  nouveaux plugins, en-tête `Requires Plugins`) : les tokens du design
  system, l'en-tête du site, Hero/Pourquoi/Valeurs/Documents/Admissions/
  Contact/pied de page, le système de décor de fond (`up2a_core_decor()`,
  y compris sa variante `--galerie` : c'est un système visuel central,
  pas un asset propre à la Galerie), les icônes de contenu
  (`up2a_core_content_icon()`), l'URL de préinscription
  (`up2a_core_preinscription_url()`) et les photos de campus partagées
  (`assets/img/` — les mêmes fichiers servent au Hero, aux Formations et
  à la Galerie, donc ils restent au même endroit plutôt que d'être
  dupliqués).
- **Dépendances croisées, résolues par degradation propre plutôt que par
  erreur** : le footer d'`up2a-core` (colonne "Nos formations") et la
  modale Formations (vignettes tirées de la Galerie) appellent la
  fonction du plugin voisin via `function_exists()` — si ce plugin
  n'est pas actif, la colonne/le bandeau concerné est simplement absent,
  jamais une erreur PHP. La home (`templates/front-page-onepage.php`,
  dans `up2a-core`) fait de même pour les deux sections entières.
- **Renommage des fonctions/filtres** : `up2a_core_formations` →
  `up2a_formations_formations`, `up2a_core_find_formation` →
  `up2a_formations_find`, `up2a_core_render_formations` →
  `up2a_formations_render`, `up2a_core_gallery_images` →
  `up2a_galerie_images`, `up2a_core_render_galerie` →
  `up2a_galerie_render` (même chose pour les filtres `apply_filters`
  correspondants). Sans impact connu à ce jour : aucun mu-plugin/thème
  enfant n'utilisait encore ces filtres.

## CPT Formation depuis le dashboard (2026-09-26) — remplacé le lendemain

> ⚠️ **Approche remplacée le 2026-09-27** par un écran de réglages (voir
> "Réglages Formations/Galerie" ci-dessous) : le client voulait gérer
> chaque module "à travers réglage de l'extension", pas une liste
> d'articles CPT à ouvrir un par un. Section conservée pour l'historique
> (pourquoi le CPT avait été choisi, ce qu'il apportait) mais **le code
> actuel n'a plus de CPT `up2a_formation`/`up2a_photo` ni de taxonomie
> `up2a_faculte`** — ne pas s'y référer pour comprendre le code présent.

- **Pourquoi** : demande explicite de rendre Formations et Galerie
  "modifiables à partir du tableau de bord" et affichées via un
  shortcode plutôt qu'un appel de code — la version "plugins séparés"
  ci-dessus scindait déjà le code, mais le contenu restait un tableau PHP
  statique, donc toujours non modifiable sans mise à jour du plugin.
- **Modèle retenu** : un CPT par plugin plutôt qu'un simple tableau
  filtrable (`apply_filters`) — un CPT donne un vrai écran d'édition
  wp-admin (titre, image, champs), ce qu'un filtre ne permet pas sans
  écrire soi-même une page de réglages.
  - `up2a-formations` → CPT `up2a_formation` (titre = nom, extrait =
    description courte, contenu = "Programme" sur la page de détail,
    image mise en avant, attribut d'ordre) + taxonomie `up2a_faculte`
    (SJPA/SEG, champ "Nom complet" par terme — une 3ᵉ faculté se crée
    depuis wp-admin sans toucher au code) + métabox "Détails" (icône en
    liste fermée pour éviter une faute de frappe silencieuse, débouchés
    un par ligne). Le rewrite `/formations/{slug}/` est désormais géré
    nativement par le CPT (`'rewrite' => ['slug' => 'formations']`),
    WordPress gère lui-même l'URL/404/permaliens — supprime la règle de
    réécriture manuelle + le filtre 404 codés à la main pour la phase 4.
  - `up2a-galerie` → CPT `up2a_photo` (titre = légende, image mise en
    avant, attribut d'ordre — la première entrée devient la vignette "en
    avant"). CPT non public (`'public' => false`) : pas de page de
    détail, ces photos ne s'affichent que dans la grille/lightbox. Le
    texte alternatif vient du champ natif de la médiathèque
    (`_wp_attachment_image_alt`), pas d'un champ dupliqué.
- **Amorçage automatique, avec les photos d'origine** : à la première
  exécution après mise à jour (vérifié sur `init`, même filet de sécurité
  que le flush des règles de réécriture — tourne même sans passer par une
  désactivation/réactivation explicite), chaque plugin recrée ses entrées
  par défaut (4 licences, 8 photos) **et** copie dans la médiathèque
  WordPress la même photo que l'ancienne version "tableau statique"
  utilisait pour cette entrée (`wp_upload_bits()` + `wp_insert_attachment()`
  depuis un fichier déjà présent dans `up2a-core/assets/img/`), pour que
  la mise à jour ne fasse RIEN disparaître d'un site déjà en ligne avec du
  vrai contenu. Une formation/photo ajoutée ensuite depuis wp-admin (pas
  par cet amorçage) doit avoir sa photo définie manuellement une fois ;
  tant que ce n'est pas fait, la carte s'affiche sans image (fond dégradé
  pour une formation, entrée simplement ignorée pour une photo de
  galerie) plutôt qu'avec une image cassée.
- **Shortcodes** `[up2a_formations]` / `[up2a_galerie]` : la home onepage
  d'`up2a-core` les exécute elle-même par défaut
  (`shortcode_exists()` + `do_shortcode()`, remplace l'appel direct de
  fonction de la phase 4 bis), mais rien n'empêche de coller le shortcode
  dans un widget Elementor "Shortcode" sur une autre page pour déplacer
  la section sans toucher au code.
- **Correctif de cohérence appliqué du même coup** : `up2a-preinscription`
  dupliquait sa propre liste statique des 4 licences pour la validation
  serveur du champ `formation` (voir inc/data.php). Une fois les
  formations éditables depuis wp-admin, garder cette liste figée aurait
  créé un vrai bug : une formation ajoutée depuis le dashboard aurait été
  proposée sur la home mais **rejetée** à la soumission du formulaire de
  préinscription. `up2a_preinscription_formations()` lit désormais
  `up2a_formations_formations()` en priorité (repli sur son ancienne
  liste statique si `up2a-formations` n'est pas actif).
- **Changement de contrat** : `up2a_formations_formations()` et
  `up2a_galerie_images()` gardent la même forme de retour (même clés de
  tableau) mais ne passent plus par `apply_filters()` — un mu-plugin qui
  aurait modifié leur contenu via ce filtre (aucun sur ce projet à ce
  jour) devrait passer par le tableau de bord à la place.

## Réglages Formations/Galerie — remplace le CPT (2026-09-27)

- **Pourquoi ce second changement, un jour après le premier** : retour
  client explicite sur la version CPT ci-dessus — "je veux des plugins
  dont je pourrai modifier chaque module à travers réglage de l'extension
  depuis le tableau de bord wordpress". Un CPT donne un menu avec une
  **liste de posts** à ouvrir un par un (comme Articles) ; ce qui était
  demandé est un **écran de réglages unique par module** (comme la page
  de réglages d'à peu près n'importe quel plugin WordPress), où tout le
  contenu se voit et se modifie en un seul formulaire.
- **Ce qui a changé** : CPT `up2a_formation`/`up2a_photo` + taxonomie
  `up2a_faculte` → une simple option WordPress par plugin
  (`up2a_formations_option`, `up2a_galerie_option`, chacune un tableau de
  lignes) éditée via un formulaire répéteur sur une page
  `add_menu_page()` dédiée :
  - **Formations** (menu "Formations") : une ligne par formation avec
    nom, slug, faculté (deux champs texte — code court + nom complet,
    plus de taxonomie séparée : simplicité de l'écran unique plutôt que
    la réutilisation entre formations, qui n'est de toute façon qu'un
    gain marginal pour 2 facultés), icône (liste fermée), description
    courte, débouchés (un par ligne), programme, et une photo choisie
    dans la médiathèque (`wp.media`, pas de champ URL à coller à la
    main). Boutons "Monter"/"Descendre" par ligne pour l'ordre d'affichage,
    "+ Ajouter une formation" pour une nouvelle ligne (clone d'un
    `<template>` avec un jeton `__INDEX__` remplacé côté JS, même
    principe que le clonage de `.js-formation-template` en front),
    "Supprimer" par ligne. Un seul bouton "Enregistrer les modifications"
    pour tout le formulaire.
  - **Galerie** (menu "Galerie") : même principe, une ligne par photo
    (légende + photo), la première ligne de la liste étant celle qui
    devient la vignette "en avant" avec le défilement automatique.
  - Le rewrite `/formations/{slug}/` redevient une règle de réécriture
    manuelle (`add_rewrite_rule()`) — il n'y a plus de CPT pour porter un
    rewrite natif, retour au mécanisme de la phase 4 bis (filet de
    sécurité de flush par version de plugin inclus).
- **Amorçage automatique inchangé dans son principe** : la première fois
  que l'option n'existe pas encore (`get_option(...) === false`), le
  plugin la préremplit avec les 4 licences/8 photos connues **et** copie
  leurs photos d'origine dans la médiathèque (`wp_upload_bits()` +
  `wp_insert_attachment()`), pour qu'une mise à jour ne fasse rien
  disparaître d'un site déjà en ligne — même raisonnement que pour le
  CPT, juste appliqué à une option plutôt qu'à des posts.
- **Lien "Réglages" dans la liste des extensions** : chaque plugin ajoute
  un lien "Réglages" à côté de son nom dans **Extensions → Extensions
  installées** (filtre `plugin_action_links_{basename}`), qui pointe vers
  son écran — pour que l'accès soit aussi visible depuis là où les
  administrateurs WordPress ont l'habitude de chercher les réglages d'une
  extension.
- **Toujours vrai depuis la version CPT** (inchangé par ce second
  changement) : les shortcodes `[up2a_formations]`/`[up2a_galerie]`, la
  dégradation propre via `function_exists()`/`shortcode_exists()` si un
  plugin est inactif, et la lecture prioritaire de
  `up2a_formations_formations()` par `up2a-preinscription` pour éviter
  qu'une formation ajoutée depuis le tableau de bord soit rejetée à la
  soumission du formulaire.

## Extraction En-tête/Pied de page/Slider/Actualités, SEO, login matricule (2026-09-28)

- **Pourquoi ce nouveau découpage** : même logique que la scission
  Formations/Galerie (2026-09-26/27) et même demande client — "gérer le
  site web facilement sur WordPress" — étendue au reste de la home.
  En-tête, Pied de page et Hero portaient encore du contenu réel
  (téléphone, adresse, logo, texte du bandeau défilant, diapositives,
  date de rentrée) codé en dur ou piloté par des constantes
  `wp-config.php` (`UP2A_ESPACE_ETUDIANT_URL`, `UP2A_RENTREE_DATE`) —
  plus aucune de ces deux constantes n'est nécessaire, tout est un champ
  d'un écran de réglages.
- **Ce qui a bougé, même principe que Formations/Galerie (une option par
  plugin, un écran de réglages `add_menu_page()`, un shortcode)** :
  - `up2a-header` : logo, téléphone, localisation courte, adresse
    complète, texte du bandeau défilant, URL de l'espace étudiant.
    Shortcode `[up2a_header]`, hooké sur `wp_body_open` — s'affiche
    désormais sur **toutes les pages** (avant : seulement la home et les
    pages détail formation, via un appel direct dans chaque template).
  - `up2a-footer` : logo, slogan, liens (répéteur label/URL), texte de
    copyright (jeton `{annee}` remplacé automatiquement). Shortcode
    `[up2a_footer]`, hooké sur `wp_footer` — également sitewide
    désormais, ce qui corrige une régression où des pages comme
    `/preinscription/` n'avaient **aucun** pied de page.
  - `up2a-slider` : diapositives du Hero (répéteur image + titre/sous-
    titre/CTA), date de rentrée (`<input type="date">` plutôt qu'une
    constante PHP). Shortcode `[up2a_slider]`, home uniquement.
  - `up2a-actualites` (nouveau module, pas une extraction — voir Scène 6
    plus haut) : actualités (répéteur titre/date/extrait/image/lien).
    Shortcode `[up2a_actualites]`, home uniquement. **Sans amorçage
    automatique**, à la différence des trois plugins ci-dessus : aucune
    actualité réelle n'existait avant ce module, donc rien à préserver —
    inventer un contenu de démonstration aurait violé CLAUDE.md §3.
- **Dépendance croisée sans lien rigide entre plugins** : la section
  Contact (`up2a-core`) lit désormais le téléphone/l'adresse via
  `function_exists('up2a_header_get_option')` plutôt qu'un appel direct
  à une fonction d'`up2a-core` — chaque carte de contact disparaît
  individuellement si sa donnée est vide, jamais d'erreur bloquante si
  `up2a-header` est inactif. Même motif déjà en place pour Formations/
  Galerie (voir plus haut).
- **Bug de portée CSS repéré et corrigé avant livraison** : les classes
  `.up2a-hero__cta*` sont en réalité les boutons **partagés** de tout le
  site (Pourquoi, Admissions, Documents, CTA final, Contact, modale
  Formations) et pas seulement du Hero. Les déplacer avec le reste du CSS
  du Hero vers `up2a-slider` (chargé uniquement sur la home) aurait cassé
  le style des boutons sur toute page ne chargeant pas ce plugin (ex.
  `/formations/{slug}/`). Ces règles sont restées dans
  `up2a-core/assets/css/up2a-front-page.css`.
- **Second bug repéré et corrigé** : le pied de page utilisait la classe
  utilitaire `.up2a-section-inner` (centrage/largeur max) définie dans
  `up2a-front-page.css`, chargée seulement sur la home — or le pied de
  page doit maintenant s'afficher partout. Les propriétés de centrage
  ont été recopiées directement dans `.up2a-footer__grid` (nouveau
  fichier CSS autonome `up2a-footer.css`) plutôt que de dépendre d'une
  classe utilitaire potentiellement absente.
- **SEO minimal ajouté à `up2a-core`** (menu "SEO") : méta description et
  image de partage par défaut (réglables), balises Open Graph/Twitter
  Card et URL canonique injectées en `wp_head` (priorité 1). Se
  désactive automatiquement si un plugin SEO dédié (Yoast, RankMath, All
  in One SEO) est détecté actif, pour ne jamais produire de balises en
  double. Le sitemap XML (`/wp-sitemap.xml`) et le robots.txt virtuel
  sont natifs à WordPress depuis la 5.5 — rien codé pour ça, juste à
  vérifier une fois le site en ligne.
- **Pages légales rédigées** (docs/07-pages-legales.md) : Mentions
  légales + Politique de confidentialité, avec des placeholders
  `[À CONFIRMER]` explicites pour toute information officielle non
  fournie par le client (numéro RCCM/IFU, nom légal de l'association
  gestionnaire, hébergeur, e-mail de contact...) — livrées comme du
  contenu à coller dans deux Pages WordPress, pas comme du code.
- **Login étudiant par matricule, SMTP abandonné définitivement** (hors
  périmètre de ce fichier storyboard home, mais lié à la même demande
  client) : voir `docs/03-roadmap.md` Phase 6 et
  `app-etudiant/README.md` "Connexion par matricule".

## Règles transverses (toutes scènes)

- Toute scène avec pin/effet 3D/parallaxe lourd est déclarée dans un bloc
  `ScrollTrigger.matchMedia()` avec au minimum les branches `isDesktop` /
  `isMobile`, la branche mobile étant toujours la version la plus légère.
- `prefers-reduced-motion: reduce` désactive tous les effets d'entrée
  (le contenu apparaît directement à son état final, sans transition).
- Aucun texte de contenu n'est écrit dans le JS : le JS ne fait que cibler
  les classes `js-*` posées sur les éléments Elementor et lire les
  `data-*` nécessaires (ex. `data-target` pour les compteurs).
