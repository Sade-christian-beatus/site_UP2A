# 03 — Roadmap

> Reprend l'ordre de travail de CLAUDE.md §6. Chaque phase doit être
> terminée (ou explicitement mise en pause avec l'accord du client) avant
> de commencer la suivante, sauf dépendance technique évidente (ex. le
> scaffold app-etudiant peut être posé en parallèle de la phase 1 puisqu'il
> ne dépend que du schéma, pas du contenu WordPress).

## Phase 0 — Cadrage (fait / en cours)

- [x] CLAUDE.md + docs/00 à 06 rédigés.
- [x] Revue des migrations SQL 0001/0002/0003.
- [ ] Validation du client sur : palette/design system, textes de
      docs/05-contenus.md, noms réels des 4 licences (actuellement
      placeholders dans le seed).

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

## Phase 4 — Formations

- [ ] Custom Post Type "Formation" (ou pages statiques si CPT jugé
      excessif pour 4 licences — à trancher en phase, cf. docs/04).
- [ ] Une page détail par licence (contenu depuis docs/05, structure
      commune).

## Phase 5 — Préinscription

- [ ] Finaliser `up2a-preinscription` : formulaire multi-étapes, upload
      des pièces, appel serveur → Supabase (`service_role`), e-mail de
      confirmation.
- [ ] **Aucun paiement.** Vérifier qu'aucune étape ne mentionne de frais à
      régler en ligne.
- [ ] Tests : soumission complète, gestion des erreurs réseau, validation
      des champs obligatoires côté serveur (pas seulement côté client).

## Phase 6 — Espace étudiant (app-etudiant)

- [ ] Scaffold déjà posé en Phase 1 bis (voir ci-dessous) : auth,
      routage par rôle, design system.
- [ ] Écrans étudiant (lecture) : tableau de bord, emploi du temps,
      supports de cours, examens, résultats, documents, annonces.
- [ ] Chaque écran = requêtes Supabase filtrées par RLS, pas de logique
      d'autorisation dupliquée côté client.

## Phase 7 — Back-office

- [ ] Écrans admin : liste des candidatures + changement de statut,
      transformation candidature → étudiant (crée compte `auth.users` via
      route API serveur avec `service_role`), saisie académique
      (formations, emplois du temps, examens, résultats), publication
      d'annonces et de supports de cours.

## Phase 8 — Optimisation & tests

- [ ] Audit Lighthouse mobile (cible : performance ≥ 90 sur les pages
      vitrine).
- [ ] Images en AVIF/WebP, lazy loading, vidéos compressées avec poster.
- [ ] Test réel sur Android milieu de gamme + 3G/4G.
- [ ] Minification/concaténation JS en prod, self-host (pas de multi-CDN).

---

## Phase 1 bis — Scaffold app-etudiant (peut démarrer en parallèle de la phase 1)

Réalisée sans écrans métier, juste le socle technique :

- [ ] Next.js + TypeScript + Tailwind + `@supabase/supabase-js`.
- [ ] Design system (docs/02) intégré comme tokens Tailwind.
- [ ] Garde d'authentification (middleware / layout protégé).
- [ ] Routage par rôle (`/etudiant/*`, `/admin/*`) — pages vides pour
      l'instant, pas d'écran métier.

## Hors périmètre tant que non explicitement ajouté à cette roadmap

Voir docs/00-brief.md "Périmètre (out)". Toute demande hors de cette liste
doit être ajoutée ici avant d'être développée, pour garder une trace des
décisions de scope.
