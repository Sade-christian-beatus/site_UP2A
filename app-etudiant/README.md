# app-etudiant

Espace étudiant UP-2A — lecture seule, rôle `etudiant` uniquement.
Next.js (App Router) + TypeScript + Tailwind CSS v4 +
`@supabase/supabase-js` / `@supabase/ssr`.

> **Statut (2026-09-25)** : application **séparée** du back-office (voir
> `../back-office/`) depuis cette date — avant, les deux espaces
> vivaient dans la même app sous `/etudiant` et `/admin`. Sept écrans en
> lecture seule (voir "Écrans" ci-dessous), navigation moderne (sidebar
> desktop, nav mobile) avec avatar à initiales. Reste hors périmètre :
> génération de documents administratifs (l'écran existe mais rien ne
> dépose encore de fichier — voir docs/03-roadmap.md).
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
├── layout.tsx              coquille HTML racine (pas d'auth ici — voir plus bas)
├── connexion/               page + formulaire de connexion (Server Action)
└── (app)/                   groupe de routes protégé (rôle "etudiant")
    ├── layout.tsx            sidebar desktop / nav mobile + avatar, requireRole("etudiant")
    ├── sidebar-nav.tsx        nav verticale (desktop) + horizontale à pastilles (mobile)
    ├── page.tsx               tableau de bord (identité + stats + aperçus)
    ├── emploi-du-temps/
    ├── supports/
    ├── examens/
    ├── resultats/
    ├── documents/
    ├── annonces/
    └── profil/                 changement de mot de passe self-service

lib/
├── supabase/
│   ├── client.ts            client Supabase (Client Components, clé anon)
│   ├── server.ts             client Supabase (Server Components/Actions, clé anon)
│   ├── middleware.ts          rafraîchissement de session + garde optimiste (proxy)
│   └── database.types.ts      types générés depuis le schéma (voir en-tête du fichier)
├── auth/
│   ├── dal.ts                 vérifications "sûres" (rôle) — voir "Garde d'authentification"
│   ├── actions.ts              Server Actions login/logout
│   └── logout-button.tsx       bouton de déconnexion partagé
├── academique/
│   └── constants.ts            labels FR (jours, types examen/document)
├── etudiant/
│   └── data.ts                 DAL espace étudiant (server-only) — la plupart des
│                               requêtes n'ont pas besoin de `.eq(...)` explicite, la
│                               RLS filtre déjà par formation/année/étudiant connecté
├── avatar.tsx                 avatar à initiales (pas de vraie photo, voir plus bas)
└── icons.tsx                  icônes SVG en ligne (pas de librairie externe)

proxy.ts                     garde d'authentification "optimiste" (Next.js 16 :
                              remplace middleware.ts, voir docs/01-architecture.md)
