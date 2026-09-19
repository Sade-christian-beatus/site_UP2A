# 04 — Conventions

## Git

- Commits **en français, à l'impératif** : `Ajoute la politique RLS des
  résultats`, `Corrige la contrainte d'unicité du matricule`.
- Un commit = un sujet cohérent. Ne pas mélanger schéma SQL et scaffold
  front dans le même commit.
- Jamais de secret committé (clé `service_role`, mots de passe, `.env`
  rempli). `.env.example` uniquement, avec des valeurs factices.
- Branches : `feature/<sujet-court>`, `fix/<sujet-court>`.

## SQL / Supabase

- Tables et colonnes en **snake_case**, français (le domaine métier est en
  français : `candidatures`, `etudiants`, `annees_academiques`).
- Clé primaire : `id uuid primary key default gen_random_uuid()`.
- Horodatage : `created_at timestamptz not null default now()` partout ;
  `updated_at` uniquement sur les tables mutables (ex. `candidatures`).
- Clés étrangères : `<table_singulier>_id`, ex. `formation_id references
  public.formations(id)`.
- Enums métier via `create type ... as enum (...)` plutôt que des
  `varchar` libres, pour que la contrainte vive dans le schéma.
- Toute nouvelle table doit **activer RLS** dans la même migration qui la
  crée (`alter table ... enable row level security;`), même si les
  policies détaillées vivent dans `0002_rls.sql` pour le schéma initial.
- Migrations numérotées et rejouables : ne jamais modifier une migration
  déjà appliquée en production — en écrire une nouvelle.
- Nom de fichier de migration Supabase CLI : `<timestamp>_<description>.sql`
  généré par `supabase migration new <description>`. Les fichiers
  `0001_init.sql` / `0002_rls.sql` / `0003_seed.sql` du dépôt sont la
  version "lisible" de référence ; voir docs/01 et la structure
  `supabase/` pour la mise en cohérence avec le CLI.

## TypeScript / Next.js (`app-etudiant`)

- App Router (`app/`), Server Components par défaut, `"use client"`
  seulement quand nécessaire (interactivité, hooks).
- Un seul client Supabase par contexte d'exécution : `lib/supabase/
  server.ts` (server components/actions, cookies) et
  `lib/supabase/client.ts` (client components). Jamais de client Supabase
  instancié à la volée dans un composant.
- Variables d'environnement : `NEXT_PUBLIC_SUPABASE_URL`,
  `NEXT_PUBLIC_SUPABASE_ANON_KEY` côté client (préfixe `NEXT_PUBLIC_`
  obligatoire et volontaire — c'est la clé publique). `SUPABASE_SERVICE_ROLE_KEY`
  **sans** préfixe `NEXT_PUBLIC_`, utilisée uniquement dans des fichiers
  serveur (`route.ts`, `actions.ts`), jamais importée dans un composant
  client.
- Typage : générer les types depuis le schéma (`supabase gen types
  typescript`) plutôt que les écrire à la main, pour rester synchronisé
  avec les migrations.
- Nommage : composants en `PascalCase`, fichiers de composants en
  `kebab-case.tsx`, hooks `useXxx`.
- Pas de logique d'autorisation dupliquée côté client (pas de `if (role ===
  'admin')` pour cacher des données sensibles) : la RLS est la seule
  barrière de sécurité réelle, l'UI ne fait que refléter ce que
  l'utilisateur peut déjà obtenir via l'API.
- Interface et contenus en **français**.

## PHP / WordPress

- Espace de noms fonctionnel `up2a_` pour tous les hooks/fonctions des
  plugins maison (`up2a_register_preinscription_endpoint`, etc.), pour
  éviter les collisions avec d'autres plugins.
- Un plugin = un dossier sous `wordpress/plugins/`, avec un fichier
  d'entrée `<slug>.php` contenant l'en-tête standard WordPress.
- Toute donnée entrante (formulaire de préinscription) est validée et
  échappée côté serveur avant tout usage (`sanitize_text_field`,
  validation de type, etc.) — ne jamais faire confiance au client.
- Les appels vers Supabase avec `service_role` ne se font que depuis du
  code exécuté côté serveur (hooks PHP classiques, jamais dans un script
  chargé par le navigateur).
- Contenu éditorial dans Elementor, jamais codé en dur dans le PHP/JS des
  plugins.

## CSS / Design system

- Les tokens de docs/02-design-system.md sont la seule source de vérité
  pour les couleurs/typos/espacements — pas de couleur "magique" ajoutée
  à la volée dans Elementor ou dans le CSS custom.
- Classes d'animation préfixées `js-` (ex. `js-fade-in`, `js-pin-hero`) :
  ce sont des **hooks JS**, pas des classes de style. Le style vit dans
  des classes ou styles Elementor séparés.
- Respect systématique de `prefers-reduced-motion` : toute animation
  décorative doit avoir un fallback statique.

## Accessibilité & i18n

- Contenu et interface en français partout (site, préinscription, espace
  étudiant, back-office).
- Contrastes conformes AA minimum (voir docs/02), navigation clavier
  fonctionnelle sur les formulaires (préinscription, connexion).
