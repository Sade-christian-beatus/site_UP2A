# 03 — Roadmap

> Reprend l'ordre de travail de CLAUDE.md §6. Chaque phase doit être
> terminée (ou explicitement mise en pause avec l'accord du client) avant
> de commencer la suivante, sauf dépendance technique évidente (ex. le
> scaffold app-etudiant peut être posé en parallèle de la phase 1 puisqu'il
> ne dépend que du schéma, pas du contenu WordPress).

## Phase 0 — Cadrage (fait / en cours)

- [x] CLAUDE.md + docs/00 à 06 rédigés.
- [x] Revue des migrations SQL 0001/0002/0003.
- [x] Noms réels des 4 licences confirmés par le client (2026-09-22, voir
      docs/05-contenus.md) et alignés partout : seed Supabase, plugin
      `up2a-core`, plugin `up2a-preinscription`.
- [ ] Validation du client sur : palette/design system, reste des textes
      de docs/05-contenus.md (chiffres clés, contact précis...).

## Phase 1 — Supabase

- [ ] Créer le projet Supabase (le client fournit URL + clés).
- [ ] Appliquer `0001_init.sql`, `0002_rls.sql`, `0003_seed.sql` via
      Supabase CLI (`supabase db push` ou `supabase migration up`).
- [ ] Créer les buckets Storage (`candidatures`, `supports-cours`,
      `documents-etudiants`) + leurs policies.
- [ ] Créer le premier compte admin (procédure manuelle, voir
      docs/01-architecture.md "Bootstrap").
- [ ] Vérifier les RLS avec des requêtes de test (anon, étudiant, admin).

## Phase 2 — WordPress

- [ ] Installation propre sur le domaine cible (thème Hello Elementor).
- [ ] **Wipe de l'existant** — uniquement après feu vert explicite du
      client, jamais de façon autonome.
- [ ] Installer les plugins requis : Elementor (+ Pro si licence
      disponible), `up2a-core`, `up2a-preinscription`.
- [ ] Appliquer le design system (docs/02) : variables CSS, typographies,
      styles Elementor globaux.

## Phase 3 — Home cinématique

- [ ] Construire les sections dans Elementor à partir du storyboard
      (docs/06) et des textes (docs/05).
- [ ] Poser les classes `js-*` correspondantes (pas de contenu en dur dans
      le JS).
- [ ] Brancher `up2a-core` (GSAP + ScrollTrigger + SplitText + Lenis).
- [ ] `ScrollTrigger.matchMedia()` pour désactiver les effets lourds sur
      mobile ; respect de `prefers-reduced-motion`.

## Phase 4 — Formations (2026-09-25)

- [x] Décision : ni CPT ni montage Elementor — une règle de réécriture
      `/formations/{slug}/` + un template codé (`up2a-core`), qui relit
      `up2a_core_formations()` (déjà la source unique du contenu des 4
      licences, synchronisée avec `supabase/migrations/0003_seed.sql`) au
      lieu de dupliquer ce contenu dans un CPT pour seulement 4 pages
      fixes.
- [x] Une page détail par licence (contenu réel uniquement — programme
      détaillé marqué "à venir", rien d'inventé), structure commune :
      fil d'Ariane, bannière, présentation, débouchés, conditions
      d'admission, CTA préinscription préremplie, autres formations.
- [x] Cartes/modale de la home reliées vers ces pages ("Voir la fiche
      complète").

### Phase 4 bis — Scission Formations/Galerie (2026-09-26)

- [x] `up2a_core_formations()`/`up2a_core_render_formations()`/
      `up2a_core_find_formation()` + la règle de réécriture
      `/formations/{slug}/` déplacés dans un plugin dédié
      `up2a-formations` (renommés `up2a_formations_*`).
- [x] `up2a_core_gallery_images()`/`up2a_core_render_galerie()` déplacés
      dans un plugin dédié `up2a-galerie` (renommés `up2a_galerie_*`).
- [x] Les deux dépendent d'`up2a-core` (icônes, décor, styles partagés —
      en-tête `Requires Plugins`) ; la home affiche la section
      correspondante en moins si l'un n'est pas actif, jamais une
      erreur. Le footer (`up2a-core`) et la modale Formations (vignettes
      Galerie) utilisent `function_exists()` pour ce même motif.
      Voir wordpress/README.md "Statut des plugins maison" et
      docs/06-storyboard.md.

### Phase 4 ter — Réglages + shortcodes, contenu depuis le dashboard (2026-09-26 → 27)

- [x] `up2a_formations_formations()`/`up2a_galerie_images()` lisent
      désormais une option (`up2a_formations_option`/`up2a_galerie_option`,
      un tableau de formations/photos) au lieu d'un tableau statique dans
      le code — modifiable depuis un écran de réglages dans wp-admin
      (menus "Formations"/"Galerie"), plus besoin d'une mise à jour du
      plugin pour changer une photo ou un texte.
- [x] **Choix retenu : un écran de réglages (formulaire répéteur), pas un
      CPT.** Une première version utilisait un CPT (`up2a_formation`,
      `up2a_photo`) + une taxonomie Faculté — écartée sur retour explicite
      du client, qui voulait gérer chaque module "à travers réglage de
      l'extension" (un seul écran par plugin, pas une liste d'articles à
      ouvrir un par un). Voir docs/06-storyboard.md "Réglages Formations/
      Galerie" pour le détail des deux approches et pourquoi la seconde a
      remplacé la première.
- [x] Écran "Formations" : répéteur (ajouter/supprimer/réordonner une
      formation), champs nom/slug/faculté (code + nom complet, texte
      libre)/icône/description courte/débouchés/programme, photo choisie
      depuis la médiathèque (`wp.media`). Écran "Galerie" : même principe,
      champs légende + photo (l'alt vient du champ natif de la médiathèque).
- [x] Rewrite `/formations/{slug}/` : règle de réécriture manuelle (pas de
      CPT à accrocher un rewrite natif dessus) — même mécanisme que la
      phase 4 bis.
- [x] Shortcodes `[up2a_formations]`/`[up2a_galerie]` : la home les
      utilise par défaut (`do_shortcode()`), mais chaque section peut être
      replacée ailleurs (Elementor, éditeur de blocs) sans toucher au code.
- [x] Amorçage automatique (une fois, à la première lecture de l'option) :
      les 4 licences et 8 photos par défaut sont préremplies avec leurs
      photos d'origine (copiées dans la médiathèque) pour qu'une mise à
      jour ne fasse rien disparaître d'un site déjà en ligne. Une
      formation/photo ajoutée ensuite depuis l'écran de réglages sans
      image définie s'affiche sans photo plutôt que cassée.
- [x] `up2a-preinscription` lit `up2a_formations_formations()` en
      priorité pour sa validation de slug (avec repli statique si
      `up2a-formations` est inactif) — sans ça, une formation ajoutée
      depuis le dashboard aurait été rejetée à la soumission du formulaire.
      Voir docs/06-storyboard.md "Réglages Formations/Galerie".

### Phase 4 quater — En-tête/Pied de page/Slider/Actualités, SEO, pages légales, login matricule (2026-09-28)

- [x] `up2a_core_render_hero()`/`up2a_core_hero_slides()`/
      `up2a_core_rentree_date()`/`up2a_core_format_date_fr()` déplacés dans
      un plugin dédié `up2a-slider` (renommés `up2a_slider_*`), shortcode
      `[up2a_slider]`, écran de réglages "Slider" (répéteur de
      diapositives + champ date de rentrée — plus besoin de la constante
      `wp-config.php` `UP2A_RENTREE_DATE`).
- [x] L'en-tête (`up2a-core/inc/header.php`) déplacé dans un plugin dédié
      `up2a-header` (renommé `up2a_header_*`), shortcode `[up2a_header]`,
      écran de réglages "En-tête" (logo, téléphone, localisation, adresse,
      bandeau défilant, URL de l'espace étudiant — plus besoin de la
      constante `UP2A_ESPACE_ETUDIANT_URL`). S'affiche désormais sur
      **toutes les pages** (`wp_body_open`), pas seulement la home.
- [x] `up2a_core_render_footer()` déplacé dans un plugin dédié
      `up2a-footer` (renommé `up2a_footer_*`), shortcode `[up2a_footer]`,
      écran de réglages "Pied de page" (logo, slogan, liens, texte de
      copyright). S'affiche désormais sur **toutes les pages**
      (`wp_footer`), corrigeant une régression où des pages comme
      `/preinscription/` n'avaient aucun pied de page.
- [x] Nouveau module **Actualités** (`up2a-actualites`, inexistant avant
      cette date) : écran de réglages "Actualités" (répéteur titre/date/
      extrait/image/lien optionnel), shortcode `[up2a_actualites]`.
      **Aucun amorçage automatique** (contrairement aux autres modules) :
      pas d'actualité réelle à préserver, donc la section reste absente
      de la page tant qu'aucune actualité n'est ajoutée depuis le tableau
      de bord — jamais de contenu inventé (CLAUDE.md §3).
- [x] Ces quatre plugins dépendent d'`up2a-core` (icônes, décor, styles
      partagés — en-tête `Requires Plugins`), même motif que la phase 4
      bis ; les sections utilisant leurs données ailleurs (Contact,
      Footer) passent par `function_exists()` pour rester non bloquantes
      si un plugin est inactif.
- [x] `up2a-core/templates/front-page-onepage.php` : appel à l'ancien
      `up2a_core_render_hero()` (supprimé) remplacé par le shortcode
      `[up2a_slider]`, et `[up2a_actualites]` ajouté à la suite de la
      Galerie — les deux protégés par `shortcode_exists()`.
- [x] **SEO** minimal ajouté à `up2a-core` (menu "SEO") : méta
      description + image de partage par défaut (éditables), balises
      Open Graph/Twitter Card et URL canonique injectées en `wp_head`
      (désactivé automatiquement si un plugin SEO dédié est détecté).
      Sitemap XML et robots.txt : nativement fournis par WordPress, rien
      à coder.
- [x] **Pages légales** rédigées (docs/07-pages-legales.md) : Mentions
      légales + Politique de confidentialité, avec placeholders
      `[À CONFIRMER]` explicites pour toute donnée officielle non fournie
      par le client (RCCM/IFU, nom légal de l'association, hébergeur,
      e-mail de contact...) — à coller dans deux Pages WordPress.
- [x] **Login étudiant par matricule** : SMTP abandonné définitivement
      (pas seulement mis en pause) — l'étudiant se connecte avec le
      matricule fourni à son inscription plutôt que son e-mail. Voir
      Phase 6 pour le détail côté `app-etudiant`.

## Phase 5 — Préinscription (2026-09-23)

- [x] Finaliser `up2a-preinscription` : formulaire multi-étapes (4
      étapes : informations, formation, pièces jointes, envoi), upload
      des pièces vers Supabase Storage, appel serveur → Supabase
      (`service_role`), e-mail de confirmation (`wp_mail`).
- [x] **Aucun paiement.** Aucune étape ne mentionne de frais à régler en
      ligne — vérifié dans le shortcode et les e-mails envoyés.
- [x] Validation des champs obligatoires côté serveur (route REST
      `up2a/v1/preinscription`, indépendante de la validation JS qui
      n'est qu'un confort visiteur) — voir
      `wordpress/plugins/up2a-preinscription/inc/rest.php`.
- [ ] Tests avec de vrais identifiants Supabase une fois
      `UP2A_SUPABASE_URL`/`UP2A_SUPABASE_SERVICE_ROLE_KEY` configurés en
      production (le développement a validé le rendu et la navigation
      entre étapes en local, pas un appel Supabase réel — voir
      wordpress/README.md Étape 4/5).

## Phase 6 — Espace étudiant (app-etudiant, 2026-09-25)

- [x] Scaffold déjà posé en Phase 1 bis (voir ci-dessous) : auth,
      routage par rôle, design system.
- [x] Écrans étudiant (lecture) : tableau de bord, emploi du temps,
      supports de cours, examens, résultats, documents, annonces. Voir
      `app-etudiant/README.md` "Écrans".
- [x] **Application séparée du back-office** (même date) : `app-etudiant`
      ne porte plus que l'espace étudiant (rôle `etudiant` uniquement),
      le back-office vit désormais dans `back-office/` — deux projets
      Vercel, deux sous-domaines. Voir docs/01-architecture.md.
- [x] Présentation modernisée : sidebar desktop / nav mobile à pastilles
      avec icônes, avatar à initiales (couleur dérivée du nom, pas de
      vraie photo — voir `app-etudiant/README.md` "Présentation"),
      tableau de bord avec cartes de statistiques.
- [x] Chaque écran = requêtes Supabase filtrées par RLS (`lib/etudiant/data.ts`),
      pas de logique d'autorisation dupliquée côté client — les policies
      `*_select_self_or_admin` filtrent déjà les lignes, la plupart des
      requêtes n'ont même pas besoin d'un `.eq(...)` explicite.
- [x] Accès étudiant en lecture aux supports de cours de sa propre
      formation/année (`supabase/migrations/0005_storage_student_supports.sql`,
      basé sur la convention de chemin `{formation_id}/{annee_id}/...`).
- [x] **Connexion par matricule (2026-09-28)**, pas par e-mail : l'e-mail
      Supabase Auth devient un détail d'implémentation interne que
      l'étudiant ne voit jamais. Résolution matricule → e-mail via une
      fonction Postgres `SECURITY DEFINER`
      (`supabase/migrations/0006_resolve_etudiant_email.sql`, contourne
      la RLS car appelée avant authentification), puis
      `signInWithPassword` côté Server Action (`lib/auth/actions.ts`) —
      message d'erreur générique identique quelle que soit l'étape en
      échec, pour ne jamais révéler si un matricule existe. SMTP
      abandonné **définitivement** (pas mis en pause) : voir
      `app-etudiant/README.md` "Connexion par matricule".
- [ ] Documents administratifs : l'écran existe (lecture seule, URLs
      signées) mais rien ne dépose encore de fichier dans
      `documents-etudiants` — pas de fonctionnalité de génération de
      document construite dans cette passe (hors périmètre explicite de
      cette phase). Affiche honnêtement "Aucun document disponible" tant
      que ce n'est pas construit, plutôt que de fabriquer un écran qui
      ment sur l'état réel.

## Phase 7 — Back-office (2026-09-25, MVP complet)

- [x] **Application séparée de l'espace étudiant** (2026-09-25) : le
      back-office vit désormais dans `back-office/` (son propre projet
      Vercel, son propre sous-domaine), après avoir vécu sous `/admin`
      dans `app-etudiant`. URLs aplaties (`/admin/...` → `/...`, cette
      app étant mono-usage). Voir docs/01-architecture.md.
- [x] Écrans admin : liste des candidatures (filtre par statut) +
      changement de statut, transformation candidature → étudiant (crée
      compte `auth.users` via `supabase.auth.admin.inviteUserByEmail`,
      exécuté dans une Server Action plutôt qu'une route API dédiée — même
      garantie de sécurité, `service_role` toujours server-only, cohérent
      avec le reste du code qui utilise déjà des Server Actions partout).
      Voir `back-office/README.md` "Candidatures".
- [x] Saisie académique : emplois du temps, examens, résultats (saisie
      groupée par examen + publication/dépublication en masse), supports
      de cours (upload vers Storage), annonces (ciblage formation/année
      optionnel). Voir `back-office/README.md` "Saisie académique".
- [ ] Gestion des formations/facultés/années académiques elles-mêmes
      depuis l'admin (aujourd'hui : SQL Editor Supabase uniquement) — pas
      construit, non prioritaire vu la fréquence de changement quasi
      nulle de ces données (2 facultés, 4 licences fixes).

