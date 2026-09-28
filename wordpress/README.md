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
   `up2a-core.zip` (fourni) → Installer maintenant → Activer. **À activer
   en premier** : tous les autres plugins `up2a-*` en dépendent (déclaré
   dans leur en-tête `Requires Plugins`) et WordPress refuse de les
   activer si `up2a-core` ne l'est pas déjà.
4. **Extensions → Ajouter → Téléverger une extension** : répéter avec
   `up2a-header.zip`, `up2a-footer.zip`, `up2a-slider.zip`,
   `up2a-actualites.zip`, `up2a-formations.zip`, puis `up2a-galerie.zip`
   (fournis) — En-tête, Pied de page, Hero, Actualités, Formations et
   Galerie sont chacun un plugin séparé avec son propre écran de
   réglages dans le tableau de bord (voir "Statut des plugins maison"
   ci-dessous et docs/06-storyboard.md). L'ordre entre eux n'a pas
   d'importance, seul `up2a-core` doit être actif avant.
5. **Extensions → Ajouter → Téléverger une extension** : sélectionner
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

## Étape 3 bis — Activer l'en-tête, le pied de page et la home (onepage)

Depuis le 2026-09-28, l'en-tête, le pied de page, le Hero et les
actualités sont chacun un plugin séparé (voir "Statut des plugins
maison" plus bas) — **tout leur contenu se règle depuis wp-admin, aucune
constante `wp-config.php` à toucher** :

- **`up2a-header`** (menu wp-admin **"En-tête"**) : logo, téléphone,
  localisation courte, adresse complète, texte du bandeau défilant, URL
  de l'espace étudiant. S'affiche automatiquement sur **toutes les
  pages** dès que le plugin est actif. Le menu de navigation
  ("Accueil", "Formations", "Admissions"...) se configure séparément
  dans **Apparence → Menus** : créer un menu, y ajouter les pages/liens
  souhaités, puis lui assigner l'emplacement **"Menu principal UP-2A"**.
- **`up2a-footer`** (menu wp-admin **"Pied de page"**) : logo, slogan,
  liens (ajouter/supprimer/réordonner), texte de copyright. S'affiche
  également sur **toutes les pages**.
- **Un modèle de page "Accueil UP-2A (onepage)"** (fourni par
  `up2a-core`), dans cet ordre : Hero (fourni par `up2a-slider`,
  shortcode `[up2a_slider]`, menu wp-admin "Slider" — slider de photos +
  **compte à rebours de la rentrée**), "Pourquoi choisir l'UP-2A"
  (collage photo + checklist), les Valeurs, les Formations (cartes façon
  "fiche" qui **ouvrent une modale** de détail au clic — fournies par
  `up2a-formations`, shortcode `[up2a_formations]`, menu "Formations"),
  une **Galerie photo** (grille + lightbox — fournie par `up2a-galerie`,
  shortcode `[up2a_galerie]`, menu "Galerie"), les **Actualités** (fourni
  par `up2a-actualites`, shortcode `[up2a_actualites]`, menu
  "Actualités" — absent tant qu'aucune actualité n'est ajoutée),
  "Comment candidater" (grande carte pour la 1ère étape + 3 petites
  cartes), la section Contact (coordonnées + **carte Google Maps
  intégrée**). Chaque section a un léger décor de fond (formes discrètes
  aux couleurs de la marque, avec un effet de parallaxe au scroll sur
  desktop). Si l'un de ces plugins n'est pas actif, la section
  correspondante est simplement absente de la page plutôt que de casser
  le reste — jamais bloquant. Pour activer cette page d'accueil :
  1. **Pages → Ajouter** (ou éditer la page existante prévue comme
     accueil).
  2. Dans **Attributs de page** (colonne de droite) → **Modèle**,
     choisir **"Accueil UP-2A (onepage)"**.
  3. Publier la page.
  4. **Réglages → Lecture** → "Une page statique" → sélectionner cette
     page comme **Page d'accueil**.
- Section volontairement absente pour l'instant : "chiffres clés" (pas
  de chiffres validés par le client) — voir docs/06-storyboard.md. La
  section "Télécharger nos documents" (brochure) et le bandeau "CTA
  final" existent aussi dans le code mais ne sont plus dans le parcours
  de la page actuelle (structure demandée par le client le 2026-09-22) —
  voir "Documents (brochure)" ci-dessous pour les réactiver.

