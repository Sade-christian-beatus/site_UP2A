# 05 — Contenus (textes des pages)

> **Statut : brouillon de travail**, à valider/corriger par le client avant
> intégration finale dans Elementor. Tout ce qui est marqué
> `[À CONFIRMER]` est une hypothèse ou un placeholder — **ne pas publier
> tel quel**. Les seules données factuelles fermes viennent de CLAUDE.md
> (nom, slogan, ville, n° d'autorisation) ; tout le reste (chiffres,
> intitulés précis de licences, coordonnées) doit être confirmé par le
> client avant mise en ligne.

## Identité

- Nom complet : **Université Privée An-Nahdah d'Afrique (UP-2A)**
- Slogan : *« Former aujourd'hui les élites de demain »*
- Ville : Ouagadougou, Burkina Faso
- Autorisation : MESRI n°2026-001647
- Téléphone (confirmé, logo/en-tête officiel) : **+226 50 63 85 54**
- Phrase d'accroche de l'en-tête (bandeau défilant, confirmée) :
  *« Rejoignez dès aujourd'hui l'UNIVERSITÉ PRIVÉE AN-NAHDAH D'AFRIQUE
  pour bâtir une carrière à la hauteur de vos ambitions »*

## Home — Hero (slider)

> Titre principal
**Former aujourd'hui les élites de demain !**

> Sous-titre
Une formation de qualité pour construire les compétences, développer les
ambitions et préparer les professionnels de demain.

> CTA (deux boutons)
Découvrir nos formations · S'inscrire maintenant

> Badges de confiance (sous les CTA)
Préinscription 100% en ligne · Aucun frais de dossier · 4 licences, Ouagadougou

> Images du slider
`hero-slide-1.webp` / `hero-slide-2.webp` — photos de campus fournies par
le client (voir `wordpress/plugins/up2a-core/assets/img/`).

## Home — Pourquoi choisir l'UP-2A

> Titre
Pourquoi choisir l'UP-2A

> Paragraphe 1
Fondée par une association engagée pour l'éducation, l'Université Privée
An-Nahdah d'Afrique accompagne chaque étudiant vers l'excellence
académique et professionnelle, dans un cadre exigeant et bienveillant.

> Paragraphe 2
À Ouagadougou, au cœur du Burkina Faso, nous formons une nouvelle
génération de diplômés capables de répondre aux défis économiques,
sociaux et technologiques de l'Afrique de demain.

> Points forts
- Encadrement pédagogique de proximité, en petits effectifs
- Formations connectées aux besoins concrets du marché du travail
- Préinscription entièrement en ligne, sans frais de dossier
- Une communauté étudiante ouverte sur l'Afrique et le monde

> Visuel
Pas de photo disponible à ce jour (voir CLAUDE.md §3) — illustration de
marque (`assets/img/motif-communaute.svg`) utilisée en attendant. À
remplacer par une vraie photo de vie étudiante dès qu'elle existe (voir
wordpress/README.md, constante `UP2A_LIFE_IMAGE_URL`).

## Home — Valeurs

**Excellence** — Une exigence académique constante, portée par un corps
enseignant qualifié et des méthodes pédagogiques rigoureuses.

**Savoir** — Une formation ancrée dans les savoirs fondamentaux et les
compétences pratiques attendues par le monde professionnel.

**Intégrité** — Une éducation qui forme des femmes et des hommes
responsables, honnêtes et engagés envers leur communauté.

**Ouverture** — Une université tournée vers l'Afrique et le monde,
accueillante pour tous les profils d'étudiants.

*(4ᵉ valeur "Ouverture" proposée pour équilibrer la grille de 4 cartes —
[À CONFIRMER] avec le client, qui peut préférer un autre intitulé.)*

## Home — Chiffres clés `[À CONFIRMER — ne pas publier sans validation]`

- Année de création : `[À CONFIRMER]`
- Nombre de facultés : `[À CONFIRMER]`
- Nombre de licences proposées : 4 *(cohérent avec CLAUDE.md, intitulés
  précis à confirmer — voir "Formations" ci-dessous)*
- Taux d'encadrement / autres repères : `[À CONFIRMER]`

## Formations (confirmées par le client, 2026-09-22)

2 facultés, 4 licences — repris à l'identique dans
`supabase/migrations/0003_seed.sql` et dans le template
`wordpress/plugins/up2a-core/templates/front-page-onepage.php`. Les
descriptions de chaque licence (une phrase) sont une formulation de
travail à valider, les intitulés eux sont confirmés.

**Sciences Juridiques, Politiques et de l'Administration (SJPA)**

1. **Licence en Droit Public**
   Droit constitutionnel, administratif et institutions publiques — pour
   les métiers de l'administration, de la fonction publique et des
   collectivités.

2. **Licence en Droit Privé**
   Droit civil, des affaires et des contrats — pour les métiers du droit,
   du conseil juridique et des professions judiciaires.

**Sciences Économiques et de Gestion (SEG)**

3. **Licence en Logistique Internationale**
   Transport, chaîne d'approvisionnement et commerce international — pour
   les métiers de la logistique et des échanges.

4. **Licence en Marketing Communication**
   Stratégie de marque, communication et marketing digital — pour les
   métiers du marketing et de la communication d'entreprise.

## Admissions — Étapes (résumé pour la home)

1. Je consulte les formations disponibles.
2. Je remplis le formulaire de préinscription en ligne (pièces d'identité
   et diplômes à joindre).
3. Je reçois une confirmation par e-mail.
4. L'université étudie mon dossier et me contacte pour la suite.

*(Aucune étape de paiement — c'est volontaire et non négociable, voir
CLAUDE.md §3.)*

## Contact `[adresse précise, e-mail et réseaux sociaux à confirmer]`

- Adresse : **Ouagadougou - Balkuy, Burkina Faso** (confirmé — numéro de
  rue/repère précis encore `[À CONFIRMER]` si besoin pour un plan/itinéraire)
- Téléphone : **+226 50 63 85 54** (confirmé)
- E-mail : `[À CONFIRMER]`
- Réseaux sociaux : `[À CONFIRMER]`
- Horaires : `[À CONFIRMER — "Lundi – Vendredi, 8h – 17h" utilisé comme placeholder plausible sur la home]`

## Mentions légales (footer)

Université Privée An-Nahdah d'Afrique (UP-2A) — autorisation MESRI
n°2026-001647. `[Autres mentions légales/statutaires à confirmer avec le
client.]`
