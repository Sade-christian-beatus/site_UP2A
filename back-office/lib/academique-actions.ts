"use server";

import { revalidatePath } from "next/cache";
import { requireRole } from "@/lib/auth/dal";
import { createClient } from "@/lib/supabase/server";
import type { Database } from "@/lib/supabase/database.types";

export type ActionState = { error: string } | { success: true } | undefined;

type JourSemaine = Database["public"]["Enums"]["jour_semaine"];
type TypeExamen = Database["public"]["Enums"]["type_examen"];

function field(formData: FormData, key: string): string {
  return String(formData.get(key) ?? "").trim();
}

function optionalField(formData: FormData, key: string): string | null {
  const value = field(formData, key);
  return value === "" ? null : value;
}

// ---------------------------------------------------------------------------
// Emplois du temps
// ---------------------------------------------------------------------------

function creneauPayload(formData: FormData) {
  return {
    formation_id: field(formData, "formation_id"),
    annee_academique_id: field(formData, "annee_academique_id"),
    jour_semaine: field(formData, "jour_semaine") as JourSemaine,
    heure_debut: field(formData, "heure_debut"),
    heure_fin: field(formData, "heure_fin"),
    matiere: field(formData, "matiere"),
    enseignant: optionalField(formData, "enseignant"),
    salle: optionalField(formData, "salle"),
  };
}

export async function createCreneau(formData: FormData): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();
  const payload = creneauPayload(formData);

  const { error } = await supabase.from("emplois_du_temps").insert(payload);
  if (error) return { error: "Impossible de créer le créneau." };

  revalidatePath("/emplois-du-temps");
  return { success: true };
}

export async function deleteCreneau(id: string): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();
  const { error } = await supabase.from("emplois_du_temps").delete().eq("id", id);
  if (error) return { error: "Impossible de supprimer le créneau." };

  revalidatePath("/emplois-du-temps");
  return { success: true };
}

// ---------------------------------------------------------------------------
// Examens
// ---------------------------------------------------------------------------

function examenPayload(formData: FormData) {
  return {
    formation_id: field(formData, "formation_id"),
    annee_academique_id: field(formData, "annee_academique_id"),
    matiere: field(formData, "matiere"),
    type: field(formData, "type") as TypeExamen,
    date_examen: field(formData, "date_examen"),
    heure_debut: field(formData, "heure_debut"),
    heure_fin: field(formData, "heure_fin"),
    salle: optionalField(formData, "salle"),
  };
}

export async function createExamen(formData: FormData): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();
  const payload = examenPayload(formData);

  const { error } = await supabase.from("examens").insert(payload);
  if (error) return { error: "Impossible de créer l'examen." };

  revalidatePath("/examens");
  return { success: true };
}

export async function deleteExamen(id: string): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();
  const { error } = await supabase.from("examens").delete().eq("id", id);
  if (error) return { error: "Impossible de supprimer l'examen." };

  revalidatePath("/examens");
  return { success: true };
}

// ---------------------------------------------------------------------------
// Résultats — saisie groupée par examen (voir app/(app)/resultats/[examenId]).
// Le formulaire poste un champ `note_{etudiantId}` (et `mention_{etudiantId}`)
// par étudiant listé sur la page ; les champs vides sont ignorés plutôt que
// d'écrire une note à 0, pour ne pas fabriquer un résultat non saisi.
// ---------------------------------------------------------------------------

export async function upsertResultats(
  examenId: string,
  etudiantIds: string[],
  formData: FormData,
): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();

  const rows = etudiantIds
    .map((etudiantId) => {
      const noteRaw = field(formData, `note_${etudiantId}`);
      if (noteRaw === "") return null;
      const note = Number(noteRaw);
      if (Number.isNaN(note)) return null;
      return {
        etudiant_id: etudiantId,
        examen_id: examenId,
        note,
        mention: optionalField(formData, `mention_${etudiantId}`),
      };
    })
    .filter((row): row is NonNullable<typeof row> => row !== null);

  if (rows.length === 0) {
    return { error: "Aucune note saisie." };
  }

  const { error } = await supabase
    .from("resultats")
    .upsert(rows, { onConflict: "etudiant_id,examen_id" });

  if (error) return { error: "Impossible d'enregistrer les résultats." };

  revalidatePath(`/resultats/${examenId}`);
  return { success: true };
}

