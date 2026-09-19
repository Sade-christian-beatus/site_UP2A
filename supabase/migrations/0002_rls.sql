-- 0002_rls.sql
-- Politiques RLS pour toutes les tables créées dans 0001_init.sql.
-- Principe : la RLS est la SEULE barrière d'autorisation de l'application
-- (voir docs/01-architecture.md "Modèle d'autorisation"). Le client
-- (WordPress server-side, Next.js) ne fait qu'exécuter des requêtes ; c'est
-- ici que se décide qui peut voir/modifier quoi.

-- ---------------------------------------------------------------------------
-- Fonctions utilitaires (SECURITY DEFINER pour éviter la récursion RLS sur
-- profiles/etudiants lors de leur propre évaluation).
-- ---------------------------------------------------------------------------

create or replace function public.is_admin()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select exists (
    select 1 from public.profiles
    where id = auth.uid() and role = 'admin'
  );
$$;

create or replace function public.mon_etudiant_id()
returns uuid
language sql
stable
security definer
set search_path = public
as $$
  select id from public.etudiants where profile_id = auth.uid() limit 1;
$$;

create or replace function public.ma_formation_id()
returns uuid
language sql
stable
security definer
set search_path = public
as $$
  select formation_id from public.etudiants where profile_id = auth.uid() limit 1;
$$;

create or replace function public.mon_annee_academique_id()
returns uuid
language sql
stable
security definer
set search_path = public
as $$
  select annee_academique_id from public.etudiants where profile_id = auth.uid() limit 1;
$$;

grant execute on function public.is_admin() to anon, authenticated;
grant execute on function public.mon_etudiant_id() to anon, authenticated;
grant execute on function public.ma_formation_id() to anon, authenticated;
grant execute on function public.mon_annee_academique_id() to anon, authenticated;

-- ---------------------------------------------------------------------------
-- profiles
-- ---------------------------------------------------------------------------

create policy profiles_select_self_or_admin
  on public.profiles for select
  using (id = auth.uid() or public.is_admin());

create policy profiles_insert_admin
  on public.profiles for insert
  with check (public.is_admin());

create policy profiles_update_self_or_admin
  on public.profiles for update
  using (id = auth.uid() or public.is_admin())
  with check (id = auth.uid() or public.is_admin());

create policy profiles_delete_admin
  on public.profiles for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- facultes — lecture publique (vitrine + formulaire de préinscription),
-- écriture admin uniquement.
-- ---------------------------------------------------------------------------

create policy facultes_select_public
  on public.facultes for select
  using (true);

create policy facultes_insert_admin
  on public.facultes for insert
  with check (public.is_admin());

create policy facultes_update_admin
  on public.facultes for update
  using (public.is_admin())
  with check (public.is_admin());

create policy facultes_delete_admin
  on public.facultes for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- formations — même logique que facultes.
-- ---------------------------------------------------------------------------

create policy formations_select_public
  on public.formations for select
  using (true);

create policy formations_insert_admin
  on public.formations for insert
  with check (public.is_admin());

create policy formations_update_admin
  on public.formations for update
  using (public.is_admin())
  with check (public.is_admin());

create policy formations_delete_admin
  on public.formations for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- annees_academiques — même logique.
-- ---------------------------------------------------------------------------

create policy annees_academiques_select_public
  on public.annees_academiques for select
  using (true);

create policy annees_academiques_insert_admin
  on public.annees_academiques for insert
  with check (public.is_admin());

create policy annees_academiques_update_admin
  on public.annees_academiques for update
  using (public.is_admin())
  with check (public.is_admin());

create policy annees_academiques_delete_admin
  on public.annees_academiques for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- candidatures — insertion publique contrôlée (préinscription), lecture et
-- gestion réservées à l'admin. Le flux réel passe par service_role côté
-- serveur WordPress (qui bypass la RLS de toute façon) ; cette policy sert
-- de garde-fou en défense en profondeur si l'insertion était un jour faite
-- avec la clé anon.
-- ---------------------------------------------------------------------------

create policy candidatures_insert_public
  on public.candidatures for insert
  with check (statut = 'nouvelle');

create policy candidatures_select_admin
  on public.candidatures for select
  using (public.is_admin());

create policy candidatures_update_admin
  on public.candidatures for update
  using (public.is_admin())
  with check (public.is_admin());

