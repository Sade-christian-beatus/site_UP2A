# 01 — Architecture & flux de données

## Vue d'ensemble

```
┌─────────────────────┐      ┌───────────────────────────┐      ┌─────────────────────┐
│   WordPress          │      │        Supabase            │      │   Next.js            │
│   (vitrine publique)  │      │  (source unique de vérité) │      │  app-etudiant         │
│                       │      │                             │      │  (étudiant + admin)   │
│ - Home cinématique     │      │ - PostgreSQL (schéma métier)│      │                       │
│ - Pages formations     │      │ - Auth (comptes étudiant/   │      │ - Auth via            │
│ - Admissions/Contact   │      │   admin)                    │      │   @supabase/ssr        │
│ - up2a-core (GSAP)     │◄────►│ - Storage (pièces jointes,  │◄────►│ - Dashboard étudiant   │
│ - up2a-preinscription  │ REST │   supports de cours, docs)  │  JS  │ - Back-office admin    │
│   (PHP, server-side)   │      │ - RLS (contrôle d'accès)    │      │                       │
└─────────────────────┘      └───────────────────────────┘      └─────────────────────┘
```

- **WordPress** ne connaît la logique métier qu'à travers un seul point
  d'entrée : le formulaire de préinscription. Il n'affiche aucune donnée
  académique dynamique en V1 (pas de "mes notes" dans WordPress).
- **Supabase** est la seule base de données de l'application. Aucune donnée
  candidature/étudiant/académique ne vit ailleurs, ne serait-ce que
  temporairement (pas de synchronisation à double sens, pas de cache
  MySQL WordPress qui ferait autorité).
- **Next.js** ne fait que lire/écrire dans Supabase via `@supabase/supabase-js`
  avec la clé `anon` + RLS. Aucune logique d'autorisation dupliquée côté
  client : la RLS est la seule source de vérité pour "qui peut voir quoi".

## Flux 1 — Préinscription (WordPress → Supabase)

1. Le visiteur remplit le formulaire multi-étapes (plugin
   `up2a-preinscription`) sur le site WordPress.
2. À la soumission finale, le **PHP côté serveur** (jamais le navigateur)
   appelle l'API REST Supabase avec la clé **`service_role`**, stockée dans
   `wp-config.php` (ou variable d'environnement serveur), pour :
   - uploader les pièces jointes dans Supabase Storage (bucket
     `candidatures`) ;
   - insérer la ligne dans `public.candidatures` (statut `nouvelle`).
3. Un e-mail de confirmation est envoyé au candidat via `wp_mail()` (SMTP
   côté WordPress) — tranché en phase 5 : plus simple qu'une fonction
   Supabase Edge, pas de dépendance supplémentaire à opérer. Une
   notification interne (e-mail admin, adresse filtrable via
   `up2a_preinscription_notify_email`) part en parallèle. Pour une bonne
   délivrabilité en production, configurer un plugin SMTP (ex. **WP Mail
   SMTP**) plutôt que le `mail()` PHP par défaut — voir
   wordpress/README.md Étape 5.
4. **Aucun paiement n'intervient à aucune étape.**
5. Le candidat n'a pas de compte à ce stade : il n'est pas dans
   `auth.users`. C'est l'admin qui, plus tard, transforme la candidature en
   compte étudiant (flux 3).

Le navigateur du visiteur ne voit jamais la clé `service_role` : le
formulaire poste vers un endpoint WordPress (admin-ajax.php ou route REST
WP dédiée), qui lui seul parle à Supabase.

## Flux 2 — Espace étudiant (Next.js ↔ Supabase, rôle étudiant)

1. L'étudiant se connecte via Supabase Auth (email/mot de passe, compte créé
   par l'admin lors de la transformation de sa candidature).
2. Le client Next.js utilise la clé **`anon`** uniquement. Toutes les
   lectures (emploi du temps, supports, examens, résultats, documents,
   annonces) passent par des requêtes `supabase-js` filtrées par les
   politiques RLS : un étudiant ne peut lire que ses propres données (ou
   les données publiques à sa formation/année).
3. V1 : accès **lecture seule** pour l'étudiant (pas d'édition de profil ni
   de dépôt de document dans le périmètre initial — à confirmer en phase 6
   de la roadmap).

## Flux 3 — Back-office (Next.js ↔ Supabase, rôle admin)

1. L'admin se connecte via Supabase Auth (compte créé manuellement en base
   au démarrage du projet — voir "Bootstrap" ci-dessous).
