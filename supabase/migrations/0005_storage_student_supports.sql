-- 0005_storage_student_supports.sql
-- Accès étudiant en lecture aux supports de cours de sa propre
-- formation/année (phase 6, voir docs/03-roadmap.md). Convention de
-- chemin des objets retenue par app-etudiant/lib/admin/academique-actions.ts
-- (createSupport) : "{formation_id}/{annee_academique_id}/{uuid}.{ext}" —
-- storage.foldername() renvoie ces deux segments en [1]/[2].
--
-- Les buckets `candidatures` et `documents-etudiants` restent admin
-- uniquement pour l'instant (voir 0004_storage_policies.sql) : aucune
-- fonctionnalité ne dépose encore de fichier dans `documents-etudiants`
-- avec une convention de chemin arrêtée.

create policy "up2a_etudiant_supports_cours"
on storage.objects for select
using (
  bucket_id = 'supports-cours'
  and (storage.foldername(name))[1] = public.ma_formation_id()::text
  and (storage.foldername(name))[2] = public.mon_annee_academique_id()::text
);
