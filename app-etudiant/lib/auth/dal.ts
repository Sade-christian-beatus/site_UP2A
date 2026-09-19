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
 * À appeler dans le layout d'une section protégée (app/etudiant,
 * app/admin). Redirige vers /connexion si non authentifié, vers / si
 * authentifié mais avec le mauvais rôle.
 */
export async function requireRole(role: Profile["role"]): Promise<Profile> {
  const profile = await getProfile();

  if (!profile) {
    redirect("/connexion");
  }

  if (profile.role !== role) {
    redirect("/");
  }

  return profile;
}
