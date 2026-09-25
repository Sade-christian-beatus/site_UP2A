import "server-only";
import { cache } from "react";
import { redirect } from "next/navigation";
import { createClient } from "@/lib/supabase/server";
import type { Database } from "@/lib/supabase/database.types";

type Profile = Database["public"]["Tables"]["profiles"]["Row"];

/**
 * Couche d'accès aux données (DAL) — centralise les vérifications
 * d'authentification/autorisation "sûres" (proches de la donnée), par
 * opposition à la vérification "optimiste" du proxy
 * (lib/supabase/middleware.ts). Voir docs/01-architecture.md.
 */

export const getSessionUser = cache(async () => {
  const supabase = await createClient();
  const { data, error } = await supabase.auth.getUser();

  if (error || !data.user) {
    return null;
  }

  return data.user;
});

export const getProfile = cache(async (): Promise<Profile | null> => {
  const user = await getSessionUser();
  if (!user) return null;

  const supabase = await createClient();
  const { data, error } = await supabase
    .from("profiles")
    .select("*")
    .eq("id", user.id)
    .single();

  if (error || !data) return null;
  return data;
});

/**
 * À appeler dans app/(app)/layout.tsx. Redirige vers /connexion si non
 * authentifié. Si authentifié mais avec le mauvais rôle (un compte
 * étudiant qui atterrit sur le back-office), redirige vers l'espace
 * étudiant plutôt que vers une route interne : cette app est
 * maintenant mono-rôle (admin uniquement), donc rediriger vers "/"
 * bouclerait indéfiniment (le proxy et cette même fonction
 * réappliqueraient la même redirection). Si l'URL de l'espace étudiant
 * n'est pas configurée, déconnecte plutôt que de boucler.
 */
export async function requireRole(role: Profile["role"]): Promise<Profile> {
  const profile = await getProfile();

  if (!profile) {
    redirect("/connexion");
  }

  if (profile.role !== role) {
    const autreAppUrl = process.env.NEXT_PUBLIC_ESPACE_ETUDIANT_URL;
    if (autreAppUrl) {
      redirect(autreAppUrl);
    }
    const supabase = await createClient();
    await supabase.auth.signOut();
    redirect("/connexion");
  }

  return profile;
}
