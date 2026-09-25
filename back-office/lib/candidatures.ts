import "server-only";
import { createClient } from "@/lib/supabase/server";
import type { StatutCandidature } from "@/lib/constants";
import type { Database } from "@/lib/supabase/database.types";

export type CandidatureListItem = {
  id: string;
  created_at: string;
  nom: string;
  prenom: string;
  email: string;
  telephone: string;
  statut: StatutCandidature;
  formation: { nom: string; slug: string } | null;
};

export type PieceJointe = {
  type?: string;
  label?: string;
  path?: string;
};

export type CandidatureDetail = Database["public"]["Tables"]["candidatures"]["Row"] & {
  formation: { id: string; nom: string; slug: string } | null;
  annee_academique: { id: string; libelle: string } | null;
};

export const STATUTS_CANDIDATURE: StatutCandidature[] = [
  "nouvelle",
  "en_cours",
  "acceptee",
  "refusee",
  "transformee",
];

/**
 * Liste des candidatures, plus récentes en premier. Filtrable par statut
 * (voir app/(app)/page.tsx, onglets). Repose sur la policy RLS
 * `candidatures_select_admin` (voir supabase/migrations/0002_rls.sql) :
 * aucune vérification de rôle ici, la base fait déjà autorité.
 */
export async function listCandidatures(
  statut?: StatutCandidature,
): Promise<CandidatureListItem[]> {
  const supabase = await createClient();
  let query = supabase
    .from("candidatures")
    .select("id, created_at, nom, prenom, email, telephone, statut, formation:formations(nom, slug)")
    .order("created_at", { ascending: false });

  if (statut) {
    query = query.eq("statut", statut);
  }

  const { data, error } = await query;

  if (error || !data) {
    return [];
  }

  return data as unknown as CandidatureListItem[];
}

export async function getCandidature(
  id: string,
): Promise<CandidatureDetail | null> {
  const supabase = await createClient();
  const { data, error } = await supabase
    .from("candidatures")
    .select(
      "*, formation:formations(id, nom, slug), annee_academique:annees_academiques(id, libelle)",
    )
    .eq("id", id)
    .single();

  if (error || !data) {
    return null;
  }

  return data as unknown as CandidatureDetail;
}

/**
 * Génère une URL signée (10 min) pour chaque pièce jointe, pour
 * affichage/téléchargement depuis l'écran détail. Le bucket
 * "candidatures" n'est pas public : ces URLs expirent volontairement
 * vite plutôt que d'être stockées/partagées.
 */
export async function signPiecesJointes(
  pieces: PieceJointe[],
): Promise<Array<PieceJointe & { url: string | null }>> {
  const supabase = await createClient();

  return Promise.all(
    pieces.map(async (piece) => {
      if (!piece.path) {
        return { ...piece, url: null };
      }
      const { data, error } = await supabase.storage
        .from("candidatures")
        .createSignedUrl(piece.path, 60 * 10);

      return { ...piece, url: error ? null : data.signedUrl };
    }),
  );
}
