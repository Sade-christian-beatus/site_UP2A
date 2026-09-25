import "server-only";
import { createClient } from "@/lib/supabase/server";
import { getSessionUser } from "@/lib/auth/dal";
import type { Database } from "@/lib/supabase/database.types";

/**
 * DAL espace étudiant (phase 6) : lecture seule, filtrée par RLS.
 * Contrairement à lib/admin/academique.ts, la plupart des requêtes ici
 * n'ont pas besoin de `.eq("formation_id", ...)` explicite : les policies
 * `*_select_self_or_admin` (voir supabase/migrations/0002_rls.sql)
 * comparent déjà `formation_id`/`annee_academique_id` à
 * `ma_formation_id()`/`mon_annee_academique_id()`, donc un simple
 * `select("*")` ne renvoie déjà que les lignes de l'étudiant connecté.
 */

export type MonEtudiant = {
  id: string;
  matricule: string;
  statut: Database["public"]["Enums"]["statut_etudiant"];
  formation: { id: string; nom: string; slug: string } | null;
  annee_academique: { id: string; libelle: string } | null;
};

export async function getMonEtudiant(): Promise<MonEtudiant | null> {
  const user = await getSessionUser();
  if (!user) return null;

  const supabase = await createClient();
  const { data, error } = await supabase
    .from("etudiants")
    .select(
      "id, matricule, statut, formation:formations(id, nom, slug), annee_academique:annees_academiques(id, libelle)",
    )
    .eq("profile_id", user.id)
    .single();

  if (error || !data) return null;
  return data as unknown as MonEtudiant;
}

export type MonCreneau = Database["public"]["Tables"]["emplois_du_temps"]["Row"];

export async function getMesCreneaux(): Promise<MonCreneau[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("emplois_du_temps")
    .select("*")
    .order("heure_debut");
  return data ?? [];
}

export type MonExamen = Database["public"]["Tables"]["examens"]["Row"];

export async function getMesExamens(): Promise<MonExamen[]> {
  const supabase = await createClient();
  const { data } = await supabase.from("examens").select("*").order("date_examen");
  return data ?? [];
}

export type MonResultat = Database["public"]["Tables"]["resultats"]["Row"] & {
  examen: {
    matiere: string;
    type: Database["public"]["Enums"]["type_examen"];
    date_examen: string;
  } | null;
};

/** RLS ne renvoie déjà que les résultats publiés de l'étudiant connecté. */
export async function getMesResultats(): Promise<MonResultat[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("resultats")
    .select("*, examen:examens(matiere, type, date_examen)")
    .order("created_at", { ascending: false });
  return (data as unknown as MonResultat[]) ?? [];
}

export type MonSupport = Database["public"]["Tables"]["supports_cours"]["Row"] & {
  url: string | null;
};

export async function getMesSupports(): Promise<MonSupport[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("supports_cours")
    .select("*")
    .order("published_at", { ascending: false });

  return Promise.all(
    (data ?? []).map(async (s) => {
      const { data: signed } = await supabase.storage
        .from("supports-cours")
        .createSignedUrl(s.fichier_url, 60 * 10);
      return { ...s, url: signed?.signedUrl ?? null };
    }),
  );
}

export type MonDocument = Database["public"]["Tables"]["documents"]["Row"] & {
  url: string | null;
};

export async function getMesDocuments(): Promise<MonDocument[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("documents")
    .select("*")
    .order("genere_le", { ascending: false });

  return Promise.all(
    (data ?? []).map(async (d) => {
      const { data: signed } = await supabase.storage
        .from("documents-etudiants")
        .createSignedUrl(d.fichier_url, 60 * 10);
      return { ...d, url: signed?.signedUrl ?? null };
    }),
  );
}

export type MonAnnonce = Database["public"]["Tables"]["annonces"]["Row"];

export async function getMesAnnonces(): Promise<MonAnnonce[]> {
  const supabase = await createClient();
  const { data } = await supabase
    .from("annonces")
    .select("*")
    .order("publie_le", { ascending: false });
  return data ?? [];
}
