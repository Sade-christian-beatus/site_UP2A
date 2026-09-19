import { createBrowserClient } from "@supabase/ssr";
import type { Database } from "./database.types";

/**
 * Client Supabase pour les Client Components. N'utilise que la clé
 * `anon` : toute autorisation réelle vient de la RLS côté base, jamais
 * d'une vérification côté navigateur (voir docs/01-architecture.md).
 */
export function createClient() {
  return createBrowserClient<Database>(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
  );
}
