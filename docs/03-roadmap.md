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