### Compte à rebours de la rentrée

Le Hero affiche un compte à rebours vers la date de rentrée. Elle se
règle directement sur l'écran de réglages **"Slider"** (champ "Date de
rentrée") — vide par défaut (5 octobre de l'année courante ou suivante
si déjà passée), pas besoin de toucher à `wp-config.php`.

### Images

Le Hero est un **slider** de photos de campus, réglable depuis l'écran
**"Slider"** (menu wp-admin) : ajouter/supprimer/réordonner une
diapositive, choisir chaque photo dans la médiathèque. Les 2 photos
fournies par le client sont préremplies automatiquement à la première
activation du plugin (rien à ressaisir).

La section "Pourquoi choisir l'UP-2A" affiche par défaut une photo fournie
par le client (`assets/img/pourquoi-photo.webp`). Pour la remplacer, ajouter
dans `wp-config.php` :

```php
define('UP2A_LIFE_IMAGE_URL', 'https://.../vie-etudiante.webp');
```

(remplacer l'URL par celle du média une fois téléversé dans **Médias** ;
sans cette constante, la photo par défaut reste utilisée — plus jamais
l'illustration vectorielle, gardée uniquement comme filet de sécurité si
le fichier venait à manquer). Ce réglage reste dans `up2a-core` (pas
d'écran wp-admin dédié pour cette photo précise, contrairement aux
plugins scindés) — si le besoin de le rendre éditable depuis wp-admin se
confirme, on l'ajoutera à l'écran "SEO" ou à un nouvel écran "Contenus"
générique.

### Formations, Galerie et Actualités — réglages depuis le tableau de bord

Chacune de ces trois sections a son propre écran de réglages dans
wp-admin (pas une liste d'articles à ouvrir un par un — un seul écran
par module, tout le contenu s'y modifie et s'enregistre en une fois) :

- **Formations** (plugin `up2a-formations`, menu **Formations**) : une
  carte par formation avec nom, slug (URL `/formations/...`, généré
  automatiquement depuis le nom si laissé vide), faculté, icône,
  description courte, débouchés, programme, et une photo. Boutons
  "↑ Monter"/"↓ Descendre" pour réordonner, "+ Ajouter une formation".
- **Galerie** (plugin `up2a-galerie`, menu **Galerie**) : une carte par
  photo (légende + photo). La première carte de la liste devient la
  vignette "en avant" avec défilement automatique.
