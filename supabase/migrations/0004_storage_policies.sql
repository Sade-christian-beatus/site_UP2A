-- 0004_storage_policies.sql
-- Politiques RLS sur storage.objects pour les buckets déclarés dans
-- supabase/config.toml (candidatures, supports-cours, documents-etudiants).
--
-- Volontairement restreint à l'admin pour l'instant : l'upload réel
-- (formulaire de préinscription depuis WordPress) passe par service_role
-- côté serveur, qui bypass la RLS. L'accès étudiant fin (lire ses propres
-- supports de cours / documents) sera ajouté en phase 6 une fois la
-- convention de chemin des objets (ex. "{formation_id}/{annee_id}/...")
-- arrêtée avec les écrans réels — inutile de la deviner maintenant.
--
-- storage.objects a RLS activée par défaut sur tout projet Supabase ; pas
-- besoin de le refaire ici.

create policy "up2a_admin_candidatures"
on storage.objects for all
using (bucket_id = 'candidatures' and public.is_admin())
with check (bucket_id = 'candidatures' and public.is_admin());

create policy "up2a_admin_supports_cours"
on storage.objects for all
using (bucket_id = 'supports-cours' and public.is_admin())
with check (bucket_id = 'supports-cours' and public.is_admin());

create policy "up2a_admin_documents_etudiants"
on storage.objects for all
using (bucket_id = 'documents-etudiants' and public.is_admin())
with check (bucket_id = 'documents-etudiants' and public.is_admin());
