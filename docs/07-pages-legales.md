# 07 — Pages légales (Mentions légales + Politique de confidentialité)

> **Statut : brouillon de travail**, à valider par le client avant mise en
> ligne — même règle que `docs/05-contenus.md`. Tout ce qui est marqué
> `[À CONFIRMER]` est un placeholder : **ne pas publier tel quel**. Les
> seules données fermes viennent de CLAUDE.md et des réglages déjà en
> production dans `up2a-header` (téléphone, adresse) ; tout le reste
> (numéro RCCM/IFU, nom légal exact de l'association gestionnaire,
> représentant légal, hébergeur, e-mail de contact, éventuels outils de
> mesure d'audience) doit être confirmé par le client — voir CLAUDE.md §3
> : ne jamais inventer une donnée officielle.
>
> **À faire côté WordPress** : créer deux **Pages** (Pages → Ajouter),
> coller le contenu ci-dessous (titre + corps), publier, puis les ajouter
> au menu du pied de page (menu "Pied de page" → réglages `up2a-footer`,
> ou le menu de navigation principal selon la préférence du client). Une
> fois les placeholders confirmés, remplacer directement dans la Page
> WordPress (pas besoin de repasser par ce fichier).

## Mentions légales

> Titre de la page
**Mentions légales**

> Corps de page

### Éditeur du site

Le présent site est édité par :

**Université Privée An-Nahdah d'Afrique (UP-2A)**
Établissement d'enseignement supérieur privé, autorisé par le Ministère
de l'Enseignement Supérieur, de la Recherche et de l'Innovation (MESRI)
du Burkina Faso sous le n° **2026-001647**.

- Forme juridique : [À CONFIRMER — statut associatif ; nom légal exact et
  numéro d'enregistrement de l'association gestionnaire à confirmer par
  le client]
- Siège / adresse : Ouagadougou - Balkuy, Burkina Faso
- Téléphone : +226 50 63 85 54
- E-mail de contact : [À CONFIRMER]
- Numéro RCCM : [À CONFIRMER]
- Numéro IFU : [À CONFIRMER]
- Directeur de la publication / représentant légal : [À CONFIRMER]

### Hébergement

- Hébergement du site web (WordPress) : [À CONFIRMER — nom et adresse de
  l'hébergeur une fois choisi]
- Hébergement des données (Supabase) : Supabase Inc., infrastructure
  cloud (PostgreSQL, authentification, stockage) — voir
  `docs/01-architecture.md` pour le détail technique.

### Conception et développement

Site conçu et développé par **LUPORA Group**.

### Propriété intellectuelle

L'ensemble des contenus présents sur ce site (textes, photographies,
logo, charte graphique) est la propriété de l'Université Privée
An-Nahdah d'Afrique, sauf mention contraire. Toute reproduction, totale
ou partielle, sans autorisation préalable est interdite.

### Limitation de responsabilité

L'UP-2A s'efforce d'assurer l'exactitude et la mise à jour des
informations diffusées sur ce site, sans garantie d'exhaustivité. L'UP-2A
ne pourra être tenue responsable des erreurs ou omissions, ni de
l'indisponibilité temporaire du site.

### Médiation / litiges

En cas de litige relatif à l'utilisation du site, la loi burkinabè est
applicable. [À CONFIRMER — juridiction compétente en cas de litige, si le
client souhaite le préciser.]

---

## Politique de confidentialité

> Titre de la page
**Politique de confidentialité**

> Corps de page

### Qui est responsable de vos données ?

Le responsable du traitement des données collectées sur ce site est
l'**Université Privée An-Nahdah d'Afrique (UP-2A)**, Ouagadougou - Balkuy,
Burkina Faso — contact : [À CONFIRMER — e-mail ou téléphone dédié aux
questions de confidentialité].

### Quelles données sont collectées, et pourquoi

| Situation | Données collectées | Finalité |
|---|---|---|
| Formulaire de préinscription en ligne | Identité, coordonnées, parcours scolaire, pièces justificatives téléversées (voir `docs/01-architecture.md`) | Traiter la candidature et, en cas d'admission, créer le dossier étudiant |
| Espace étudiant (une fois inscrit) | Matricule, mot de passe, emploi du temps, résultats, documents administratifs | Permettre à l'étudiant de consulter ses informations académiques |
| Navigation sur le site vitrine | Données techniques courantes (voir section "Cookies" ci-dessous) | Fonctionnement du site |

**Aucun paiement en ligne n'est demandé lors de la préinscription** — le
site ne collecte donc aucune donnée bancaire ou de carte de paiement
(voir CLAUDE.md — contrainte non négociable du projet).

### Où sont stockées ces données

Les données de préinscription et les données académiques sont stockées
sur **Supabase** (base de données PostgreSQL avec authentification et
règles d'accès strictes — voir `docs/01-architecture.md`), qui est la
source unique de vérité du projet. Le site vitrine WordPress ne conserve
pas de copie de ces données : le formulaire de préinscription les
transmet directement à Supabase.

### Qui peut accéder à vos données

- L'étudiant lui-même, pour ses propres données (accès en lecture,
  identifiant unique = matricule).
- Le personnel administratif de l'UP-2A habilité (rôle "admin" du
  back-office), pour le traitement des candidatures et la gestion
  académique.
- [À CONFIRMER — si un sous-traitant technique tiers (agence, mainteneur)
  dispose d'un accès administrateur en dehors de LUPORA Group, le
  préciser ici.]

### Durée de conservation

[À CONFIRMER — durée de conservation des dossiers de candidature non
retenus, et des dossiers étudiants après la fin du parcours, à définir
avec le client selon la réglementation applicable.]

### Vos droits

Conformément à la réglementation applicable en matière de protection des
données personnelles, vous disposez d'un droit d'accès, de rectification
et de suppression de vos données. Pour l'exercer, contactez l'UP-2A au
+226 50 63 85 54 ou par e-mail à [À CONFIRMER].

### Cookies

Ce site peut utiliser des cookies strictement nécessaires à son
fonctionnement (ex. maintien de la session de connexion dans l'espace
étudiant). [À CONFIRMER — si un outil de mesure d'audience (statistiques
de visite) est ajouté ultérieurement, cette section devra être mise à
jour pour le décrire et, si nécessaire, un bandeau de consentement devra
être ajouté ; à ce jour, aucun outil de ce type n'est prévu dans le
périmètre du projet, voir CLAUDE.md.]

### Contact

Pour toute question relative à cette politique de confidentialité :
Université Privée An-Nahdah d'Afrique (UP-2A), Ouagadougou - Balkuy,
Burkina Faso — +226 50 63 85 54 — [À CONFIRMER : e-mail].