- **Actualités** (plugin `up2a-actualites`, menu **Actualités**) : une
  carte par actualité (titre, date, extrait, image, lien "En savoir
  plus" optionnel). **Aucune actualité n'est préremplie** — la section
  reste absente de la home tant qu'aucune n'a été ajoutée ici (voir
  docs/06-storyboard.md, "jamais de contenu inventé").

Pour chacune : **rien n'est enregistré tant que vous n'avez pas cliqué
sur "Enregistrer les modifications"** en bas de la page. Formations et
Galerie sont préremplies automatiquement à la première activation
(photos d'origine incluses) ; Actualités démarre vide (voir ci-dessus).
Chaque section s'affiche via son shortcode
(`[up2a_formations]`/`[up2a_galerie]`/`[up2a_actualites]`), affiché par
défaut sur la home onepage, mais réutilisable sur n'importe quelle autre
page/widget Elementor.

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
adresse, `up2a_header_maps_embed_url()`). L'adresse utilisée pour ces
deux liens vient du champ **"Adresse complète"** de l'écran de réglages
**"En-tête"** (wp-admin) — la modifier là suffit à mettre à jour les
deux à la fois, sans toucher au code. Il n'existe pas encore de champ
dédié à des coordonnées GPS précises (lat/long) : si le client en fournit
un jour, cela demandera une petite mise à jour du plugin `up2a-header`
pour ajouter ce champ plutôt qu'une simple modification de contenu.

## Étape 4 — Constantes Supabase (requis pour la préinscription)

`up2a-preinscription` est maintenant construit (formulaire complet,
voir plus bas). Pour qu'il puisse écrire dans Supabase, ajoutez dans
`wp-config.php` (avant la ligne `/* That's all, stop editing! */`) :

```php
define('UP2A_SUPABASE_URL', 'https://xxxxxxxxxxxx.supabase.co');
define('UP2A_SUPABASE_SERVICE_ROLE_KEY', '...'); // secret serveur uniquement
```

Ces deux valeurs se trouvent dans le tableau de bord Supabase du projet
UP-2A → **Project Settings → API** (`URL` et `service_role` — jamais la
clé `anon`). **Ne jamais** coller ces valeurs ailleurs que dans
`wp-config.php` sur le serveur (jamais dans un fichier du dépôt, jamais
dans une page/un article WordPress).

Si vous n'avez pas d'accès SFTP/SSH pour éditer `wp-config.php`
directement, deux options : demander l'accès au support de votre
hébergeur, ou installer un plugin d'édition de `wp-config.php` depuis
wp-admin (ex. **WP Config File Editor**) — à désinstaller après usage
pour ne pas laisser cette capacité ouverte en permanence.

Tant que ces constantes ne sont pas définies, `up2a-preinscription`
affiche un avertissement dans wp-admin (**Extensions**) et toute
soumission du formulaire échoue proprement côté serveur (message
d'erreur générique au visiteur, détail technique dans les logs PHP —
jamais de clé exposée).

## Étape 4 bis — Lien "Espace étudiant"

Le bouton "ESPACE ÉTUDIANT" du header pointe par défaut vers une ancre
neutre (`#up2a-espace-etudiant`) tant que son URL n'a pas été renseignée.
Depuis le 2026-09-28, ce réglage se fait directement dans wp-admin —
menu **"En-tête"** → champ **"URL de l'espace étudiant"** (ex.
`https://espace.bdo-burkina.com/`) — plus besoin de la constante
`wp-config.php` `UP2A_ESPACE_ETUDIANT_URL` (encore lue comme valeur de
repli la première fois que l'écran de réglages s'initialise, si elle est
déjà définie, mais le champ wp-admin est désormais la source de vérité).

## Étape 5 — Page de préinscription

1. **Pages → Ajouter** une nouvelle page, titre libre (ex. "Préinscription").
2. Dans le contenu, ajoutez le shortcode : `[up2a_preinscription]`
   (bloc "Shortcode" dans l'éditeur, ou widget Elementor "Shortcode" si
   vous préférez composer le reste de la page avec Elementor autour).
3. **Réglages → Permaliens** : vérifiez que l'URL de cette page est bien
   `/preinscription/` — c'est l'adresse par défaut vers laquelle
   pointent tous les boutons "Faire ma préinscription" / "S'inscrire
   maintenant" de la home. **Si l'URL affiche `/index.php/preinscription/`
   au lieu de `/preinscription/`** (page introuvable sur l'adresse
   propre), les règles de réécriture d'URL de WordPress ne sont pas à
   jour côté serveur : allez dans **Réglages → Permaliens** et cliquez
   sur **Enregistrer les modifications** (sans rien changer) — ça force
   WordPress à régénérer ces règles. Si le problème revient après un
   redémarrage du serveur, contactez votre hébergeur : `mod_rewrite`
   (Apache) doit être activé et le `.htaccess` accessible en écriture.
   Si vous préférez un autre slug, publiez la page puis ajustez dans
   `wp-config.php` :
   ```php
   add_filter('up2a_core_preinscription_url', function () {
       return home_url('/votre-slug/');
   });
   ```
4. Publiez. Le formulaire (4 étapes : informations, formation, pièces
   jointes, envoi) s'affiche automatiquement avec le design du site.
5. Testez une soumission complète (avec de vrais petits fichiers image/PDF)
   et vérifiez dans le tableau Supabase **candidatures** qu'une nouvelle
   ligne apparaît, et que l'e-mail de confirmation arrive bien (vérifiez
   aussi les spams — dépend de la configuration SMTP de l'hébergeur, un
   plugin SMTP comme **WP Mail SMTP** est recommandé pour la délivrabilité
   en production).

   > Cet e-mail de confirmation de candidature (envoyé par WordPress via
   > `wp_mail`) est indépendant du SMTP de Supabase Auth : ce dernier a
   > été **abandonné définitivement** (2026-09-28) — l'étudiant se
   > connecte désormais avec le **matricule** qui lui est communiqué à
   > son admission, jamais avec un e-mail. Voir
   > `app-etudiant/README.md` "Connexion par matricule" et
   > `back-office/README.md` "Candidatures".

