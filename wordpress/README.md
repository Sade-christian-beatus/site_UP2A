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
   | Primary | `#082E6C` |
   | Primary Dark | `#03122A` |
   | Accent | `#FE931A` |
   | Ink | `#0F1B2E` |
   | Background | `#FFFFFF` |

## Étape 3 bis — Activer l'en-tête et la home (onepage)

`up2a-core` fournit désormais, en plus de la couche d'animation :

- **L'en-tête du site** (bandeau + logo + menu + bouton "Espace étudiant"),
  affiché automatiquement sur toutes les pages dès que le plugin est
  actif — rien à faire de plus. Le menu ("Accueil", "Formations",
  "Admissions"...) se configure dans **Apparence → Menus** : créer un
  menu, y ajouter les pages/liens souhaités, puis lui assigner
  l'emplacement **"Menu principal UP-2A"**.
- **Un modèle de page "Accueil UP-2A (onepage)"**, dans cet ordre : Hero
  (slider de photos de campus + **compte à rebours de la rentrée**),
  "Pourquoi choisir l'UP-2A" (collage photo + checklist), les Valeurs, les
  Formations (cartes façon "fiche" qui **ouvrent une modale** de détail au
  clic), une **Galerie photo** (grille + lightbox, 7 photos), "Comment
  candidater" (grande carte pour la 1ère étape + 3 petites cartes), la
  section Contact (coordonnées + **carte Google Maps intégrée**) et un
  pied de page multi-colonnes (voir docs/06-storyboard.md). Chaque section
  a un léger décor de fond (formes discrètes aux couleurs de la marque,
  avec un effet de parallaxe au scroll sur desktop). Pour l'activer :
  1. **Pages → Ajouter** (ou éditer la page existante prévue comme
     accueil).
  2. Dans **Attributs de page** (colonne de droite) → **Modèle**,
     choisir **"Accueil UP-2A (onepage)"**.
  3. Publier la page.
  4. **Réglages → Lecture** → "Une page statique" → sélectionner cette
     page comme **Page d'accueil**.
