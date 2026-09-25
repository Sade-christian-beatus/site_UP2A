import "server-only";
import { createClient as createSupabaseClient } from "@supabase/supabase-js";
import type { Database } from "./database.types";

/**
 * Client Supabase avec la clé `service_role` — bypass la RLS entièrement.
 * `import "server-only"` fait échouer le build si ce fichier est importé
 * depuis du code Client Component. À n'utiliser que pour les opérations
 * que la RLS ne permet structurellement pas au rôle admin authentifié,
 * ex. créer un compte `auth.users` (voir docs/01-architecture.md "Flux 3").
 * Pour tout le reste (lire/écrire candidatures, changer un statut...),
 * utiliser lib/supabase/server.ts (clé anon + RLS) — c'est la RLS qui
 * autorise l'admin, pas ce client.
 */
export function createAdminClient() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const serviceRoleKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

  if (!url || !serviceRoleKey) {
    throw new Error(
      "SUPABASE_SERVICE_ROLE_KEY manquante — voir .env.example.",
    );
  }

  return createSupabaseClient<Database>(url, serviceRoleKey, {
    auth: {
      autoRefreshToken: false,
      persistSession: false,
    },
  });
}
