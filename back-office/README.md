# back-office

Back-office admin UP-2A — gestion des candidatures et saisie académique,
rôle `admin` uniquement. Next.js (App Router) + TypeScript +
Tailwind CSS v4 + `@supabase/supabase-js` / `@supabase/ssr`.

> **Statut (2026-09-25)** : application créée par séparation de
> l'ancienne app `app-etudiant` (qui portait les deux espaces sous
> `/etudiant` et `/admin`) — voir docs/01-architecture.md. Tout le
> périmètre back-office construit avant la séparation (candidatures,
> saisie académique, résultats, supports de cours, annonces) est repris
> à l'identique, URLs aplaties (`/admin/...` → `/...`, cette app étant
> désormais mono-usage).
>
> Même projet Supabase que `../app-etudiant/` (même schéma, même RLS) —
> voir `.env.example` pour les variables à renseigner (identiques à
> `app-etudiant`, plus `SUPABASE_SERVICE_ROLE_KEY` qui vit **uniquement**
> ici désormais).

## Démarrer

```bash
cp .env.example .env.local   # renseigner les vraies valeurs Supabase
npm install
npm run dev
```

Ouvrir [http://localhost:3000](http://localhost:3000).

## Structure

```
app/
├── layout.tsx              coquille HTML racine (pas d'auth ici)
├── connexion/                page + formulaire de connexion (Server Action)
└── (app)/                    groupe de routes protégé (rôle "admin")
    ├── layout.tsx              header + nav, requireRole("admin")
    ├── page.tsx                liste des candidatures (filtre par statut)
    ├── candidatures/[id]/      détail (pièces jointes, statut, transformation en étudiant)
    ├── emplois-du-temps/       CRUD créneaux (filtre formation/année)
    ├── examens/                CRUD examens (filtre formation/année)
    ├── resultats/              choix d'un examen → saisie groupée
    │   └── [examenId]/          saisie des notes + publication en masse
    ├── supports-cours/         upload + liste (filtre formation/année)
    └── annonces/               CRUD, ciblage formation/année optionnel

lib/
├── supabase/
│   ├── client.ts            client Supabase (Client Components, clé anon)
│   ├── server.ts             client Supabase (Server Components/Actions, clé anon)
│   ├── admin.ts               client Supabase clé service_role (server-only —
│   │                         uniquement pour créer un compte auth.users)
│   ├── middleware.ts          rafraîchissement de session + garde optimiste (proxy)
│   └── database.types.ts      types générés depuis le schéma (voir en-tête du fichier)
├── auth/
│   ├── dal.ts                 vérifications "sûres" (rôle) — voir "Garde d'authentification"
│   ├── actions.ts              Server Actions login/logout
│   └── logout-button.tsx       bouton de déconnexion partagé
├── academique/
│   └── constants.ts            labels FR (jours, types examen/document)
├── candidatures.ts            DAL candidatures (server-only)
├── actions.ts                  Server Actions candidatures
├── constants.ts                 statuts de candidature (liste + libellés)
├── academique.ts               DAL saisie académique (server-only) : formations,
│                               années, créneaux, examens, résultats, supports,
│                               annonces, URLs signées
├── academique-actions.ts       Server Actions saisie académique (CRUD créneaux/
│                               examens, upsert+publication résultats, upload
│                               supports, CRUD annonces)
├── formation-annee-filter.tsx  barre de filtre réutilisée (emplois du temps/
│                               examens/supports)
├── delete-button.tsx           bouton de suppression générique (confirm())
└── nav-links.tsx               nav horizontale avec lien actif en évidence

proxy.ts                     garde d'authentification "optimiste" (Next.js 16 :
                              remplace middleware.ts, voir docs/01-architecture.md)
```

## Candidatures (phase 7, MVP)

Liste des candidatures (`/`, filtrable par statut), détail d'une
candidature (`/candidatures/[id]`) avec ses pièces jointes (URLs
signées, 10 min), changement de statut, et transformation d'une
candidature **acceptée** en compte étudiant.

La transformation (`lib/actions.ts` → `transformerEnEtudiant`) :
1. Crée le compte `auth.users` avec un **mot de passe temporaire généré
   côté serveur** (`supabase.auth.admin.createUser`, e-mail déjà
   confirmé) — pas d'e-mail d'invitation envoyé : le SMTP du projet
   n'est pas configuré (voir `wordpress/README.md`), un compte qui en
   dépendrait serait inutilisable tant que ça reste le cas. Le mot de
   passe est renvoyé une seule fois à l'écran (jamais stocké en clair
   côté applicatif) pour que l'admin le communique lui-même à
   l'étudiant (téléphone, en personne...). L'étudiant peut le changer
   ensuite depuis l'espace étudiant (`/profil`). C'est la **seule**
   étape qui utilise la clé `service_role` (`lib/supabase/admin.ts`) ;
   tout le reste passe par le client `anon` + RLS, comme le reste de
   l'admin.
