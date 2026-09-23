# app-etudiant

Espace étudiant + back-office UP-2A. Next.js (App Router) + TypeScript +
Tailwind CSS v4 + `@supabase/supabase-js` / `@supabase/ssr`.

> **Statut (2026-09-23)** : authentification + routage par rôle en place.
> Back-office candidatures construit (phase 7, périmètre MVP — voir
> "Back-office" ci-dessous). Espace étudiant (phase 6) et le reste du
> back-office (saisie académique, annonces) restent à construire.
> Voir docs/01-architecture.md pour le modèle d'autorisation complet.

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
├── etudiant/              section protégée (rôle "etudiant"), vide pour l'instant
└── admin/                 section protégée (rôle "admin")
    ├── page.tsx            liste des candidatures (filtre par statut)
    └── candidatures/[id]/  détail d'une candidature (pièces jointes,
                             changement de statut, transformation en étudiant)

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
└── admin/
    ├── constants.ts          statuts de candidature (liste + libellés) —
    │                         PAS de "server-only", importable côté client
    ├── candidatures.ts       DAL back-office (server-only) : liste, détail,
    │                         URLs signées des pièces jointes
    └── actions.ts            Server Actions : changer un statut, transformer
                              une candidature en compte étudiant

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

**Non couvert par ce MVP** (reste de la phase 7, voir
docs/03-roadmap.md) : saisie académique (emplois du temps, examens,
résultats), publication d'annonces et de supports de cours.

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
