"use server";

import { redirect } from "next/navigation";
import { createClient } from "@/lib/supabase/server";

export type LoginFormState = { error: string } | undefined;

const ERREUR_IDENTIFIANTS = "Matricule ou mot de passe incorrect.";

/**
 * Connexion par matricule plutôt que par e-mail (voir
 * docs/06-storyboard.md "Login étudiant par matricule") : le SMTP du
 * projet n'étant pas configuré, l'e-mail de l'étudiant reste un
 * identifiant Supabase Auth interne, jamais ce que l'étudiant utilise
 * pour se connecter. `resolve_etudiant_email` (migration Supabase
 * 0006) résout le matricule vers cet e-mail interne avant l'appel
 * standard `signInWithPassword`. Le message d'erreur reste le même,
 * que le matricule soit inconnu ou le mot de passe faux — ne jamais
 * révéler laquelle des deux informations est incorrecte.
 */
export async function login(
  _prevState: LoginFormState,
  formData: FormData,
): Promise<LoginFormState> {
  const matricule = formData.get("matricule");
  const password = formData.get("password");

  if (
    typeof matricule !== "string" ||
    typeof password !== "string" ||
    !matricule ||
    !password
  ) {
    return { error: "Matricule et mot de passe requis." };
  }

  const supabase = await createClient();

  const { data: email, error: resolveError } = await supabase.rpc(
    "resolve_etudiant_email",
    { p_matricule: matricule.trim() },
  );

  if (resolveError || !email) {
    return { error: ERREUR_IDENTIFIANTS };
  }

  const { error } = await supabase.auth.signInWithPassword({
    email,
    password,
  });

  if (error) {
    return { error: ERREUR_IDENTIFIANTS };
  }

  redirect("/");
}

export async function logout() {
  const supabase = await createClient();
  await supabase.auth.signOut();
  redirect("/connexion");
}

export type ChangerMotDePasseState = { error: string } | { success: true } | undefined;

/**
 * Changement de mot de passe self-service — utile en particulier pour
 * remplacer le mot de passe temporaire généré par l'admin lors de la
 * transformation de candidature (voir back-office/lib/actions.ts). La
 * session déjà authentifiée suffit comme garantie côté Supabase Auth,
 * pas besoin de redemander l'ancien mot de passe.
 */
export async function changerMotDePasse(
  _prevState: ChangerMotDePasseState,
  formData: FormData,
): Promise<ChangerMotDePasseState> {
  const motDePasse = formData.get("mot_de_passe");
  const confirmation = formData.get("confirmation");

  if (typeof motDePasse !== "string" || motDePasse.length < 8) {
    return { error: "Le mot de passe doit contenir au moins 8 caractères." };
  }
  if (motDePasse !== confirmation) {
    return { error: "Les deux mots de passe ne correspondent pas." };
  }

  const supabase = await createClient();
  const { error } = await supabase.auth.updateUser({ password: motDePasse });

  if (error) {
    return { error: "Impossible de mettre à jour le mot de passe." };
  }

  return { success: true };
}
