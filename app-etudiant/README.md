# app-etudiant

Espace étudiant + back-office UP-2A. Next.js (App Router) + TypeScript +
Tailwind CSS v4 + `@supabase/supabase-js` / `@supabase/ssr`.

> Scaffold uniquement à ce stade : authentification + routage par rôle en
> place, **aucun écran métier** (voir docs/03-roadmap.md, phases 6 et 7).
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
└── admin/                 section protégée (rôle "admin"), vide pour l'instant

lib/
├── supabase/
│   ├── client.ts          client Supabase (Client Components, clé anon)
│   ├── server.ts           client Supabase (Server Components/Actions, clé anon)
│   ├── middleware.ts        rafraîchissement de session + garde optimiste (proxy)
│   └── database.types.ts    types générés depuis le schéma (voir en-tête du fichier)
└── auth/
    ├── dal.ts               vérifications "sûres" (rôle) — utilisées par les layouts
    ├── actions.ts            Server Actions login/logout
    └── logout-button.tsx     bouton de déconnexion partagé

proxy.ts                   garde d'authentification "optimiste" (Next.js 16 :
                            remplace middleware.ts, voir docs/01-architecture.md)
```

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
