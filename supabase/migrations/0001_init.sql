-- 0001_init.sql
-- Schéma initial UP-2A : facultés, formations, académique, candidatures,
-- étudiants. Voir docs/01-architecture.md pour la vue d'ensemble et
-- supabase/migrations/0002_rls.sql pour les politiques d'accès détaillées.
--
-- Convention : chaque table active RLS ici même ; les policies détaillées
-- vivent dans 0002_rls.sql (voir docs/04-conventions.md).

create extension if not exists pgcrypto;

-- ---------------------------------------------------------------------------
-- Types énumérés
-- ---------------------------------------------------------------------------

create type public.role_utilisateur as enum ('etudiant', 'admin');

create type public.statut_candidature as enum (
  'nouvelle', 'en_cours', 'acceptee', 'refusee', 'transformee'
);

create type public.statut_etudiant as enum (
  'actif', 'suspendu', 'diplome', 'abandon'
);

create type public.type_examen as enum ('partiel', 'final', 'rattrapage');

create type public.type_document as enum (
  'releve_notes', 'certificat_scolarite', 'attestation', 'autre'
);

create type public.jour_semaine as enum (
  'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'
);

-- ---------------------------------------------------------------------------
-- Fonction utilitaire : mise à jour automatique de updated_at
-- ---------------------------------------------------------------------------

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

-- ---------------------------------------------------------------------------
-- profiles — extension de auth.users
-- ---------------------------------------------------------------------------

create table public.profiles (
  id uuid primary key references auth.users (id) on delete cascade,
  role public.role_utilisateur not null default 'etudiant',
  nom text not null,
  prenom text not null,
  telephone text,
  created_at timestamptz not null default now()
);

alter table public.profiles enable row level security;

comment on table public.profiles is
  'Extension de auth.users : identité et rôle applicatif (etudiant/admin).';

-- ---------------------------------------------------------------------------
-- facultes
-- ---------------------------------------------------------------------------

create table public.facultes (
  id uuid primary key default gen_random_uuid(),
  nom text not null,
  slug text not null unique,
  description text,
  created_at timestamptz not null default now()
);

alter table public.facultes enable row level security;

-- ---------------------------------------------------------------------------
-- formations (licences)
-- ---------------------------------------------------------------------------

create table public.formations (
  id uuid primary key default gen_random_uuid(),
  faculte_id uuid not null references public.facultes (id) on delete restrict,
  nom text not null,
  slug text not null unique,
  niveau text not null default 'licence',
  duree_annees smallint not null default 3 check (duree_annees > 0),
  description text,
  created_at timestamptz not null default now()
);

alter table public.formations enable row level security;

create index formations_faculte_id_idx on public.formations (faculte_id);

-- ---------------------------------------------------------------------------
-- annees_academiques
-- ---------------------------------------------------------------------------

create table public.annees_academiques (
  id uuid primary key default gen_random_uuid(),
  libelle text not null unique,
  date_debut date not null,
  date_fin date not null check (date_fin > date_debut),
  est_courante boolean not null default false,
  created_at timestamptz not null default now()
);

alter table public.annees_academiques enable row level security;

-- Au plus une année académique "courante" à la fois.
create unique index annees_academiques_courante_unique
  on public.annees_academiques (est_courante)
  where est_courante;

-- ---------------------------------------------------------------------------
-- candidatures (préinscriptions — jamais de paiement associé)
-- ---------------------------------------------------------------------------

create table public.candidatures (
  id uuid primary key default gen_random_uuid(),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  nom text not null,
  prenom text not null,
  date_naissance date not null,
  sexe text check (sexe in ('M', 'F')),
  email text not null check (email ~* '^[^@\s]+@[^@\s]+\.[^@\s]+$'),
  telephone text not null,
  adresse text,
  formation_id uuid not null references public.formations (id) on delete restrict,
  annee_academique_id uuid not null references public.annees_academiques (id) on delete restrict,
  diplome_obtenu text,
  etablissement_origine text,
  pieces_jointes jsonb not null default '[]'::jsonb,
  statut public.statut_candidature not null default 'nouvelle',
  notes_admin text
);

alter table public.candidatures enable row level security;

create index candidatures_formation_id_idx on public.candidatures (formation_id);
create index candidatures_annee_academique_id_idx on public.candidatures (annee_academique_id);
create index candidatures_statut_idx on public.candidatures (statut);

create trigger candidatures_set_updated_at
  before update on public.candidatures
  for each row
  execute function public.set_updated_at();

comment on table public.candidatures is
  'Préinscriptions soumises depuis WordPress. Aucun champ de paiement : '
  'contrainte absolue du projet (voir CLAUDE.md §3).';

-- ---------------------------------------------------------------------------
-- etudiants
-- ---------------------------------------------------------------------------

