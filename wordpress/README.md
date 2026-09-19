# WordPress — installation & wipe

> ⚠️ Les étapes de wipe (section 2) sont destructives et irréversibles
> sans sauvegarde préalable. Ne les exécuter qu'après la sauvegarde de
> l'étape 1, et seulement quand vous êtes certain(e) de vouloir procéder
> (voir CLAUDE.md §3 et §9, docs/00-brief.md contrainte absolue #5).

> **Pourquoi ce document est à exécuter par vous-même** : l'environnement
> Claude Code qui a préparé ce scaffold n'a pas d'accès réseau sortant
> vers `bdo-burkina.com` (politique réseau de l'environnement — voir
> https://code.claude.com/docs/en/claude-code-on-the-web). Toutes les
> étapes ci-dessous se font donc à la main dans wp-admin ; aucun accès
> SFTP/SSH n'est nécessaire, les plugins maison sont fournis en `.zip`
> prêts à téléverser.

## Étape 0 — Sauvegarde (avant tout le reste)

1. Dans wp-admin actuel (si encore accessible) : installer temporairement
   un plugin de sauvegarde (ex. **All-in-One WP Migration** ou
   **UpdraftPlus**) et exporter site complet (fichiers + base).
   Alternative : demander à l'hébergeur un snapshot/sauvegarde du compte.
2. Conserver cette sauvegarde en dehors de l'hébergement (téléchargement
   local), même si le contenu doit être supprimé — au cas où une info
   (réglages e-mail, DNS, etc.) doive être récupérée plus tard.

## Étape 1 — Wipe du contenu existant

⚠️ Ne cocher/exécuter cette étape que si la sauvegarde de l'étape 0 est
faite et que vous êtes certain(e) de vouloir tout supprimer.

Dans wp-admin :

1. **Réglages → Général** : noter l'URL du site, le titre, l'e-mail admin
   (pour les remettre à l'identique après).
2. **Extensions → Extensions installées** : désactiver toutes les
   extensions actuelles, puis les supprimer.
3. **Apparence → Thèmes** : installer un thème par défaut WordPress (ex.
   Twenty Twenty-Four) s'il n'y en a pas déjà un, l'activer, puis
   supprimer tous les autres thèmes existants.
4. **Articles → Tous les articles** : sélectionner tout → Actions groupées
   → Mettre à la corbeille → puis vider la corbeille.
5. **Pages → Toutes les pages** : même procédure (corbeille puis vider).
6. **Médias → Bibliothèque** : supprimer tous les fichiers médias
   existants (un par un ou via un plugin de nettoyage type
   **Media Cleaner**, à désinstaller après usage).
7. Vérifier que les menus (**Apparence → Menus**) et widgets obsolètes
   sont également vidés.

Ce nettoyage via wp-admin ne réinitialise pas le cœur de WordPress
lui-même (fichiers `wp-*.php`, préfixe de base) — pour une remise à zéro
totale au niveau serveur, il faudrait un accès hébergeur (hors périmètre
de cette page tant qu'on n'en a pas besoin).

## Étape 2 — Installation propre

1. **Apparence → Thèmes → Ajouter** : rechercher **Hello Elementor** →
   Installer → Activer.
2. **Extensions → Ajouter → rechercher "Elementor"** → Installer →
   Activer. (Elementor Pro seulement si une licence est disponible —
   sinon rester sur la version gratuite et adapter le storyboard,
   docs/06, à ses limites.)
3. **Extensions → Ajouter → Téléverger une extension** : sélectionner
   `up2a-core.zip` (fourni) → Installer maintenant → Activer.
4. **Extensions → Ajouter → Téléverger une extension** : sélectionner
   `up2a-preinscription.zip` (fourni) → Installer maintenant → Activer.
   Un avertissement admin apparaît ("constantes Supabase non définies") —
   normal à ce stade, sans effet bloquant. Ce plugin sera construit en
   phase 5 (docs/03-roadmap.md) ; son activation maintenant sert juste à
   valider qu'il s'installe proprement.

## Étape 3 — Vérifier le design system

1. Visiter le site en front (une page quelconque, même vide).
2. Ouvrir les outils de développement du navigateur → Éléments → sur
   `<html>` ou `<body>`, vérifier la présence des variables CSS
   `--up2a-color-primary`, `--up2a-color-accent`, etc. (injectées par
   `up2a-core`, voir `wordpress/plugins/up2a-core/assets/css/up2a-tokens.css`).
3. **Elementor → Réglages du site → Couleurs globales** : saisir
   manuellement les couleurs de docs/02-design-system.md (Elementor ne
   lit pas les variables CSS dans son color picker) :

   | Nom | Valeur |
   |---|---|
   | Primary | `#0B4D3C` |
   | Primary Dark | `#083A2D` |
   | Accent | `#C9A227` |
   | Ink | `#14202B` |
   | Background | `#F7F5F0` |

## Étape 4 — Constantes Supabase (à faire en phase 5, pas maintenant)

Quand `up2a-preinscription` sera construit (phase 5), il faudra ajouter
dans `wp-config.php` :

```php
define('UP2A_SUPABASE_URL', 'https://xxxxxxxxxxxx.supabase.co');
define('UP2A_SUPABASE_SERVICE_ROLE_KEY', '...'); // secret serveur uniquement
```

Si vous n'avez pas d'accès SFTP/SSH pour éditer `wp-config.php`
directement, deux options : demander l'accès au support de votre
hébergeur, ou installer un plugin d'édition de `wp-config.php` depuis
wp-admin (ex. **WP Config File Editor**) — à désinstaller après usage
pour ne pas laisser cette capacité ouverte en permanence.

## Statut des plugins maison

| Plugin | Rôle | Statut |
|---|---|---|
| `up2a-core` | GSAP/ScrollTrigger/SplitText/Lenis + tokens design system | Prêt (`up2a-core.zip` fourni) |
| `up2a-preinscription` | Formulaire préinscription → Supabase, sans paiement | Squelette (`up2a-preinscription.zip` fourni), logique complète en phase 5 |

## Checklist de vérification

- [ ] Sauvegarde de l'ancien site effectuée et téléchargée
- [ ] Ancien contenu (articles, pages, médias, plugins, thèmes) supprimé
- [ ] Hello Elementor + Elementor installés et activés
- [ ] `up2a-core` installé et activé, variables CSS visibles en front
- [ ] `up2a-preinscription` installé et activé (avertissement admin normal)
- [ ] Couleurs globales Elementor renseignées
