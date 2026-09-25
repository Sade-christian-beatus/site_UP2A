import "server-only";
import { createClient } from "@/lib/supabase/server";
import type { Database } from "@/lib/supabase/database.types";

/**
 * DAL back-office pour la saisie académique (phase 7, reste du MVP) :
 * emplois du temps, examens, résultats, supports de cours, annonces.
 * Même logique que lib/admin/candidatures.ts : repose sur les policies
 * RLS `*_select_admin`/`*_insert_admin`/etc. (voir
 * supabase/migrations/0002_rls.sql), pas de vérification de rôle ici —
 * la base fait déjà autorité. requireRole("admin") reste appelé dans les
 * Server Actions qui mutent (lib/admin/academique-actions.ts).
 */

export type Formation = Database["public"]["Tables"]["formations"]["Row"];
export type AnneeAcademique =
  Database["public"]["Tables"]["annees_academiques"]["Row"];
export type Creneau = Database["public"]["Tables"]["emplois_du_temps"]["Row"];
export type Support = Database["public"]["Tables"]["supports_cours"]["Row"];
export type Examen = Database["public"]["Tables"]["examens"]["Row"];
export type Annonce = Database["public"]["Tables"]["annonces"]["Row"];
export type EtudiantAvecProfil = {
  id: string;
  matricule: string;
  profile: { nom: string; prenom: string } | null;
};
export type Resultat = Database["public"]["Tables"]["resultats"]["Row"];

export async function listFormations(): Promise<Formation[]> {
  const supabase = await createClient();
  const { data } = await supabase.from("formations").select("*").order("nom");
  return data ?? [];
}

export async function listAnneesAcademiques(): Promise<AnneeAcademique[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("annees_academiques")
    .select("*")
    .order("libelle", { ascending: false });
  return data ?? [];
}

/** Année académique courante, ou la plus récente si aucune n'est marquée courante. */
export async function getAnneeCourante(): Promise<AnneeAcademique | null> {
  const annees = await listAnneesAcademiques();
  return annees.find((a) => a.est_courante) ?? annees[0] ?? null;
}

export async function listEmploisDuTemps(
  formationId: string,
  anneeAcademiqueId: string,
): Promise<Creneau[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("emplois_du_temps")
    .select("*")
    .eq("formation_id", formationId)
    .eq("annee_academique_id", anneeAcademiqueId)
    .order("heure_debut");
  return data ?? [];
}

export async function listExamens(
  formationId: string,
  anneeAcademiqueId: string,
): Promise<Examen[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("examens")
    .select("*")
    .eq("formation_id", formationId)
    .eq("annee_academique_id", anneeAcademiqueId)
    .order("date_examen", { ascending: false });
  return data ?? [];
}

export async function getExamen(id: string): Promise<Examen | null> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("examens")
    .select("*")
    .eq("id", id)
    .single();
  return data ?? null;
}

export async function listSupports(
  formationId: string,
  anneeAcademiqueId: string,
): Promise<Support[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("supports_cours")
    .select("*")
    .eq("formation_id", formationId)
    .eq("annee_academique_id", anneeAcademiqueId)
    .order("published_at", { ascending: false });
  return data ?? [];
}

export async function listAnnonces(): Promise<
  Array<Annonce & { formation: { nom: string } | null; annee_academique: { libelle: string } | null }>
> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("annonces")
    .select("*, formation:formations(nom), annee_academique:annees_academiques(libelle)")
    .order("publie_le", { ascending: false });
  return (
    (data as unknown as Array<
      Annonce & { formation: { nom: string } | null; annee_academique: { libelle: string } | null }
    >) ?? []
  );
}

/**
 * Étudiants actifs d'une formation/année, avec nom/prénom (jointure sur
 * profiles) — utilisé pour la saisie groupée des résultats.
 */
export async function listEtudiantsFormationAnnee(
  formationId: string,
  anneeAcademiqueId: string,
): Promise<EtudiantAvecProfil[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("etudiants")
    .select("id, matricule, profile:profiles(nom, prenom)")
    .eq("formation_id", formationId)
    .eq("annee_academique_id", anneeAcademiqueId)
    .order("matricule");
  return (data as unknown as EtudiantAvecProfil[]) ?? [];
}

export async function listResultatsExamen(
  examenId: string,
): Promise<Resultat[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("resultats")
    .select("*")
    .eq("examen_id", examenId);
  return data ?? [];
}

/**
 * URL signée (10 min) pour un fichier d'un bucket privé — mêmes buckets
 * que ceux déclarés dans supabase/config.toml. Réutilisée pour les
 * supports de cours (admin + étudiant) et les documents étudiant.
 */
export async function signStorageUrl(
  bucket: "supports-cours" | "documents-etudiants",
  path: string,
): Promise<string | null> {
  const supabase = await createClient();
  const { data, error } = await supabase.storage
    .from(bucket)
    .createSignedUrl(path, 60 * 10);
  return error ? null : data.signedUrl;
}
