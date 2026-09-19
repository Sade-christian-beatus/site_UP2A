# 02 — Design system

> **Statut : proposition de départ, à valider par le client** (LUPORA
> Group / UP-2A) avant application définitive dans Elementor et Tailwind.
> Rien ici n'est une charte graphique officielle imposée par l'université —
> c'est un point de départ cohérent avec le brief (valeurs d'excellence,
> savoir, intégrité ; ton ouvert et professionnel).

## Principe directeur

Une identité qui évoque le sérieux académique et les valeurs de
l'association fondatrice (excellence, savoir, intégrité) **sans
vocabulaire confessionnel visuel appuyé** (pas de motifs religieux
explicites) — sobriété institutionnelle + une touche dorée pour le
prestige académique.

## Palette (proposition)

| Token | Valeur | Usage |
|---|---|---|
| `--color-primary` | `#0B4D3C` (vert profond) | Couleur de marque principale : header, CTA primaires, liens |
| `--color-primary-dark` | `#083A2D` | Hover/actif sur primaire |
| `--color-accent` | `#C9A227` (or/doré) | Accents de prestige : soulignés, badges, séparateurs, détails |
| `--color-accent-light` | `#E4C868` | Hover sur accent, fonds subtils |
| `--color-ink` | `#14202B` (encre bleu-nuit) | Texte principal |
| `--color-ink-soft` | `#3E4C57` | Texte secondaire |
| `--color-bg` | `#F7F5F0` (blanc cassé/crème) | Fond de page par défaut |
| `--color-surface` | `#FFFFFF` | Cartes, formulaires |
| `--color-border` | `#E2DFD6` | Séparateurs, bordures discrètes |
| `--color-success` | `#2E7D32` | Confirmations (ex. préinscription envoyée) |
| `--color-error` | `#B3261E` | Erreurs de formulaire |
| `--color-warning` | `#B8860B` | Avertissements |

Contraste vérifié AA minimum pour `--color-ink` sur `--color-bg`/`--color-surface`,
et pour le texte blanc sur `--color-primary`.

## Typographie

- **Titres** : police serif institutionnelle (proposition : *Fraunces* ou
  *Playfair Display*, disponibles Google Fonts, self-hostées en prod) —
  se prête bien à l'animation `SplitText` (headline cinématique de la home).
- **Corps de texte / UI** : police sans-serif lisible (proposition :
  *Inter* ou *Manrope*), utilisée pour paragraphes, formulaires, espace
  étudiant.
- Échelle typographique (rem, base 16px) :

| Token | Taille | Usage |
|---|---|---|
| `--text-xs` | 0.75rem | Légendes, métadonnées |
| `--text-sm` | 0.875rem | Texte secondaire |
| `--text-base` | 1rem | Corps de texte |
| `--text-lg` | 1.125rem | Intro de section |
| `--text-xl` | 1.5rem | Sous-titres |
| `--text-2xl` | 2rem | Titres de section |
| `--text-3xl` | 2.75rem | Titre de page |
| `--text-4xl` | 3.75rem | Hero home (desktop uniquement, réduit sur mobile) |

## Espacement

Échelle en multiples de 4px : `--space-1` (4px) à `--space-16` (64px),
utilisée aussi bien dans Elementor (variables globales) que dans le thème
Tailwind de `app-etudiant`.

```
--space-1: 0.25rem;  --space-2: 0.5rem;   --space-3: 0.75rem;
--space-4: 1rem;     --space-6: 1.5rem;   --space-8: 2rem;
--space-12: 3rem;    --space-16: 4rem;
```

## Rayons & ombres

```
--radius-sm: 4px;   --radius-md: 8px;   --radius-lg: 16px;
--shadow-sm: 0 1px 2px rgba(20, 32, 43, 0.06);
--shadow-md: 0 4px 12px rgba(20, 32, 43, 0.08);
--shadow-lg: 0 12px 32px rgba(20, 32, 43, 0.12);
```

## Composants de base

- **Boutons** : primaire (fond `--color-primary`, texte blanc), secondaire
  (contour `--color-primary`, fond transparent), accent réservé aux CTA de
  préinscription (fond `--color-accent`, texte `--color-ink`). Rayon
  `--radius-md`, padding vertical généreux (min 44px de hauteur pour le
  tactile).
- **Cartes** (formations, actualités) : fond `--color-surface`, ombre
  `--shadow-sm`, rayon `--radius-lg`.
- **Champs de formulaire** (préinscription, connexion) : bordure
  `--color-border`, focus `--color-primary`, messages d'erreur en
  `--color-error` avec texte explicite (pas seulement une couleur).
- **Badges** (statut de candidature, mention) : petites pastilles arrondies,
  couleur sémantique (succès/attente/erreur).

## Motion (GSAP)

- Durées : `--motion-fast: 0.2s`, `--motion-base: 0.4s`, `--motion-slow: 0.8s`.
- Easing par défaut : `power2.out` pour les apparitions, `power3.inOut`
  pour les transitions de section/pin.
- Tout effet scroll lourd (pin, parallaxe 3D, SplitText) est déclaré via
  `ScrollTrigger.matchMedia()` avec un breakpoint mobile qui **désactive**
  ou **simplifie drastiquement** l'effet (voir docs/06-storyboard.md).
- `prefers-reduced-motion: reduce` → toutes les animations décoratives
  sont neutralisées (opacité/position finales appliquées directement, pas
  de transition).

## Application

- **WordPress/Elementor** : les tokens ci-dessus sont déclarés comme
  variables globales Elementor (Site Settings → Global Colors/Fonts) pour
  que toute la vitrine hérite du même système.
- **Next.js (`app-etudiant`)** : le projet utilise Tailwind CSS v4
  (config CSS-first, pas de `tailwind.config.ts`). Les mêmes tokens sont
  déclarés comme variables dans `app/globals.css` (`:root` puis exposées
  via `@theme inline`), pour que l'espace étudiant reste visuellement
  cohérent avec la vitrine sans dupliquer de valeurs magiques.
