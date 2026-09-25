# app-etudiant

Espace étudiant + back-office UP-2A. Next.js (App Router) + TypeScript +
Tailwind CSS v4 + `@supabase/supabase-js` / `@supabase/ssr`.

> **Statut (2026-09-25)** : authentification + routage par rôle en place.
> Back-office complet (candidatures + saisie académique + annonces,
> phases 7) et espace étudiant complet (phase 6, lecture seule) — voir
> "Back-office" et "Espace étudiant" ci-dessous. Reste hors périmètre :
> génération de documents administratifs (l'écran étudiant existe mais
> rien ne dépose encore de fichier) et gestion des
> formations/facultés/années académiques depuis l'admin (SQL Editor
> Supabase pour l'instant). Voir docs/01-architecture.md pour le modèle
> d'autorisation complet.
>
> **Avant de tester en local ou en prod** : appliquer
> `supabase/migrations/0005_storage_student_supports.sql` (SQL Editor
> Supabase) — sans elle, les étudiants ne peuvent pas télécharger leurs
> supports de cours (RLS Storage).

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
├── page.tsx              redirige vers /connexion ou /etudiant|/admin selon le rôle
├── connexion/             page + formulaire de connexion (Server Action)
├── etudiant/              section protégée (rôle "etudiant") — tableau de bord +
│   ├── emploi-du-temps/    6 écrans de lecture (voir "Espace étudiant")
│   ├── supports/
│   ├── examens/
│   ├── resultats/
│   ├── documents/
│   └── annonces/
└── admin/                 section protégée (rôle "admin")
    ├── page.tsx            liste des candidatures (filtre par statut)
    ├── candidatures/[id]/  détail d'une candidature (pièces jointes,
    │                       changement de statut, transformation en étudiant)
    ├── emplois-du-temps/   CRUD créneaux (filtre formation/année)
    ├── examens/            CRUD examens (filtre formation/année)
    ├── resultats/          choix d'un examen → saisie groupée
    │   └── [examenId]/      saisie des notes + publication en masse
    ├── supports-cours/     upload + liste (filtre formation/année)
    └── annonces/           CRUD, ciblage formation/année optionnel

lib/
├── supabase/
│   ├── client.ts          client Supabase (Client Components, clé anon)
│   ├── server.ts           client Supabase (Server Components/Actions, clé anon)
│   ├── admin.ts            client Supabase clé service_role (server-only —
│   │                       uniquement pour créer un compte auth.users)
│   ├── middleware.ts        rafraîchissement de session + garde optimiste (proxy)
│   └── database.types.ts    types générés depuis le schéma (voir en-tête du fichier)
├── auth/
│   ├── dal.ts               vérifications "sûres" (rôle) — utilisées par les layouts
│   ├── actions.ts            Server Actions login/logout
│   └── logout-button.tsx     bouton de déconnexion partagé
├── academique/
│   └── constants.ts          labels FR (jours, types examen/document) — PAS
│                             de "server-only", importable côté client
├── admin/
│   ├── constants.ts          statuts de candidature (liste + libellés) —
│   │                         PAS de "server-only", importable côté client
│   ├── candidatures.ts       DAL back-office candidatures (server-only)
│   ├── actions.ts            Server Actions candidatures
│   ├── academique.ts         DAL back-office saisie académique (server-only) :
│   │                         formations, années, créneaux, examens, résultats,
│   │                         supports, annonces, URLs signées
│   ├── academique-actions.ts Server Actions saisie académique (CRUD créneaux/
│   │                         examens, upsert+publication résultats, upload
│   │                         supports, CRUD annonces)
│   ├── formation-annee-filter.tsx  barre de filtre réutilisée (emplois du
│   │                                temps/examens/supports)
│   └── delete-button.tsx     bouton de suppression générique (confirm())
└── etudiant/
    └── data.ts               DAL espace étudiant (server-only) — la plupart
                              des requêtes n'ont pas besoin de `.eq(...)`
                              explicite, la RLS filtre déjà par formation/
                              année/étudiant connecté

proxy.ts                   garde d'authentification "optimiste" (Next.js 16 :
                            remplace middleware.ts, voir docs/01-architecture.md)
