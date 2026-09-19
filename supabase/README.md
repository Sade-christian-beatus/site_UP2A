# Supabase — structure locale

## Organisation

```
supabase/
├── config.toml              généré par `supabase init`, config du stack local
├── migrations/
│   ├── 0001_init.sql         schéma (tables, types, index, triggers)
│   ├── 0002_rls.sql          politiques RLS + fonctions d'autorisation
│   └── 0003_seed.sql         données de démonstration (⚠️ placeholders, voir le fichier)
└── .gitignore                ignore .branches/ et .temp/ (générés par la CLI)
```

> **Écart volontaire par rapport au repo map de CLAUDE.md §5** : CLAUDE.md
> décrit `0002_rls.sql` comme un fichier séparé sous `supabase/policies/`.
> Il a été déplacé dans `supabase/migrations/` pour une raison technique :
> la CLI Supabase (`supabase migration up`, `supabase db reset`,
> `supabase db push`) ne rejoue que les fichiers présents dans
> `supabase/migrations/`, dans l'ordre lexical de leur nom. Un fichier
> laissé dans `supabase/policies/` ne serait jamais appliqué
> automatiquement — ce qui allait à l'encontre de la mission "rendre ces
> fichiers rejouables proprement". Le contenu (RLS + fonctions
> d'autorisation) reste inchangé, seul l'emplacement a changé.

## Prérequis

- [Supabase CLI](https://supabase.com/docs/guides/cli) (utilisée ici via
  `npx supabase <commande>`, aucune installation globale requise).
- Docker, pour lancer la stack complète en local (`supabase start`) — non
  disponible dans cet environnement d'exécution, donc non testé ici (voir
  "Ce qui a été vérifié" ci-dessous).

## Commandes utiles

```bash
# Démarrer la stack Supabase locale complète (Postgres + Auth + Storage + API)
npx supabase start

# Rejouer les migrations sur la base locale depuis zéro
npx supabase db reset

# Appliquer les migrations sur un projet distant (une fois SUPABASE_ACCESS_TOKEN
# et le projet liés via `supabase link`)
npx supabase db push

# Générer les types TypeScript à partir du schéma (utilisé par app-etudiant)
npx supabase gen types typescript --local > ../app-etudiant/src/lib/supabase/database.types.ts
```

## Ce qui a été vérifié dans cet environnement

Docker n'étant pas disponible ici, la stack Supabase complète (`supabase
start`) n'a pas pu être testée. À la place, les trois migrations ont été
rejouées dans l'ordre (`0001` → `0002` → `0003`) sur un PostgreSQL 16 local,
avec un schéma `auth.users` minimal simulé, pour valider que :

- le DDL s'applique sans erreur (types, tables, index, triggers) ;
- les policies et fonctions RLS se créent sans erreur de dépendance ;
- le seed s'insère correctement ;
- les contraintes clés fonctionnent (unicité de l'année académique
  "courante", unicité des slugs, `check` sur les notes 0–20, etc.).

Ceci valide la **structure et la validité SQL** des migrations. Cela ne
teste pas le comportement réel des politiques RLS sous PostgREST (qui
nécessite le vrai stack Auth/API via Docker) — à faire dès que
l'environnement de développement du client sera disponible, avant la mise
en production.

## Bootstrap du premier compte admin

Voir docs/01-architecture.md "Bootstrap" — volontairement non automatisé
dans une migration (pas de mot de passe en clair versionné).
