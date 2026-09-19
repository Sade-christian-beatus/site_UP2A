# Supabase — structure locale

## Organisation

```
supabase/
├── config.toml              généré par `supabase init` + buckets Storage déclarés
├── migrations/
│   ├── 0001_init.sql              schéma (tables, types, index, triggers)
│   ├── 0002_rls.sql               politiques RLS + fonctions d'autorisation
│   ├── 0003_seed.sql              données de démonstration (⚠️ placeholders, voir le fichier)
│   └── 0004_storage_policies.sql  RLS sur storage.objects (buckets, admin uniquement pour l'instant)
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
start`) n'a pas pu être testée. À la place, les quatre migrations ont été
rejouées dans l'ordre (`0001` → `0002` → `0003` → `0004`) sur un
PostgreSQL 16 local, avec un schéma `auth.users`/`storage.objects` minimal
simulé, pour valider que :

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

## Application manuelle sur le vrai projet (sans CLI)

L'environnement d'exécution qui a produit ce scaffold n'a pas d'accès
réseau sortant vers `*.supabase.co` (politique réseau de l'environnement
Claude Code — voir https://code.claude.com/docs/en/claude-code-on-the-web).
Les migrations n'ont donc pas pu être appliquées automatiquement sur le
vrai projet. Voici la procédure manuelle via le dashboard, qui ne
nécessite ni CLI ni accès réseau particulier de votre côté :

### 1. Schéma, RLS, seed, policies Storage

Dans le dashboard Supabase du projet → **SQL Editor** → **New query**,
coller puis exécuter (**Run**) le contenu de chaque fichier, **dans cet
ordre exact**, un fichier à la fois :

1. `supabase/migrations/0001_init.sql`
2. `supabase/migrations/0002_rls.sql`
3. `supabase/migrations/0003_seed.sql`
4. `supabase/migrations/0004_storage_policies.sql` (⚠️ à exécuter **après**
   avoir créé les 3 buckets Storage à l'étape 2 ci-dessous — sinon les
   policies référencent des `bucket_id` qui n'existent pas encore ; ça ne
   provoque pas d'erreur SQL mais les policies seraient inutiles tant que
   les buckets n'existent pas)

Vérification : **Table Editor** → `facultes` (4 lignes), `formations`
(4 lignes), `annees_academiques` (1 ligne, `est_courante = true`).

### 2. Buckets Storage

**Storage** → **New bucket**, créer les 3 buckets suivants avec les
réglages de `supabase/config.toml` (section `[storage.buckets.*]`) :

| Bucket | Public | Taille max | Types autorisés |
|---|---|---|---|
| `candidatures` | Non | 10 MiB | `application/pdf`, `image/jpeg`, `image/png` |
| `supports-cours` | Non | 50 MiB | pdf, doc/docx, ppt/pptx, jpeg, png |
| `documents-etudiants` | Non | 10 MiB | `application/pdf` |

Si l'interface ne propose pas de restreindre les types MIME à la
création, laissez public **désactivé** (le plus important) et passez à la
suite — la restriction de type est une protection secondaire.

Puis exécuter `0004_storage_policies.sql` (étape 1.4 ci-dessus) si ce
n'est pas déjà fait.

### 3. Premier compte admin

1. **Authentication** → **Users** → **Add user** → **Create new user**.
   Renseignez un e-mail et un mot de passe fort (à conserver dans un
   gestionnaire de mots de passe), cochez **Auto Confirm User**.
2. Copier l'**UID** de l'utilisateur créé (visible dans la liste des
   utilisateurs).
3. **SQL Editor** → nouvelle requête :

   ```sql
   insert into public.profiles (id, role, nom, prenom)
   values ('<UID copié>', 'admin', '<Nom>', '<Prénom>');
   ```

4. Vérification : **Table Editor** → `profiles` → une ligne avec
   `role = admin` doit apparaître.

### Checklist de vérification finale

- [ ] `facultes` : 4 lignes, `formations` : 4 lignes, `annees_academiques` : 1 ligne courante
- [ ] 3 buckets Storage créés, tous non publics
- [ ] `select * from pg_policies where tablename = 'objects';` renvoie 3 lignes (`up2a_admin_*`)
- [ ] 1 utilisateur créé dans Authentication, confirmé
- [ ] 1 ligne dans `profiles` avec `role = admin` liée à cet utilisateur

## Bootstrap du premier compte admin

Voir "Application manuelle" ci-dessus (étape 3) et docs/01-architecture.md
"Bootstrap" — volontairement non automatisé dans une migration (pas de
mot de passe en clair versionné).