## Étape 6 — SEO et pages légales

1. **SEO** : menu wp-admin **"SEO"** (fourni par `up2a-core`) — renseigner
   la description par défaut et l'image de partage par défaut (utilisées
   par Google et par les partages Facebook/WhatsApp/X quand une page n'a
   pas son propre extrait/image mise en avant). Rien d'autre à faire :
   les balises meta/Open Graph/canonique s'injectent automatiquement, et
   WordPress fournit déjà nativement un sitemap XML
   (`/wp-sitemap.xml`) et un robots.txt virtuel — à vérifier une fois le
   site en ligne (ouvrir ces deux URLs dans un navigateur). Si un plugin
   SEO dédié (Yoast, RankMath...) est installé plus tard, cet écran se
   désactive automatiquement pour éviter les balises en double.
2. **Pages légales** : le contenu (texte complet, prêt à coller) est dans
   `docs/07-pages-legales.md` — deux pages à créer (**Pages → Ajouter**) :
   "Mentions légales" et "Politique de confidentialité". Certaines
   informations y sont marquées `[À CONFIRMER]` (numéro RCCM/IFU, nom
   légal de l'association gestionnaire, hébergeur, e-mail de contact...) :
   à faire valider/compléter par le client avant publication — ne jamais
   publier ces placeholders tels quels. Une fois publiées, ajouter ces
   deux pages au menu du pied de page (menu wp-admin **"Pied de page"**,
   fourni par `up2a-footer`).

## Statut des plugins maison

| Plugin | Rôle | Statut |
|---|---|---|
| `up2a-core` | Animation GSAP/Lenis (self-hostées, `assets/js/vendor/`), tokens design system, décor de section, modèle de page "Accueil (onepage)", SEO (méta/OG/canonique) | Prêt (`up2a-core.zip` fourni) — **à activer en premier** |
| `up2a-header` | Module "En-tête" (écran de réglages, logo/téléphone/adresse/bandeau/lien espace étudiant, shortcode `[up2a_header]`) — scindé de `up2a-core` le 2026-09-28, sitewide | Prêt (`up2a-header.zip` fourni) — nécessite `up2a-core` actif |
| `up2a-footer` | Module "Pied de page" (écran de réglages, logo/slogan/liens/copyright, shortcode `[up2a_footer]`) — scindé de `up2a-core` le 2026-09-28, sitewide | Prêt (`up2a-footer.zip` fourni) — nécessite `up2a-core` actif |
| `up2a-slider` | Module "Slider" (écran de réglages, diapositives Hero + date de rentrée, shortcode `[up2a_slider]`) — scindé de `up2a-core` le 2026-09-28, home uniquement | Prêt (`up2a-slider.zip` fourni) — nécessite `up2a-core` actif |
| `up2a-actualites` | Module "Actualités" (écran de réglages, shortcode `[up2a_actualites]`) — nouveau le 2026-09-28, home uniquement, sans amorçage automatique | Prêt (`up2a-actualites.zip` fourni) — nécessite `up2a-core` actif |
| `up2a-formations` | Module "Formations" (écran de réglages, cartes + modale + pages détail `/formations/{slug}/`, shortcode `[up2a_formations]`) — scindé de `up2a-core` le 2026-09-26, contenu géré depuis le tableau de bord depuis la v1.2.0 | Prêt (`up2a-formations.zip` fourni) — nécessite `up2a-core` actif |
| `up2a-galerie` | Module "Galerie" (écran de réglages, grille + lightbox photo, shortcode `[up2a_galerie]`) — scindé de `up2a-core` le 2026-09-26, contenu géré depuis le tableau de bord depuis la v1.2.0 | Prêt (`up2a-galerie.zip` fourni) — nécessite `up2a-core` actif |
| `up2a-preinscription` | Formulaire préinscription (4 étapes) → Supabase, sans paiement | Prêt (`up2a-preinscription.zip` fourni) — nécessite les constantes Supabase (Étape 4) et une page avec le shortcode (Étape 5) |

`up2a-header`, `up2a-footer`, `up2a-slider`, `up2a-actualites`,
`up2a-formations` et `up2a-galerie` sont des plugins **séparés** de
`up2a-core` : chacun gère son propre contenu via son écran de réglages,
affiché via un shortcode dédié plutôt qu'un appel de code — modifiable
entièrement depuis wp-admin, sans toucher au reste du site. Tous
dépendent de `up2a-core` (icônes, décor, styles/utilitaires partagés,
URL de préinscription) — WordPress refuse de les activer si `up2a-core`
ne l'est pas déjà (en-tête `Requires Plugins`). Deux dépendances
croisées, toutes deux protégées par `function_exists()`/
`shortcode_exists()` pour ne jamais bloquer si le plugin correspondant
est inactif : la modale de détail d'une formation affiche des vignettes
tirées de la Galerie si `up2a-galerie` est actif, et la section Contact
(`up2a-core`) affiche le téléphone/l'adresse si `up2a-header` est actif.

## Dépannage — "rien ne s'affiche comme voulu"

> **Cause confirmée sur ce projet (2026-09-23)** : les sections
> "Nos formations" et "Galerie" sont restées vides pendant plusieurs
> mises à jour du plugin alors que le code était correct — la cause
> réelle était le **cache CSS "optimisé" d'Elementor**, généré à un
> instant antérieur à l'ajout de ces sections au template. Si une
> section reste vide malgré un plugin à jour : **Elementor → Outils →
> onglet Général → "Régénérer les fichiers CSS et les données"**, puis
> rechargez la page (Ctrl+Maj+R). À essayer **avant** tout le reste
> ci-dessous.

> **Pages formation (`/formations/{slug}/`) en 404 après mise à jour**
> (2026-09-25) : ces pages sont servies par une règle de réécriture
> ajoutée par `up2a-formations`, pas par une vraie page WordPress. Si
> elles ne se chargent pas après avoir remplacé le zip du plugin (même
> symptôme déjà rencontré sur `/preinscription/`, voir plus bas) :
> **Réglages → Permaliens → Enregistrer les modifications**, sans rien
> changer — cela force WordPress à regénérer ses règles. Un filet de
> sécurité dans le code le fait aussi automatiquement à la prochaine
> visite si le numéro de version du plugin a changé, mais un flush manuel
> reste plus rapide si vous voulez tester tout de suite.

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
   pas `wp_body_open()`/`wp_footer()`). Sans ces appels, l'en-tête
   (`up2a-header`) et le pied de page (`up2a-footer`) ne peuvent pas
   s'afficher — vérifiez **Apparence → Thèmes**.
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
7. **Une section précise reste vide alors que tout le reste de la page est
   à jour** (diagnostiqué le 2026-09-22 sur "Nos formations"). Ce n'est
   **pas** un souci de cache (qui affecterait toute la page, pas une
   section isolée). Depuis la v1.2.0 (`up2a-formations`/`up2a-galerie`,
   contenu géré depuis un écran de réglages plutôt qu'un tableau
   statique) : vérifiez d'abord **Formations**/**Galerie** dans wp-admin
   — la section entière ne s'affiche que s'il y a au moins une entrée
   enregistrée avec un nom (Formations) ou une photo (Galerie). Une
   entrée sans photo ne fait, elle, disparaître que sa propre carte (fond
   dégradé sans image pour une formation ; ignorée pour une photo de
   galerie), jamais toute la section. Si des entrées existent bien et ont
   une photo mais que la section reste vide, désactivez temporairement
   les extensions tierces (hors Elementor, Hello Elementor et les
   plugins `up2a-*`) pour écarter un conflit.