export async function setResultatsPublication(
  examenId: string,
  publie: boolean,
): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();

  const { error } = await supabase
    .from("resultats")
    .update({ publie })
    .eq("examen_id", examenId);

  if (error) return { error: "Impossible de mettre à jour la publication." };

  revalidatePath(`/resultats/${examenId}`);
  return { success: true };
}

// ---------------------------------------------------------------------------
// Supports de cours — upload vers le bucket Storage `supports-cours` avec
// le client anon+session admin (la policy storage `up2a_admin_supports_cours`
// autorise l'admin authentifié, pas besoin de service_role ici — voir
// supabase/migrations/0004_storage_policies.sql).
// ---------------------------------------------------------------------------

export async function createSupport(formData: FormData): Promise<ActionState> {
  const profile = await requireRole("admin");
  const supabase = await createClient();

  const formationId = field(formData, "formation_id");
  const anneeAcademiqueId = field(formData, "annee_academique_id");
  const matiere = field(formData, "matiere");
  const titre = field(formData, "titre");
  const description = optionalField(formData, "description");
  const fichier = formData.get("fichier");

  if (!(fichier instanceof File) || fichier.size === 0) {
    return { error: "Merci de sélectionner un fichier." };
  }

  const ext = fichier.name.split(".").pop() ?? "pdf";
  const path = `${formationId}/${anneeAcademiqueId}/${crypto.randomUUID()}.${ext}`;

  const { error: uploadError } = await supabase.storage
    .from("supports-cours")
    .upload(path, fichier, { contentType: fichier.type || undefined });

  if (uploadError) {
    return { error: "Échec de l'envoi du fichier." };
  }

  const { error: insertError } = await supabase.from("supports_cours").insert({
    formation_id: formationId,
    annee_academique_id: anneeAcademiqueId,
    matiere,
    titre,
    description,
    fichier_url: path,
    created_by: profile.id,
  });

  if (insertError) {
    await supabase.storage.from("supports-cours").remove([path]);
    return { error: "Impossible d'enregistrer le support." };
  }

  revalidatePath("/supports-cours");
  return { success: true };
}

export async function deleteSupport(id: string): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();

  const { data: support } = await supabase
    .from("supports_cours")
    .select("fichier_url")
    .eq("id", id)
    .single();

  const { error } = await supabase.from("supports_cours").delete().eq("id", id);
  if (error) return { error: "Impossible de supprimer le support." };

  if (support?.fichier_url) {
    await supabase.storage.from("supports-cours").remove([support.fichier_url]);
  }

  revalidatePath("/supports-cours");
  return { success: true };
}

// ---------------------------------------------------------------------------
// Annonces
// ---------------------------------------------------------------------------

function annoncePayload(formData: FormData) {
  return {
    titre: field(formData, "titre"),
    contenu: field(formData, "contenu"),
    formation_id: optionalField(formData, "formation_id"),
    annee_academique_id: optionalField(formData, "annee_academique_id"),
  };
}

export async function createAnnonce(formData: FormData): Promise<ActionState> {
  const profile = await requireRole("admin");
  const supabase = await createClient();

  const { error } = await supabase
    .from("annonces")
    .insert({ ...annoncePayload(formData), created_by: profile.id });

  if (error) return { error: "Impossible de publier l'annonce." };

  revalidatePath("/annonces");
  return { success: true };
}

export async function deleteAnnonce(id: string): Promise<ActionState> {
  await requireRole("admin");
  const supabase = await createClient();
  const { error } = await supabase.from("annonces").delete().eq("id", id);
  if (error) return { error: "Impossible de supprimer l'annonce." };

  revalidatePath("/annonces");
  return { success: true };
}