create table public.etudiants (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid references public.profiles (id) on delete set null,
  candidature_id uuid references public.candidatures (id) on delete set null,
  matricule text not null unique,
  formation_id uuid not null references public.formations (id) on delete restrict,
  annee_academique_id uuid not null references public.annees_academiques (id) on delete restrict,
  statut public.statut_etudiant not null default 'actif',
  created_at timestamptz not null default now()
);

alter table public.etudiants enable row level security;

-- Un profil auth ne correspond qu'à un seul dossier étudiant.
create unique index etudiants_profile_id_unique
  on public.etudiants (profile_id)
  where profile_id is not null;

create index etudiants_formation_id_idx on public.etudiants (formation_id);
create index etudiants_annee_academique_id_idx on public.etudiants (annee_academique_id);

comment on column public.etudiants.annee_academique_id is
  'Année académique d''entrée de l''étudiant dans cette formation.';

-- ---------------------------------------------------------------------------
-- emplois_du_temps
-- ---------------------------------------------------------------------------

create table public.emplois_du_temps (
  id uuid primary key default gen_random_uuid(),
  formation_id uuid not null references public.formations (id) on delete cascade,
  annee_academique_id uuid not null references public.annees_academiques (id) on delete cascade,
  jour_semaine public.jour_semaine not null,
  heure_debut time not null,
  heure_fin time not null check (heure_fin > heure_debut),
  matiere text not null,
  enseignant text,
  salle text,
  created_at timestamptz not null default now()
);

alter table public.emplois_du_temps enable row level security;

create index emplois_du_temps_formation_annee_idx
  on public.emplois_du_temps (formation_id, annee_academique_id);

-- ---------------------------------------------------------------------------
-- supports_cours
-- ---------------------------------------------------------------------------

create table public.supports_cours (
  id uuid primary key default gen_random_uuid(),
  formation_id uuid not null references public.formations (id) on delete cascade,
  annee_academique_id uuid not null references public.annees_academiques (id) on delete cascade,
  matiere text not null,
  titre text not null,
  description text,
  fichier_url text not null,
  created_by uuid references public.profiles (id) on delete set null,
  published_at timestamptz not null default now()
);

alter table public.supports_cours enable row level security;

create index supports_cours_formation_annee_idx
  on public.supports_cours (formation_id, annee_academique_id);

-- ---------------------------------------------------------------------------
-- examens
-- ---------------------------------------------------------------------------

create table public.examens (
  id uuid primary key default gen_random_uuid(),
  formation_id uuid not null references public.formations (id) on delete cascade,
  annee_academique_id uuid not null references public.annees_academiques (id) on delete cascade,
  matiere text not null,
  type public.type_examen not null default 'partiel',
  date_examen date not null,
  heure_debut time not null,
  heure_fin time not null check (heure_fin > heure_debut),
  salle text,
  created_at timestamptz not null default now()
);

alter table public.examens enable row level security;

create index examens_formation_annee_idx
  on public.examens (formation_id, annee_academique_id);

-- ---------------------------------------------------------------------------
-- resultats
-- ---------------------------------------------------------------------------

create table public.resultats (
  id uuid primary key default gen_random_uuid(),
  etudiant_id uuid not null references public.etudiants (id) on delete cascade,
  examen_id uuid not null references public.examens (id) on delete cascade,
  note numeric(4, 2) not null check (note >= 0 and note <= 20),
  mention text,
  publie boolean not null default false,
  created_at timestamptz not null default now(),
  unique (etudiant_id, examen_id)
);

alter table public.resultats enable row level security;

create index resultats_etudiant_id_idx on public.resultats (etudiant_id);

comment on column public.resultats.publie is
  'Un résultat non publié reste invisible pour l''étudiant, même le sien.';

-- ---------------------------------------------------------------------------
-- documents
-- ---------------------------------------------------------------------------

create table public.documents (
  id uuid primary key default gen_random_uuid(),
  etudiant_id uuid not null references public.etudiants (id) on delete cascade,
  type public.type_document not null default 'autre',
  fichier_url text not null,
  genere_le timestamptz not null default now(),
  created_by uuid references public.profiles (id) on delete set null
);

alter table public.documents enable row level security;

create index documents_etudiant_id_idx on public.documents (etudiant_id);

-- ---------------------------------------------------------------------------
-- annonces
-- ---------------------------------------------------------------------------

create table public.annonces (
  id uuid primary key default gen_random_uuid(),
  titre text not null,
  contenu text not null,
  formation_id uuid references public.formations (id) on delete cascade,
  annee_academique_id uuid references public.annees_academiques (id) on delete cascade,
  publie_le timestamptz not null default now(),
  created_by uuid references public.profiles (id) on delete set null,
  created_at timestamptz not null default now()
);

alter table public.annonces enable row level security;

comment on column public.annonces.formation_id is
  'NULL = annonce visible par toutes les formations.';
comment on column public.annonces.annee_academique_id is
  'NULL = annonce visible pour toutes les années académiques.';
