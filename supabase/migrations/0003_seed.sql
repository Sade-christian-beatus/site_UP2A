-- 0003_seed.sql
-- Données de démonstration : facultés, 4 licences, année académique
-- courante.
--
-- Intitulés confirmés par le client (2026-09-22) : 2 facultés (SJPA, SEG),
-- 4 licences — voir docs/05-contenus.md. Les dates de l'année académique
-- restent un exemple cohérent avec un calendrier universitaire standard
-- (octobre à juillet), à ajuster si besoin.

insert into public.annees_academiques (libelle, date_debut, date_fin, est_courante)
values ('2026-2027', '2026-10-01', '2027-07-31', true);

insert into public.facultes (nom, slug, description) values
  ('Sciences Juridiques, Politiques et de l''Administration (SJPA)', 'sjpa',
   'Droit public, droit privé et sciences politiques.'),
  ('Sciences Économiques et de Gestion (SEG)', 'seg',
   'Logistique internationale, marketing et gestion.');

insert into public.formations (faculte_id, nom, slug, niveau, duree_annees, description)
select f.id, v.nom, v.slug, 'licence', 3, v.description
from (
  values
    ('sjpa', 'Licence en Droit Public', 'licence-droit-public',
     'Droit constitutionnel, administratif et institutions publiques.'),
    ('sjpa', 'Licence en Droit Privé', 'licence-droit-prive',
     'Droit civil, des affaires et des contrats.'),
    ('seg', 'Licence en Logistique Internationale', 'licence-logistique-internationale',
     'Transport, chaîne d''approvisionnement et commerce international.'),
    ('seg', 'Licence en Marketing Communication', 'licence-marketing-communication',
     'Stratégie de marque, communication et marketing digital.')
) as v (faculte_slug, nom, slug, description)
join public.facultes f on f.slug = v.faculte_slug;