2. Crée les lignes `profiles` (role=etudiant) et `etudiants` (matricule
   généré `UP2A-{année}-{séquence}`, convention volontairement simple
   en l'absence de format imposé — à ajuster dans `genererMatricule()`
   si besoin).
3. Marque la candidature `transformee`.

**Si le SMTP est configuré plus tard**, on pourra revenir à
`inviteUserByEmail` (ou ajouter un envoi d'e-mail informatif en plus du
mot de passe affiché) — non fait pour l'instant, pas la peine de
construire sur une dépendance qui ne fonctionne pas encore.

## Saisie académique (phase 7)

Écrans filtrés par formation/année académique (sauf Annonces, qui liste
tout) via `FormationAnneeFilter` (barre de filtre GET) :

- **Emplois du temps** (`/emplois-du-temps`) : liste des créneaux triés
  jour/heure + formulaire de création + suppression.
- **Examens** (`/examens`) : liste triée par date + création +
  suppression, avec un lien direct vers la saisie des résultats.
- **Résultats** (`/resultats` → `/resultats/[examenId]`) : choisir un
  examen liste tous les étudiants actifs de sa formation/année
  (matricule + nom), avec un champ note (0-20) et mention par étudiant.
  Les champs laissés vides ne créent ni ne modifient de résultat (pas de
  note à 0 fabriquée). `upsertResultats` fait un `upsert` sur la
  contrainte `(etudiant_id, examen_id)`. Un bouton publie/dépublie en
  masse tous les résultats de l'examen — **un résultat non publié reste
  invisible pour l'étudiant, même le sien** (RLS `resultats_select_self_or_admin`).
- **Supports de cours** (`/supports-cours`) : upload direct vers le
  bucket Storage `supports-cours` (client `anon` + session admin — la
  policy `up2a_admin_supports_cours` autorise l'admin authentifié, pas
  besoin de `service_role`), chemin `{formation_id}/{annee_id}/{uuid}.{ext}`.
  Liste avec URL signée (10 min) + suppression (fichier + ligne).
- **Annonces** (`/annonces`) : création/suppression, ciblage formation
  et/ou année académique optionnel (vide = visible par tous, voir
  `annonces.formation_id`/`annee_academique_id` dans le schéma).

**Non couvert** (voir docs/03-roadmap.md phase 7) : gestion des
formations/facultés/années académiques elles-mêmes depuis l'admin (2
facultés et 4 licences fixes, peu de raison de changer souvent — SQL
Editor Supabase reste suffisant pour l'instant).

## Garde d'authentification (deux niveaux)

1. **Proxy** (`proxy.ts` → `lib/supabase/middleware.ts`) : vérification
   bon marché sur (quasiment) toutes les requêtes — session présente ou
   non. Toute route hors `/connexion` est protégée (cette app est
   mono-rôle depuis la séparation de l'espace étudiant).
2. **DAL** (`lib/auth/dal.ts`) : vérification "sûre" du **rôle**
   (`admin`), lue depuis `public.profiles` via une requête Supabase
   filtrée par RLS. Appelée dans `app/(app)/layout.tsx`.

**Redirection inter-applications** : un compte `etudiant` qui se
connecte ici est redirigé vers `NEXT_PUBLIC_ESPACE_ETUDIANT_URL` plutôt
que de boucler sur `/` (qui exigerait à nouveau le rôle `admin`). Si
cette variable n'est pas configurée, le compte est déconnecté plutôt que
de boucler.

## Variables d'environnement

Voir `.env.example`. `SUPABASE_SERVICE_ROLE_KEY` ne doit **jamais** être
utilisée dans un fichier exécuté côté client — uniquement dans des Server
Actions / Route Handlers qui en ont explicitement besoin
(`lib/actions.ts` → `transformerEnEtudiant`, la seule).

## Régénérer les types Supabase

```bash
npx supabase gen types typescript --local > lib/supabase/database.types.ts
```

Nécessite le stack Supabase local (Docker) — voir `../supabase/README.md`.
**Répercuter le même changement dans `../app-etudiant/lib/supabase/database.types.ts`**
(les deux apps ont chacune leur copie, voir docs/01-architecture.md).

> **Piège** : chaque table du fichier écrit à la main a besoin d'un
> champ `Relationships: [];` (même vide) en plus de
> `Row`/`Insert`/`Update` — sans lui, `supabase-js` infère `never` pour
> `.insert()`/`.update()` (silencieux : `.select()` continue de
> fonctionner, seules les mutations cassent). La commande CLI ci-dessus
> génère ce champ automatiquement ; si vous modifiez ce fichier à la
> main en attendant, pensez-y.

## Déploiement

Nouveau projet Vercel séparé de `app-etudiant` (Root Directory =
`back-office`), avec son propre sous-domaine (ex.
`admin.bdo-burkina.com`) — voir docs/01-architecture.md. Variables
d'environnement à renseigner dans Vercel : les mêmes que `.env.example`
ci-dessus, avec les vraies valeurs Supabase.
