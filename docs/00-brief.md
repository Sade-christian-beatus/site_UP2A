# 00 — Brief projet

> Document de cadrage. Sert de référence pour trancher les questions de
> périmètre. En cas de doute, ce fichier prime sur toute improvisation.

## Client

**LUPORA Group**, pour le compte de l'**Université Privée An-Nahdah d'Afrique
(UP-2A)**, Ouagadougou, Burkina Faso.

- Slogan : *« Former aujourd'hui les élites de demain »*.
- Statut : université privée reconnue, autorisation MESRI n°2026-001647.
- Portée par une association islamique → identité de **valeurs** (excellence,
  savoir, intégrité), ton **ouvert et professionnel** (pas confessionnel dans
  le discours, les valeurs infusent le ton plutôt que le vocabulaire).

## Objectif

Remplacer intégralement le site actuel (https://bdo-burkina.com, jetable) par
un dispositif numérique complet :

1. Vitrine institutionnelle qui donne envie de candidater et rassure sur le
   sérieux académique.
2. Un parcours de préinscription simple, sans friction, sans paiement.
3. Un espace étudiant numérique (emploi du temps, notes, documents...).
4. Un back-office pour l'équipe pédagogique/administrative.

## Périmètre (in)

- Site vitrine WordPress : home cinématique, pages formations (une par
  licence), admissions, actualités, contact.
- Formulaire de préinscription multi-étapes, sans paiement, écrivant dans
  Supabase via le plugin `up2a-preinscription`.
- Espace étudiant (Next.js + Supabase) : tableau de bord, emploi du temps,
  supports de cours, examens, résultats, documents, annonces — **lecture
  seule côté étudiant** dans un premier temps.
- Back-office (même app Next.js, rôle admin) : gestion des utilisateurs,
  formations, candidatures → transformation en étudiants, saisie
  académique, publication d'annonces.

## Périmètre (out) — non-objectifs

- **Aucun paiement en ligne**, nulle part dans la préinscription (ni mobile
  money, ni carte, ni frais de dossier en ligne).
- Pas de modèle 3D navigable du bâtiment généré depuis l'image existante —
  on reste sur de la parallaxe 2.5D / du pré-rendu.
- Pas de conservation de contenu de bdo-burkina.com.
- Pas de fonctionnalités non listées ci-dessus (messagerie interne,
  paiement de scolarité, application mobile native...) tant que la roadmap
  ne les inscrit pas explicitement.

## Personas

- **Futur étudiant / famille** — sur mobile, souvent en 3G/4G, cherche
  rapidement : formations disponibles, conditions d'admission, comment
  candidater, coût des frais de scolarité (info, pas paiement).
- **Étudiant inscrit** — consulte son emploi du temps, ses résultats, ses
  supports de cours, les annonces. Usage récurrent, doit être rapide même
  sur connexion faible.
- **Administration académique (admin)** — gère les candidatures reçues, les
  transforme en dossiers étudiants, saisit les données académiques
  (emplois du temps, notes), publie des annonces.

## Contraintes absolues (rappel — voir CLAUDE.md §3)

1. Préinscription sans aucun paiement.
2. Supabase = source unique de vérité pour candidatures → étudiants →
   académique.
3. Clé `service_role` Supabase jamais exposée navigateur, uniquement côté
   serveur (wp-config.php pour WordPress, variables serveur pour Next.js).
4. Performance mobile/data locale prioritaire (voir docs/CLAUDE.md §7).
5. Aucune action destructive (site live, base) sans feu vert explicite.

## Assets

Un seul visuel existe à ce jour (rendu du bâtiment). Les autres visuels
seront générés par IA et téléversés progressivement. Toute page doit prévoir
des emplacements/placeholders clairement identifiés plutôt que bloquer sur
un asset manquant.

## Définition du "fini" pour la V1

- Les 3 contraintes absolues respectées sans exception.
- Home + pages formations + admissions + contact en ligne, testées sur
  Android milieu de gamme en 3G/4G réelle.
- Préinscription fonctionnelle de bout en bout (formulaire → Supabase →
  e-mail de confirmation).
- Espace étudiant : connexion + consultation (lecture) des données
  académiques d'un étudiant de test.
- Back-office : un admin peut transformer une candidature en étudiant et
  saisir un minimum de données académiques.