create policy candidatures_delete_admin
  on public.candidatures for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- etudiants — l'étudiant ne voit que son propre dossier.
-- ---------------------------------------------------------------------------

create policy etudiants_select_self_or_admin
  on public.etudiants for select
  using (profile_id = auth.uid() or public.is_admin());

create policy etudiants_insert_admin
  on public.etudiants for insert
  with check (public.is_admin());

create policy etudiants_update_admin
  on public.etudiants for update
  using (public.is_admin())
  with check (public.is_admin());

create policy etudiants_delete_admin
  on public.etudiants for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- emplois_du_temps — l'étudiant ne voit que les créneaux de sa propre
-- formation/année académique.
-- ---------------------------------------------------------------------------

create policy emplois_du_temps_select_self_or_admin
  on public.emplois_du_temps for select
  using (
    public.is_admin()
    or (
      formation_id = public.ma_formation_id()
      and annee_academique_id = public.mon_annee_academique_id()
    )
  );

create policy emplois_du_temps_insert_admin
  on public.emplois_du_temps for insert
  with check (public.is_admin());

create policy emplois_du_temps_update_admin
  on public.emplois_du_temps for update
  using (public.is_admin())
  with check (public.is_admin());

create policy emplois_du_temps_delete_admin
  on public.emplois_du_temps for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- supports_cours — même logique que emplois_du_temps.
-- ---------------------------------------------------------------------------

create policy supports_cours_select_self_or_admin
  on public.supports_cours for select
  using (
    public.is_admin()
    or (
      formation_id = public.ma_formation_id()
      and annee_academique_id = public.mon_annee_academique_id()
    )
  );

create policy supports_cours_insert_admin
  on public.supports_cours for insert
  with check (public.is_admin());

create policy supports_cours_update_admin
  on public.supports_cours for update
  using (public.is_admin())
  with check (public.is_admin());

create policy supports_cours_delete_admin
  on public.supports_cours for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- examens — même logique.
-- ---------------------------------------------------------------------------

create policy examens_select_self_or_admin
  on public.examens for select
  using (
    public.is_admin()
    or (
      formation_id = public.ma_formation_id()
      and annee_academique_id = public.mon_annee_academique_id()
    )
  );

create policy examens_insert_admin
  on public.examens for insert
  with check (public.is_admin());

create policy examens_update_admin
  on public.examens for update
  using (public.is_admin())
  with check (public.is_admin());

create policy examens_delete_admin
  on public.examens for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- resultats — l'étudiant ne voit que ses propres résultats PUBLIÉS.
-- ---------------------------------------------------------------------------

create policy resultats_select_self_or_admin
  on public.resultats for select
  using (
    public.is_admin()
    or (publie = true and etudiant_id = public.mon_etudiant_id())
  );

create policy resultats_insert_admin
  on public.resultats for insert
  with check (public.is_admin());

create policy resultats_update_admin
  on public.resultats for update
  using (public.is_admin())
  with check (public.is_admin());

create policy resultats_delete_admin
  on public.resultats for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- documents — l'étudiant ne voit que ses propres documents.
-- ---------------------------------------------------------------------------

create policy documents_select_self_or_admin
  on public.documents for select
  using (
    public.is_admin() or etudiant_id = public.mon_etudiant_id()
  );

create policy documents_insert_admin
  on public.documents for insert
  with check (public.is_admin());

create policy documents_update_admin
  on public.documents for update
  using (public.is_admin())
  with check (public.is_admin());

create policy documents_delete_admin
  on public.documents for delete
  using (public.is_admin());

-- ---------------------------------------------------------------------------
-- annonces — visibles si publiées ET (globales ou ciblant la
-- formation/année de l'étudiant).
-- ---------------------------------------------------------------------------

create policy annonces_select_self_or_admin
  on public.annonces for select
  using (
    public.is_admin()
    or (
      publie_le <= now()
      and (formation_id is null or formation_id = public.ma_formation_id())
      and (
        annee_academique_id is null
        or annee_academique_id = public.mon_annee_academique_id()
      )
    )
  );

create policy annonces_insert_admin
  on public.annonces for insert
  with check (public.is_admin());

create policy annonces_update_admin
  on public.annonces for update
  using (public.is_admin())
  with check (public.is_admin());

create policy annonces_delete_admin
  on public.annonces for delete
  using (public.is_admin());
