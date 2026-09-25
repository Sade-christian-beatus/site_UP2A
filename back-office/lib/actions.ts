"use server";

import { revalidatePath } from "next/cache";
import { requireRole } from "@/lib/auth/dal";
import { createClient } from "@/lib/supabase/server";
import { createAdminClient } from "@/lib/supabase/admin";
import { getCandidature } from "@/lib/candidatures";
import type { Database } from "@/lib/supabase/database.types";

type StatutCandidature = Database["public"]["Enums"]["statut_candidature"];

export type ActionState = { error: string } | { success: true } | undefined;

/**
 * Change le statut d'une candidature. Repose sur la policy RLS
 * `candidatures_update_admin` (client `anon` + session admin, pas de
 * service_role nécessaire ici — voir docs/01-architecture.md "Flux 3").
 */
export async function updateCandidatureStatut(
  candidatureId: string,
  statut: StatutCandidature,
): Promise<ActionState> {
  await requireRole("admin");

  const supabase = await createClient();
  const { error } = await supabase
    .from("candidatures")
    .update({ statut })
    .eq("id", candidatureId);

  if (error) {
    return { error: "Impossible de mettre à jour le statut." };
  }

  revalidatePath("/");
  revalidatePath(`/candidatures/${candidatureId}`);
  return { success: true };
}

/**
 * Construit un matricule "UP2A-{année}-{numéro séquentiel}" à partir du
 * libellé de l'année académique (ex. "2026-2027" -> "2026"). Le compteur
 * est global à l'année (pas par formation) : convention volontairement
 * simple en l'absence de format imposé par le client — à ajuster
 * facilement ici si une autre convention est demandée plus tard.
 */
async function genererMatricule(
  supabase: Awaited<ReturnType<typeof createClient>>,
  anneeAcademiqueId: string,
  anneeLibelle: string,
): Promise<string> {
  const { count } = await supabase
    .from("etudiants")
    .select("id", { count: "exact", head: true })
    .eq("annee_academique_id", anneeAcademiqueId);

  const annee = anneeLibelle.split("-")[0] || anneeLibelle;
  const sequence = String((count ?? 0) + 1).padStart(4, "0");
  return `UP2A-${annee}-${sequence}`;
}

/**
 * Transforme une candidature acceptée en compte étudiant (voir
 * docs/01-architecture.md "Flux 3") :
 *   1. Invite le candidat par e-mail (auth.users + lien pour choisir son
 *      mot de passe) — nécessite service_role, seule étape qui l'utilise.
 *   2. Crée sa ligne `profiles` (role=etudiant) et `etudiants`
 *      (matricule généré, statut=actif) avec le client admin authentifié
 *      normal (RLS `*_insert_admin`), pas service_role.
 *   3. Marque la candidature `transformee`.
 * Étapes 2-3 sont exécutées séquentiellement (pas de transaction
 * multi-table via l'API REST Supabase) : si l'une échoue après la
 * création du compte auth, l'erreur est renvoyée sans retenter — cas
 * rare pour une action admin supervisée, à traiter manuellement dans le
 * dashboard Supabase si besoin (voir README "Dépannage").
 */
export async function transformerEnEtudiant(
  candidatureId: string,
): Promise<ActionState> {
  await requireRole("admin");

  const candidature = await getCandidature(candidatureId);
  if (!candidature) {
    return { error: "Candidature introuvable." };
  }
  if (candidature.statut !== "acceptee") {
    return {
      error: "Seule une candidature au statut \"Acceptée\" peut être transformée.",
    };
  }
  if (!candidature.formation || !candidature.annee_academique) {
    return { error: "Formation ou année académique introuvable pour cette candidature." };
  }

  const admin = createAdminClient();
  const { data: invited, error: inviteError } =
    await admin.auth.admin.inviteUserByEmail(candidature.email, {
      data: { nom: candidature.nom, prenom: candidature.prenom },
    });

  if (inviteError || !invited.user) {
    return {
      error:
        "Impossible de créer le compte (e-mail déjà utilisé ou service e-mail indisponible).",
    };
  }

  const supabase = await createClient();

  const { error: profileError } = await supabase.from("profiles").insert({
    id: invited.user.id,
    role: "etudiant",
    nom: candidature.nom,
    prenom: candidature.prenom,
    telephone: candidature.telephone,
  });

  if (profileError) {
    return { error: "Compte créé mais échec de la création du profil — voir le dashboard Supabase." };
  }

  const matricule = await genererMatricule(
    supabase,
    candidature.annee_academique.id,
    candidature.annee_academique.libelle,
  );

  const { error: etudiantError } = await supabase.from("etudiants").insert({
    profile_id: invited.user.id,
    candidature_id: candidature.id,
    matricule,
    formation_id: candidature.formation.id,
    annee_academique_id: candidature.annee_academique.id,
    statut: "actif",
  });

  if (etudiantError) {
    return { error: "Profil créé mais échec de la création du dossier étudiant — voir le dashboard Supabase." };
  }

  const { error: statutError } = await supabase
    .from("candidatures")
    .update({ statut: "transformee" })
    .eq("id", candidature.id);

  if (statutError) {
    return { error: "Étudiant créé mais échec de la mise à jour du statut de la candidature." };
  }

  revalidatePath("/");
  revalidatePath(`/candidatures/${candidatureId}`);
  return { success: true };
}