2. Toujours avec la clé `anon` + RLS : les politiques accordent aux
   profils `role = 'admin'` un accès élargi (lecture/écriture) sur
   candidatures, étudiants, académique, contenus.
3. Actions clés : passer une candidature en `en_cours`/`acceptee`/`refusee`,
   transformer une candidature acceptée en compte étudiant (crée une ligne
   `auth.users` + `profiles` + `etudiants`), saisir emplois du temps,
   examens, résultats, publier des annonces et supports de cours.
4. La clé `service_role` peut être nécessaire pour certaines opérations que
   la RLS ne permet pas côté client (ex. création de compte `auth.users`
   depuis le back-office). Ces opérations passent par du code **exécuté
   côté serveur uniquement** — en pratique une Server Action (`"use
   server"`, cohérent avec le reste du code, voir
   `app-etudiant/lib/admin/actions.ts`) plutôt qu'une route API dédiée :
   les deux offrent la même garantie (jamais de code exécuté dans le
   navigateur), la Server Action évite juste d'introduire un deuxième
   pattern dans le projet.

## Modèle d'autorisation

- Deux rôles applicatifs : `etudiant` et `admin`, stockés dans
  `public.profiles.role` (colonne liée 1:1 à `auth.users`).
- La RLS s'appuie sur une fonction `public.current_role()` (SECURITY
  DEFINER) qui lit `profiles.role` pour `auth.uid()`, afin d'éviter les
  problèmes de récursion RLS sur la table `profiles` elle-même.
- Voir `supabase/migrations/0002_rls.sql` pour le détail des politiques par
  table (regroupée avec les autres migrations pour rester rejouable par la
  CLI Supabase — voir `supabase/README.md`).

## Vue d'ensemble du modèle de données

Voir `supabase/migrations/0001_init.sql` pour le DDL complet. Résumé des
entités :

| Table | Rôle |
|---|---|
| `profiles` | Extension de `auth.users` : nom, prénom, téléphone, `role` |
| `facultes` | Facultés de l'université |
| `formations` | Licences rattachées à une faculté |
| `annees_academiques` | Années académiques (ex. 2026-2027), une "courante" |
| `candidatures` | Préinscriptions soumises depuis WordPress (sans paiement) |
| `etudiants` | Dossier étudiant, créé à partir d'une candidature acceptée |
| `emplois_du_temps` | Créneaux de cours par formation/année |
| `supports_cours` | Documents pédagogiques par formation/année |
| `examens` | Sessions d'examen par formation/année |
| `resultats` | Notes d'un étudiant à un examen (publication contrôlée) |
| `documents` | Documents administratifs générés pour un étudiant |
| `annonces` | Annonces (globales ou ciblées formation/année) |

## Bootstrap (premier compte admin)

Pas de scellement automatique d'un compte admin dans le seed (un mot de
passe en clair dans une migration versionnée serait une fuite de secret).
Procédure recommandée une fois le projet Supabase créé :

1. Créer l'utilisateur via Supabase Auth (dashboard ou CLI), avec un e-mail
   et mot de passe fournis hors dépôt.
2. Insérer manuellement la ligne `profiles` correspondante avec
   `role = 'admin'` (script one-off, non versionné, ou via le dashboard
   SQL editor).

## Stockage (Supabase Storage)

Buckets déclarés dans `supabase/config.toml` (`[storage.buckets.*]`), avec
RLS sur `storage.objects` dans `supabase/migrations/0004_storage_policies.sql` :

- `candidatures` — pièces jointes des préinscriptions (accès restreint aux
  admins ; upload réel server-side via `service_role`, qui bypass la RLS).
- `supports-cours` — documents pédagogiques. RLS admin uniquement pour
  l'instant ; l'accès étudiant fin (lecture de sa propre formation/année)
  sera ajouté en phase 6 une fois la convention de chemin des objets
  arrêtée avec les écrans réels.
- `documents-etudiants` — documents administratifs générés. Même
  remarque : RLS admin uniquement pour l'instant, affinée en phase 6.

## Ce que WordPress ne fait jamais

- Ne lit ni n'écrit les tables académiques (`etudiants`, `emplois_du_temps`,
  `supports_cours`, `examens`, `resultats`, `documents`).
- N'affiche pas de contenu applicatif dynamique tiré de Supabase — la
  vitrine reste un site de contenu, l'app Next.js porte l'application.
- Ne détient jamais la clé `service_role` ailleurs que dans sa configuration
  serveur (`wp-config.php` / variable d'environnement du serveur
  d'hébergement).