## Phase 8 — Optimisation & tests (démarrée 2026-09-25)

- [ ] Audit Lighthouse mobile (cible : performance ≥ 90 sur les pages
      vitrine) — nécessite le site en ligne, pas exécutable depuis cet
      environnement (sandbox sans accès réseau au domaine live).
- [x] Images en AVIF/WebP, lazy loading — fait au fil des sections
      construites. Pas encore de vidéo dans le projet (aucune fournie à
      ce jour), donc rien à compresser pour l'instant.
- [ ] Test réel sur Android milieu de gamme + 3G/4G — nécessite un
      appareil physique, à faire par le client/l'équipe.
- [x] Self-host GSAP/ScrollTrigger/SplitText/Lenis (`up2a-core` v0.13.0,
      `assets/js/vendor/`, voir `VERSIONS.md` du dossier) — plus aucun
      CDN externe chargé par le site.
- [ ] Minification/concaténation du JS **propre au projet**
      (`up2a-core.js`, `up2a-front-page.js`, `up2a-header.js`,
      `up2a-preinscription.js` — actuellement non minifiés). Les
      bibliothèques vendorisées (GSAP, Lenis) le sont déjà nativement.

---

## Phase 1 bis — Scaffold app-etudiant (peut démarrer en parallèle de la phase 1)

Réalisée sans écrans métier, juste le socle technique. **Note
(2026-09-25)** : à l'origine une seule app Next.js portant les deux
rôles (`/etudiant/*`, `/admin/*`) — depuis séparée en deux applications
distinctes (`app-etudiant` et `back-office`, voir phases 6/7 et
docs/01-architecture.md), chacune mono-rôle.

- [x] Next.js + TypeScript + Tailwind + `@supabase/supabase-js`.
- [x] Design system (docs/02) intégré comme tokens Tailwind.
- [x] Garde d'authentification (proxy + layout protégé).
- [x] Routage par rôle — remplacé depuis par deux apps séparées, chacune
      mono-rôle (voir note ci-dessus).

## Hors périmètre tant que non explicitement ajouté à cette roadmap

Voir docs/00-brief.md "Périmètre (out)". Toute demande hors de cette liste
doit être ajoutée ici avant d'être développée, pour garder une trace des
décisions de scope.
