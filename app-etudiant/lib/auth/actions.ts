"use server";

import { redirect } from "next/navigation";
import { createClient } from "@/lib/supabase/server";

export type LoginFormState = { error: string } | undefined;

export async function login(
  _prevState: LoginFormState,
  formData: FormData,
): Promise<LoginFormState> {
  const email = formData.get("email");
  const password = formData.get("password");

  if (
    typeof email !== "string" ||
    typeof password !== "string" ||
    !email ||
    !password
  ) {
    return { error: "Adresse e-mail et mot de passe requis." };
  }

  const supabase = await createClient();
  const { error } = await supabase.auth.signInWithPassword({
    email,
    password,
  });

  if (error) {
    return { error: "Adresse e-mail ou mot de passe incorrect." };
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