```

## Back-office — candidatures (phase 7, MVP)

Écrans construits : liste des candidatures (`/admin`, filtrable par
statut), détail d'une candidature (`/admin/candidatures/[id]`) avec ses
pièces jointes (URLs signées, 10 min), changement de statut, et
transformation d'une candidature **acceptée** en compte étudiant.

La transformation (`lib/admin/actions.ts` → `transformerEnEtudiant`) :
1. Invite le candidat par e-mail (`supabase.auth.admin.inviteUserByEmail`)
   — crée le compte `auth.users` et lui envoie un lien pour choisir son
   mot de passe. C'est la **seule** étape qui utilise la clé
   `service_role` (`lib/supabase/admin.ts`) ; tout le reste passe par le
   client `anon` + RLS, comme le reste de l'admin.
2. Crée les lignes `profiles` (role=etudiant) et `etudiants` (matricule
   généré `UP2A-{année}-{séquence}`, convention volontairement simple
   en l'absence de format imposé — à ajuster dans `genererMatricule()`
   si besoin).
3. Marque la candidature `transformee`.

**Important** : l'envoi de l'e-mail d'invitation nécessite le SMTP
Supabase configuré (Authentication → Emails dans le dashboard du
projet) — sans ça, `inviteUserByEmail` peut réussir côté base sans que
l'e-mail parte réellement. À vérifier avant la mise en production.

## Back-office — saisie académique (phase 7, 2026-09-25)

Écrans construits, tous filtrés par formation/année académique (sauf
Annonces, qui liste tout) via `FormationAnneeFilter` (barre de filtre GET,
réutilisée sur les 3 écrans) :

- **Emplois du temps** (`/admin/emplois-du-temps`) : liste des créneaux
  triés jour/heure + formulaire de création + suppression.
- **Examens** (`/admin/examens`) : liste triée par date + création +
  suppression, avec un lien direct vers la saisie des résultats.
- **Résultats** (`/admin/resultats` → `/admin/resultats/[examenId]`) :
  choisir un examen liste tous les étudiants actifs de sa formation/année
  (matricule + nom), avec un champ note (0-20) et mention par étudiant.
  Les champs laissés vides ne créent ni ne modifient de résultat (pas de
  note à 0 fabriquée). `upsertResultats` fait un `upsert` sur la
  contrainte `(etudiant_id, examen_id)`. Un bouton publie/dépublie en
  masse tous les résultats de l'examen — **un résultat non publié reste
  invisible pour l'étudiant, même le sien** (RLS `resultats_select_self_or_admin`).
- **Supports de cours** (`/admin/supports-cours`) : upload direct vers le
  bucket Storage `supports-cours` (client `anon` + session admin — la
  policy `up2a_admin_supports_cours` autorise l'admin authentifié, pas
  besoin de `service_role`), chemin `{formation_id}/{annee_id}/{uuid}.{ext}`.
  Liste avec URL signée (10 min) + suppression (fichier + ligne).
- **Annonces** (`/admin/annonces`) : création/suppression, ciblage
  formation et/ou année académique optionnel (vide = visible par tous,
  voir `annonces.formation_id`/`annee_academique_id` dans le schéma).

**Non couvert** (voir docs/03-roadmap.md phase 7) : gestion des
formations/facultés/années académiques elles-mêmes depuis l'admin (2
facultés et 4 licences fixes, peu de raison de changer souvent — SQL
Editor Supabase reste suffisant pour l'instant).

## Espace étudiant (phase 6, 2026-09-25)

Écrans construits, tous en lecture seule, filtrés par RLS
(`lib/etudiant/data.ts`) :

- **Tableau de bord** (`/etudiant`) : identité (matricule, formation,
  année, statut), prochains examens, dernières annonces.
- **Emploi du temps** (`/etudiant/emploi-du-temps`) : créneaux groupés
  par jour.
- **Supports de cours** (`/etudiant/supports`) : liste + téléchargement
  (URL signée).
- **Examens** (`/etudiant/examens`) : liste triée par date.
- **Résultats** (`/etudiant/resultats`) : uniquement les résultats
  **publiés** (la RLS filtre `publie = true` en plus de
  `etudiant_id = mon_etudiant_id()` — un étudiant ne peut jamais voir un
  résultat non publié, même le sien).
- **Documents** (`/etudiant/documents`) : écran prêt (URLs signées), mais
  affiche "Aucun document disponible" tant qu'aucune fonctionnalité
  admin ne dépose de fichier dans `documents-etudiants` (hors périmètre
  de cette passe — voir docs/03-roadmap.md).
- **Annonces** (`/etudiant/annonces`) : liste triée par date de
  publication, déjà filtrée par la RLS (globales + celles ciblant sa
  formation/année).

## Modèle de garde d'authentification (deux niveaux)

1. **Proxy** (`proxy.ts` → `lib/supabase/middleware.ts`) : vérification bon
   marché sur (quasiment) toutes les requêtes — session présente ou non.
   Redirige `/etudiant`, `/admin` → `/connexion` si pas de session, et
   `/connexion` → `/` si déjà connecté.
2. **DAL** (`lib/auth/dal.ts`) : vérification "sûre" du **rôle**
   (`etudiant`/`admin`), lue depuis `public.profiles` via une requête
   Supabase filtrée par RLS. Appelée dans `app/etudiant/layout.tsx` et
   `app/admin/layout.tsx`.

Cette séparation suit la recommandation officielle Next.js : le proxy ne
doit faire que des vérifications optimistes (pas d'accès base de
données), la vérification faisant autorité se fait près de la donnée.

## Variables d'environnement

Voir `.env.example`. `SUPABASE_SERVICE_ROLE_KEY` ne doit **jamais** être
utilisée dans un fichier exécuté côté client — uniquement dans des Server
Actions / Route Handlers qui en ont explicitement besoin.

## Régénérer les types Supabase

```bash
npx supabase gen types typescript --local > lib/supabase/database.types.ts
```

Nécessite le stack Supabase local (Docker) — voir `../supabase/README.md`.

> **Piège corrigé (2026-09-23)** : chaque table du fichier écrit à la
> main avait besoin d'un champ `Relationships: [];` (même vide) en plus
> de `Row`/`Insert`/`Update` — sans lui, `supabase-js` infère `never`
> pour `.insert()`/`.update()` (silencieux : `.select()` continue de
> fonctionner, seules les mutations cassent). La commande CLI ci-dessus
> génère ce champ automatiquement ; si vous modifiez ce fichier à la
> main en attendant, pensez-y.