- Sections volontairement absentes pour l'instant : "chiffres clés" (pas
  de chiffres validés par le client) et "actualités" (pas encore
  d'articles) — voir docs/06-storyboard.md. La section "Télécharger nos
  documents" (brochure) et le bandeau "CTA final" existent aussi dans le
  code mais ne sont plus dans le parcours de la page actuelle (structure
  demandée par le client le 2026-09-22) — voir "Documents (brochure)"
  ci-dessous pour les réactiver.

### Compte à rebours de la rentrée

Le Hero affiche un compte à rebours vers la date de rentrée (5 octobre
par défaut, année courante ou suivante si déjà passée). Pour fixer une
date précise, ajouter dans `wp-config.php` :

```php
define('UP2A_RENTREE_DATE', '2026-10-05 08:00:00');
```

### Images

Le Hero est un **slider** de 2 photos de campus fournies par le client
(`assets/img/hero-slide-1.webp` / `hero-slide-2.webp`, + versions
`-mobile` allégées), avec rotation automatique (~6,5s) et navigation par
points. Pour remplacer ou ajouter des diapositives sans toucher au code,
utiliser le filtre `up2a_core_hero_slides` depuis un mu-plugin, ou
demander une mise à jour du plugin.

La section "Pourquoi choisir l'UP-2A" affiche par défaut une photo fournie
par le client (`assets/img/pourquoi-photo.webp`). Pour la remplacer, ajouter
dans `wp-config.php` :

```php
define('UP2A_LIFE_IMAGE_URL', 'https://.../vie-etudiante.webp');
```

(remplacer l'URL par celle du média une fois téléversé dans **Médias** ;
sans cette constante, la photo par défaut reste utilisée — plus jamais
l'illustration vectorielle, gardée uniquement comme filet de sécurité si
le fichier venait à manquer).

La **Galerie** affiche par défaut 7 photos (campus + vie étudiante,
fournies par le client). Pour en ajouter d'autres sans toucher au code,
utiliser le filtre `up2a_core_gallery_images` depuis un mu-plugin (même
logique que `up2a_core_hero_slides` ci-dessus), ou demander une mise à
jour du plugin.

### Documents (brochure) — actuellement hors du parcours de la page

La section **"Télécharger nos documents"** existe dans le code
(`up2a_core_render_documents()`) mais n'est plus appelée dans
`templates/front-page-onepage.php` (structure demandée par le client,
2026-09-22). Pour la réintégrer, ajouter
`up2a_core_render_documents();` dans ce fichier, à l'endroit souhaité.
Aucun PDF n'a encore été fourni : elle affiche honnêtement "Brochure
disponible prochainement" plutôt qu'un lien mort. Dès qu'une brochure
PDF existe, la brancher via `wp-config.php` :

```php
define('UP2A_BROCHURE_URL', 'https://.../brochure-up2a.pdf');
```

(remplacer l'URL par celle du média une fois téléversé dans **Médias**).

### Localisation

La barre supérieure, la section Contact et le pied de page affichent
l'adresse (Ouagadougou - Balkuy) comme un **lien cliquable** qui ouvre
Google Maps (recherche par adresse — aucune coordonnée GPS précise n'a
été fournie par le client à ce jour), et la section Contact intègre en
plus une **carte Google Maps intégrée** (même logique de recherche par
adresse, `up2a_core_header_maps_embed_url()`). Pour utiliser des
coordonnées GPS exactes une fois connues, ajouter dans `wp-config.php` :

```php
add_filter('up2a_core_header_maps_url', function () {
    return 'https://www.google.com/maps?q=12.3456,-1.5678';
});
```

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
| `up2a-core` | Animation GSAP/Lenis, tokens design system, en-tête du site, modèle de page "Accueil (onepage)" | Prêt (`up2a-core.zip` fourni) |
| `up2a-preinscription` | Formulaire préinscription → Supabase, sans paiement | Squelette (`up2a-preinscription.zip` fourni), logique complète en phase 5 |

## Dépannage — "rien ne s'affiche comme voulu"

Dans l'ordre le plus probable :

1. **Le modèle de page n'est pas assigné.** L'en-tête (bandeau + logo +
   menu) s'affiche automatiquement dès que `up2a-core` est actif, mais le
   contenu de la home (Hero, formations, etc.) n'apparaît **que sur la
   page à laquelle vous avez assigné le modèle "Accueil UP-2A (onepage)"**
   (Pages → [votre page] → Attributs de page → Modèle). Une page sans ce
   modèle reste une page WordPress vide/normale.
2. **Cette page n'est pas définie comme page d'accueil.** Même avec le bon
   modèle assigné, si **Réglages → Lecture** n'est pas réglé sur "Une page
   statique" pointant vers cette page, vous verrez le flux d'articles par
   défaut de WordPress à la place.
3. **Le zip installé n'est pas la dernière version.** `up2a-core` a été
   mis à jour plusieurs fois (slider, cartes flip, nouvelle photo...).
   Vérifiez la version dans **Extensions → Extensions installées** —
   comparez avec `Version:` en tête de
   `wordpress/plugins/up2a-core/up2a-core.php` dans ce dépôt. Si elle ne
   correspond pas, téléversez le dernier `.zip` fourni (WordPress propose
   de remplacer l'existant).
4. **Le thème actif n'est pas Hello Elementor** (ou un thème qui n'appelle
   pas `wp_body_open()`). Sans cet appel, l'en-tête de `up2a-core` ne peut
   pas s'afficher — vérifiez **Apparence → Thèmes**.
5. **Erreur PHP silencieuse.** Activez temporairement `WP_DEBUG` dans
   `wp-config.php` (`define('WP_DEBUG', true);`) et rechargez la page pour
   voir si un message d'erreur apparaît ; désactivez-le ensuite.
6. **Cache (souvent la cause quand desktop est à jour mais pas mobile).**
   Le CSS/JS de `up2a-core` est chargé avec un numéro de version
   (`?ver=0.5.0` par exemple) qui force normalement le navigateur à
   recharger le fichier après une mise à jour du plugin — mais un cache
   intermédiaire peut ignorer ce paramètre. À vérifier dans l'ordre :
   1. **Cache du navigateur mobile** : rechargement forcé (souvent
      indisponible sur mobile) ou plus simple, ouvrir le site en
      navigation privée sur le téléphone — si l'affichage y est correct,
      c'est bien un cache local à vider (Réglages du navigateur → Effacer
      les données de navigation).
   2. **Plugin de cache WordPress** (WP Super Cache, LiteSpeed Cache,
      W3 Total Cache...) : certains gardent un **cache mobile séparé** du
      cache desktop, qu'une purge "normale" ne vide pas toujours —
      chercher une option "Mobile cache" / "Cache séparé mobile" dans ses
      réglages et le vider explicitement.
   3. **Cache de l'hébergeur ou d'un CDN** (Cloudflare, cache serveur
      mutualisé...) : purger le cache depuis le tableau de bord de
      l'hébergeur si disponible, ou attendre l'expiration du cache (souvent
      quelques heures) si aucune option de purge n'est accessible.
   4. Pour confirmer que la version installée est bien la dernière :
      afficher le code source de la page (menu du navigateur → Code
      source) et chercher `up2a-front-page.css?ver=` — le numéro doit
      correspondre au `Version:` de `up2a-core.php` dans ce dépôt.

Si le problème persiste après ces vérifications, une capture d'écran de
ce que vous voyez (et de vos réglages Pages/Lecture) permettrait un
diagnostic précis.

## Checklist de vérification

- [ ] Sauvegarde de l'ancien site effectuée et téléchargée
- [ ] Ancien contenu (articles, pages, médias, plugins, thèmes) supprimé
- [ ] Hello Elementor + Elementor installés et activés
- [ ] `up2a-core` installé et activé (dernière version), en-tête visible sur le site
- [ ] Menu configuré (Apparence → Menus → emplacement "Menu principal UP-2A")
- [ ] Page créée, modèle "Accueil UP-2A (onepage)" assigné (Attributs de page)
- [ ] Cette page définie comme page d'accueil (Réglages → Lecture → "Une page statique")
- [ ] `up2a-preinscription` installé et activé (avertissement admin normal)
- [ ] Couleurs globales Elementor renseignées
