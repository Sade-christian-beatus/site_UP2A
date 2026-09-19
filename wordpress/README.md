# WordPress — installation & wipe

> ⚠️ Rien dans ce document ne doit être exécuté sur le site live
> (https://bdo-burkina.com) sans **feu vert explicite** du client (voir
> CLAUDE.md §3 et §9). Les étapes de wipe sont destructives et
> irréversibles sans sauvegarde préalable.

## Prérequis avant toute installation

1. **Sauvegarde complète** du site actuel (fichiers + base de données),
   même s'il est destiné à être supprimé — au cas où une information (DNS,
   config e-mail, etc.) doive être récupérée.
2. Accès hébergement (SFTP/SSH ou panneau d'hébergement) et accès
   wp-admin.
3. Confirmation explicite du client pour procéder au wipe (voir
   docs/00-brief.md, contrainte absolue #5).

## Étapes d'installation propre

1. **Wipe** : supprimer le contenu WordPress existant (thème, plugins,
   contenu de la base) — uniquement après le feu vert du point ci-dessus.
2. Installer WordPress à jour sur le domaine.
3. Installer et activer le thème **Hello Elementor**.
4. Installer et activer **Elementor** (+ Elementor Pro si une licence est
   disponible — sinon adapter le storyboard aux capacités de la version
   gratuite).
5. Installer et activer les plugins maison :
   - `wordpress/plugins/up2a-core` — couche d'animation (GSAP + Lenis) et
     variables du design system.
   - `wordpress/plugins/up2a-preinscription` — formulaire de
     préinscription → Supabase (sans paiement). *Squelette pour l'instant
     — construction complète en phase 5 de la roadmap.*
6. Configurer les constantes Supabase dans `wp-config.php` (jamais dans un
   fichier versionné) :

   ```php
   define('UP2A_SUPABASE_URL', 'https://xxxxxxxxxxxx.supabase.co');
   define('UP2A_SUPABASE_SERVICE_ROLE_KEY', '...'); // secret serveur uniquement
   ```

7. Appliquer le design system (docs/02) : `up2a-core` injecte déjà les
   variables CSS de base (voir son code) ; compléter dans Site Settings →
   Global Colors/Fonts d'Elementor pour que les widgets Elementor héritent
   des mêmes tokens.

## Plugins requis (résumé)

| Plugin | Rôle | Statut |
|---|---|---|
| Hello Elementor (thème) | Base minimale, sans style imposé | À installer |
| Elementor | Constructeur de pages | À installer |
| `up2a-core` | GSAP/ScrollTrigger/SplitText/Lenis + tokens design system | Scaffold prêt (ce dépôt) |
| `up2a-preinscription` | Formulaire préinscription → Supabase | Squelette (ce dépôt), à construire phase 5 |

## Ce qui reste à faire avant de pouvoir exécuter cette page

- Accès hébergement/domaine (SFTP/SSH ou panneau, + wp-admin).
- Confirmation explicite du client pour le wipe.
- Licence Elementor Pro (si utilisée) ou confirmation de rester sur la
  version gratuite.
