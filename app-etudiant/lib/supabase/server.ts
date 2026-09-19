import { createServerClient } from "@supabase/ssr";
import { cookies } from "next/headers";
import type { Database } from "./database.types";

/**
 * Client Supabase pour les Server Components / Server Actions / Route
 * Handlers. Clé `anon` uniquement — la clé `service_role` ne doit jamais
 * transiter par ce fichier (voir docs/04-conventions.md).
 */
export async function createClient() {
  const cookieStore = await cookies();

  return createServerClient<Database>(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
    {
      cookies: {
        getAll() {
          return cookieStore.getAll();
        },
        setAll(cookiesToSet) {
          try {
            cookiesToSet.forEach(({ name, value, options }) =>
              cookieStore.set(name, value, options),
            );
          } catch {
            // Appelé depuis un Server Component : sans effet, la session
            // est déjà rafraîchie par le proxy (voir lib/supabase/middleware.ts).
          }
        },
      },
    },
  );
}
