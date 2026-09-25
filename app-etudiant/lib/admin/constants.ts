import type { Database } from "@/lib/supabase/database.types";

/**
 * Constantes partagées entre code serveur (lib/admin/candidatures.ts,
 * server-only) et composants client (formulaires) — ce fichier ne doit
 * JAMAIS importer "server-only" ni lib/supabase/server, pour rester
 * importable depuis un Client Component.
 */

export type StatutCandidature =
  Database["public"]["Enums"]["statut_candidature"];

export const STATUTS_CANDIDATURE: StatutCandidature[] = [
  "nouvelle",
  "en_cours",
  "acceptee",
  "refusee",
  "transformee",
];

export const STATUT_LABELS: Record<StatutCandidature, string> = {
  nouvelle: "Nouvelle",
  en_cours: "En cours",
  acceptee: "Acceptée",
  refusee: "Refusée",
  transformee: "Transformée",
};
