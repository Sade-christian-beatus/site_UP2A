"use server";

import { revalidatePath } from "next/cache";
import { requireRole } from "@/lib/auth/dal";
import { createClient } from "@/lib/supabase/server";
import { createAdminClient } from "@/lib/supabase/admin";
import { getCandidature } from "@/lib/candidatures";
import type { Database } from "@/lib/supabase/database.types";

type StatutCandidature = Database["public"]["Enums"]["statut_candidature"];

export type ActionState = { error: string } | { success: true } | undefined;

export type TransformerActionState =
  | { error: string }
  | { success: true; matricule: string; motDePasse: string }
  | undefined;

/**
 * Mot de passe temporaire lisible (évite les caractères ambigus 0/O,
 * 1/l/I) — l'étudiant le change dès sa première connexion via
 * "Changer mon mot de passe" côté espace étudiant. Généré côté serveur
 * uniquement, jamais stocké en clair (Supabase Auth le hashe).
 */
function genererMotDePasse(): string {
  const alphabet = "ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789";
  const octets = new Uint8Array(10);
  crypto.getRandomValues(octets);
  return Array.from(octets, (o) => alphabet[o % alphabet.length]).join("");
}

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
 *   1. Crée le compte `auth.users` avec un mot de passe temporaire
 *      généré côté serveur (`admin.auth.admin.createUser`, email déjà
 *      confirmé) — nécessite service_role, seule étape qui l'utilise.
 *      Pas d'e-mail d'invitation : le SMTP du projet n'est pas encore
 *      configuré (voir wordpress/README.md), un compte qui dépend d'un
 *      e-mail non livré serait inutilisable. Le mot de passe est
 *      retourné une seule fois à l'admin (jamais stocké en clair côté
 *      applicatif) pour qu'il le communique à l'étudiant par un canal de
 *      son choix (téléphone, en personne...) ; l'étudiant le change dès
 *      sa première connexion via "Changer mon mot de passe".
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
): Promise<TransformerActionState> {
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

  const motDePasse = genererMotDePasse();
  const admin = createAdminClient();
  const { data: created, error: createError } = await admin.auth.admin.createUser({
    email: candidature.email,
    password: motDePasse,
    email_confirm: true,
    user_metadata: { nom: candidature.nom, prenom: candidature.prenom },
  });

  if (createError || !created.user) {
    return {
      error: "Impossible de créer le compte (adresse e-mail déjà utilisée ?).",
    };
  }

  const supabase = await createClient();

  const { error: profileError } = await supabase.from("profiles").insert({
    id: created.user.id,
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
    profile_id: created.user.id,
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
  return { success: true, matricule, motDePasse };
}