8. **Une section (Formations, Galerie, Hero, Actualités) ou l'en-tête/le
   pied de page a disparu depuis une mise à jour récente** (2026-09-26
   pour Formations/Galerie, 2026-09-28 pour En-tête/Pied de page/Hero/
   Actualités) : ces modules sont désormais des plugins séparés
   (`up2a-formations`, `up2a-galerie`, `up2a-header`, `up2a-footer`,
   `up2a-slider`, `up2a-actualites`), plus intégrés à `up2a-core`.
   Vérifiez qu'ils sont bien installés **et activés** (**Extensions →
   Extensions installées**) — sans eux, la section correspondante est
   simplement absente de la page (comportement voulu, pas une erreur),
   voir "Statut des plugins maison" ci-dessus.

Si le problème persiste après ces vérifications, une capture d'écran de
ce que vous voyez (et de vos réglages Pages/Lecture) permettrait un
diagnostic précis.

## Checklist de vérification

- [ ] Sauvegarde de l'ancien site effectuée et téléchargée
- [ ] Ancien contenu (articles, pages, médias, plugins, thèmes) supprimé
- [ ] Hello Elementor + Elementor installés et activés
- [ ] `up2a-core` installé et activé (dernière version) — **à activer en premier**
- [ ] `up2a-header`, `up2a-footer`, `up2a-slider`, `up2a-actualites`, `up2a-formations` et `up2a-galerie` installés et activés (après `up2a-core`) — en-tête et pied de page visibles sur toutes les pages
- [ ] Menus wp-admin "En-tête"/"Pied de page" renseignés (téléphone, adresse, liens...) ; "Formations"/"Galerie" contiennent bien les entrées existantes avec leurs photos (recréées automatiquement à l'activation — vérifier qu'aucune n'affiche un cadre vide) ; "Actualités" démarre vide, normal
- [ ] Menu de navigation configuré (Apparence → Menus → emplacement "Menu principal UP-2A")
- [ ] Page créée, modèle "Accueil UP-2A (onepage)" assigné (Attributs de page)
- [ ] Cette page définie comme page d'accueil (Réglages → Lecture → "Une page statique")
- [ ] `up2a-preinscription` installé et activé
- [ ] Constantes `UP2A_SUPABASE_URL` / `UP2A_SUPABASE_SERVICE_ROLE_KEY` ajoutées dans `wp-config.php` (Étape 4)
- [ ] Page "Préinscription" créée avec le shortcode `[up2a_preinscription]`, publiée à `/preinscription/` (Étape 5)
- [ ] Soumission de test effectuée : ligne visible dans Supabase (table `candidatures`) + e-mail de confirmation reçu
- [ ] Menu "SEO" renseigné (description + image de partage par défaut), sitemap `/wp-sitemap.xml` et `/robots.txt` vérifiés (Étape 6)
- [ ] Pages "Mentions légales" et "Politique de confidentialité" créées à partir de docs/07-pages-legales.md, placeholders `[À CONFIRMER]` remplacés avec le client (Étape 6)
- [ ] Couleurs globales Elementor renseignées