```

## Écrans (phase 6, lecture seule)

Tous filtrés par RLS (`lib/etudiant/data.ts`) :

- **Tableau de bord** (`/`) : bandeau d'identité (avatar, matricule,
  formation, année, statut), 4 cartes de statistiques (examens à venir,
  supports, résultats publiés, annonces), aperçu des prochains examens
  et dernières annonces.
- **Emploi du temps** (`/emploi-du-temps`) : créneaux groupés par jour.
- **Supports de cours** (`/supports`) : liste + téléchargement (URL
  signée).
- **Examens** (`/examens`) : liste triée par date.
- **Résultats** (`/resultats`) : uniquement les résultats **publiés**
  (la RLS filtre `publie = true` en plus de
  `etudiant_id = mon_etudiant_id()` — un étudiant ne peut jamais voir un
  résultat non publié, même le sien).
- **Documents** (`/documents`) : écran prêt (URLs signées), mais affiche
  "Aucun document disponible" tant qu'aucune fonctionnalité admin ne
  dépose de fichier dans `documents-etudiants` (hors périmètre de cette
  passe — voir docs/03-roadmap.md).
- **Annonces** (`/annonces`) : liste triée par date de publication, déjà
  filtrée par la RLS (globales + celles ciblant sa formation/année).
- **Mon compte** (`/profil`) : changement de mot de passe self-service
  (`supabase.auth.updateUser`). Utile en particulier pour remplacer le
  mot de passe temporaire généré par l'admin lors de la transformation
  de candidature (voir `back-office/README.md` "Candidatures" — pas
  d'e-mail d'invitation tant que le SMTP n'est pas configuré). Accessible
  depuis l'avatar (sidebar desktop ou barre du haut mobile).

## Présentation (2026-09-25)

- **Sidebar** (desktop) / **nav à pastilles horizontale** (mobile) avec
  icônes SVG dessinées à la main (`lib/icons.tsx`, pas de police
  d'icônes ni de librairie externe — cohérent avec CLAUDE.md §7).
- **Avatar à initiales** (`lib/avatar.tsx`) : cercle coloré avec les
  initiales de l'étudiant, couleur dérivée d'un hash simple du nom
  (stable d'une session à l'autre). Pas de vraie photo : aucune n'est
  demandée aux étudiants à ce jour, et CLAUDE.md interdit de fabriquer
  du contenu visuel — un pattern d'avatar à initiales (Gmail, Slack...)
  reste honnête et reconnaissable sans avoir besoin d'un vrai portrait.
  Si l'upload d'une vraie photo est demandé plus tard : nouvelle colonne
  `profiles.avatar_url`, bucket Storage dédié, écran de profil, policy
  RLS — pas construit dans cette passe.
- Titre d'onglet distinct par écran (`export const metadata` par page).

## Garde d'authentification (deux niveaux)

1. **Proxy** (`proxy.ts` → `lib/supabase/middleware.ts`) : vérification
   bon marché sur (quasiment) toutes les requêtes — session présente ou
   non. Toute route hors `/connexion` est protégée (cette app est
   mono-rôle depuis la séparation du back-office, plus besoin d'une
   liste de routes protégées explicite).
2. **DAL** (`lib/auth/dal.ts`) : vérification "sûre" du **rôle**
   (`etudiant`), lue depuis `public.profiles` via une requête Supabase
   filtrée par RLS. Appelée dans `app/(app)/layout.tsx`.

Cette séparation suit la recommandation officielle Next.js : le proxy ne
doit faire que des vérifications optimistes (pas d'accès base de
données), la vérification faisant autorité se fait près de la donnée.

**Redirection inter-applications** : un compte `admin` qui se connecte
ici est redirigé vers `NEXT_PUBLIC_BACKOFFICE_URL` plutôt que de boucler
sur `/` (qui exigerait à nouveau le rôle `etudiant`). Si cette variable
n'est pas configurée, le compte est déconnecté plutôt que de boucler.

## Variables d'environnement

Voir `.env.example`. Cette app n'a **plus besoin** de
`SUPABASE_SERVICE_ROLE_KEY` depuis la séparation du back-office (la
seule opération qui l'utilisait, la création de compte `auth.users` lors
de la transformation d'une candidature, vit maintenant dans
`back-office/`) — réduit la surface d'exposition de ce secret.

## Régénérer les types Supabase

```bash
npx supabase gen types typescript --local > lib/supabase/database.types.ts
```

Nécessite le stack Supabase local (Docker) — voir `../supabase/README.md`.
**Répercuter le même changement dans `../back-office/lib/supabase/database.types.ts`**
(les deux apps ont chacune leur copie, voir docs/01-architecture.md).

> **Piège corrigé (2026-09-23)** : chaque table du fichier écrit à la
> main avait besoin d'un champ `Relationships: [];` (même vide) en plus
> de `Row`/`Insert`/`Update` — sans lui, `supabase-js` infère `never`
> pour `.insert()`/`.update()` (silencieux : `.select()` continue de
> fonctionner, seules les mutations cassent). La commande CLI ci-dessus
> génère ce champ automatiquement ; si vous modifiez ce fichier à la
> main en attendant, pensez-y.
