# CLAUDE.md — Contexte projet UP-2A

> Fichier maître lu par Claude Code. Il donne la mission, l'architecture, les
> contraintes et les conventions. Lis-le en entier avant toute action, puis
> réfère-toi aux docs pointées dans `/docs`.

## 1. Mission

Construire le site et la plateforme numérique de **l'Université Privée
An-Nahdah d'Afrique (UP-2A)**, Ouagadougou. Slogan : *« Former aujourd'hui les
élites de demain »*. Université privée reconnue (autorisation MESRI
n°2026-001647). Portée par une association islamique → identité de **valeurs**
(excellence, savoir, intégrité), ton ouvert et professionnel.

Client réalisé par **LUPORA Group**.

## 2. Périmètre

- **Vitrine publique** : site WordPress cinématique (home déroulante « à la
  zero.university », adaptée à la connexion locale) + pages formations,
  admissions, actualités, contact.
- **Préinscription** : formulaire multi-étapes **SANS paiement**, écrit dans
  Supabase.
- **Espace étudiant** : app **Supabase** (auth + données) — tableau de bord,
  emploi du temps, supports, examens, résultats, documents, annonces.
- **Back-office** : administration des utilisateurs, formations, candidatures,
  académique, contenus.

## 3. Contraintes qui gouvernent les décisions

- ⚠️ **Le site actuel https://bdo-burkina.com est un point de départ jetable** :
  son contenu sera **entièrement supprimé** et remplacé. Repartir propre.
- ⚠️ **Préinscription = AUCUN paiement** (pas de mobile money, pas de frais en
  ligne). Ne rien ajouter de tel.
- **Supabase = source unique de vérité** pour candidatures → étudiants →
  académique. WordPress ne fait que la vitrine + le formulaire de préinscription
  (qui pousse vers Supabase côté serveur).
- **Public cible = étudiants burkinabè sur data mobile** → performance =
  exigence de premier plan, pas une option (voir §7).
- **Images fournies au fil de l'eau** : un seul visuel existe aujourd'hui
  (rendu du bâtiment). Les autres seront **générés par IA et téléversés**
  progressivement. Prévoir des emplacements/placeholders, ne pas bloquer sur les
  assets manquants.
- **GSAP est 100% gratuit** (Webflow, depuis 2025), tous plugins inclus
  (ScrollTrigger, SplitText). Aucune licence à gérer.

## 4. Stack

| Couche | Techno |
|---|---|
| Vitrine | WordPress + Elementor (thème Hello Elementor) |
| Animation | GSAP + ScrollTrigger + SplitText + Lenis (plugin `up2a-core`) |
| Préinscription | Plugin `up2a-preinscription` (PHP → Supabase REST) |
| Base de données / Auth | Supabase (PostgreSQL + Auth + Storage + RLS) |
| Espace étudiant + Back-office | **Next.js + @supabase/supabase-js** (recommandé) sur sous-domaine. *Alternative acceptable : FastAPI si préférence Python — le schéma SQL reste identique.* |

## 5. Carte du dépôt

```
up2a/
├── CLAUDE.md                     ← ce fichier
├── docs/
│   ├── 00-brief.md               brief / périmètre / non-objectifs
│   ├── 01-architecture.md        architecture & flux de données
│   ├── 02-design-system.md       palette, typos, tokens
│   ├── 03-roadmap.md             plan de fabrication phasé (tâches)
│   ├── 04-conventions.md         standards de code
│   ├── 05-contenus.md            textes rédigés des pages (à coller)
│   └── 06-storyboard.md          storyboard scène par scène de la home
├── supabase/
│   ├── migrations/0001_init.sql  schéma
│   ├── migrations/0003_seed.sql  facultés + 4 licences + année
│   └── policies/0002_rls.sql     politiques d'accès (RLS)
├── wordpress/
│   ├── README.md                 install propre + wipe + plugins requis
│   └── plugins/
│       ├── up2a-core/            couche animation (GSAP/Lenis)
│       └── up2a-preinscription/  formulaire → Supabase (sans paiement)
└── app-etudiant/
    └── README.md                 spec de l'app Next.js + Supabase
```

## 6. Ordre de travail recommandé

1. **Supabase** : appliquer `0001_init.sql`, `0002_rls.sql`, `0003_seed.sql`.
   Créer un premier compte admin (voir docs/01).
2. **WordPress** : install propre sur le domaine, wipe de l'existant, installer
   les plugins requis + `up2a-core`, appliquer le design system.
3. **Home** : construire les sections dans Elementor (contenu = docs/05), poser
   les classes JS (docs/06), brancher la couche animation.
4. **Formations** : CPT + pages détail (une par licence).
5. **Préinscription** : finaliser `up2a-preinscription` (form multi-étapes,
   upload des pièces, e-mail de confirmation) — **sans paiement**.
6. **Espace étudiant** (app-etudiant) : auth Supabase + tableau de bord + EDT +
   supports + examens + résultats + annonces (lecture, RLS étudiant).
7. **Back-office** : mêmes données en rôle admin (gestion candidatures →
   création étudiants, saisie académique, publication).
8. **Optimisation perf** + tests mobile/4G.

## 7. Règles de performance (non négociables)

- Images en **AVIF/WebP**, compressées, dimensionnées ; `loading="lazy"`.
- Vidéos : compressées, avec `poster`, lazy, idéalement Cloudflare Stream /
  bunny.net ; sinon self-host optimisé.
- Effets lourds (pin, 3D) **désactivés sur mobile** via `ScrollTrigger.matchMedia()`.
- Respecter **`prefers-reduced-motion`**.
- Tester sur **Android milieu de gamme + 4G/3G réelle**.
- En prod : self-host + minifier/concaténer les JS (pas de multi-CDN).

## 8. Conventions rapides

- Contenu éditable = dans WordPress/Elementor. Animation = dans le code, ciblant
  des **classes CSS** (`js-*`). Ne jamais coder du contenu en dur dans le JS.
- Secrets (clé `service_role` Supabase) : **uniquement côté serveur**
  (wp-config.php), jamais dans le navigateur. Le navigateur n'utilise que la clé
  **anon** + RLS.
- Langue de l'interface et des contenus : **français**.
- Détails de code : voir `docs/04-conventions.md`.

## 9. Non-objectifs (ne pas faire)

- Pas de paiement en ligne dans la préinscription.
- Pas de vrai modèle 3D navigable du bâtiment généré depuis une image (non
  fiable) ; profondeur = parallaxe 2.5D / pré-rendu.
- Ne pas conserver le contenu de bdo-burkina.com.
