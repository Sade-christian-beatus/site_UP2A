-- 0003_seed.sql
-- Données de démonstration : facultés, 4 licences, année académique
-- courante.
--
-- ⚠️ PLACEHOLDERS — les intitulés de facultés/licences ci-dessous sont des
-- propositions de travail (voir docs/05-contenus.md), pas des données
-- officielles de l'université. À valider ou remplacer par le client avant
-- toute mise en production. Idem pour les dates de l'année académique,
-- posées ici à titre d'exemple cohérent avec un calendrier universitaire
-- standard (octobre à juillet).

insert into public.annees_academiques (libelle, date_debut, date_fin, est_courante)
values ('2026-2027', '2026-10-01', '2027-07-31', true);

insert into public.facultes (nom, slug, description) values
  ('Faculté des Sciences Économiques et de Gestion', 'sciences-economiques-et-gestion',
   'Formations en gestion, comptabilité et management.'),
  ('Faculté de Droit et Sciences Politiques', 'droit-et-sciences-politiques',
   'Formation juridique généraliste.'),
  ('Faculté des Sciences et Technologies', 'sciences-et-technologies',
   'Formations aux compétences numériques et techniques.'),
  ('Faculté des Lettres, Langues et Sciences Humaines', 'lettres-langues-et-sciences-humaines',
   'Formations en communication, langues et sciences humaines.');

insert into public.formations (faculte_id, nom, slug, niveau, duree_annees, description)
select f.id, v.nom, v.slug, 'licence', 3, v.description
from (
  values
    ('sciences-economiques-et-gestion', 'Licence en Gestion des Entreprises', 'licence-gestion-des-entreprises',
     'Fondamentaux de la gestion, de la comptabilité et du management.'),
    ('droit-et-sciences-politiques', 'Licence en Droit', 'licence-droit',
     'Formation juridique généraliste.'),
    ('sciences-et-technologies', 'Licence en Informatique et Réseaux', 'licence-informatique-et-reseaux',
     'Développement, réseaux et systèmes.'),
    ('lettres-langues-et-sciences-humaines', 'Licence en Communication et Journalisme', 'licence-communication-et-journalisme',
     'Métiers de la communication, de l''information et des médias.')
) as v (faculte_slug, nom, slug, description)
join public.facultes f on f.slug = v.faculte_slug;
