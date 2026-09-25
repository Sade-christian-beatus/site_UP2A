import type { Database } from "@/lib/supabase/database.types";

type JourSemaine = Database["public"]["Enums"]["jour_semaine"];
type TypeExamen = Database["public"]["Enums"]["type_examen"];
type TypeDocument = Database["public"]["Enums"]["type_document"];

/**
 * Constantes académiques partagées entre l'espace étudiant et le
 * back-office (labels FR, ordre d'affichage). Volontairement pas
 * "server-only" : importées depuis des Client Components (formulaires).
 */

export const JOURS_SEMAINE: JourSemaine[] = [
  "lundi",
  "mardi",
  "mercredi",
  "jeudi",
  "vendredi",
  "samedi",
];

export const JOUR_LABELS: Record<JourSemaine, string> = {
  lundi: "Lundi",
  mardi: "Mardi",
  mercredi: "Mercredi",
  jeudi: "Jeudi",
  vendredi: "Vendredi",
  samedi: "Samedi",
};

export const TYPES_EXAMEN: TypeExamen[] = ["partiel", "final", "rattrapage"];

export const TYPE_EXAMEN_LABELS: Record<TypeExamen, string> = {
  partiel: "Partiel",
  final: "Final",
  rattrapage: "Rattrapage",
};

export const TYPES_DOCUMENT: TypeDocument[] = [
  "releve_notes",
  "certificat_scolarite",
  "attestation",
  "autre",
];

export const TYPE_DOCUMENT_LABELS: Record<TypeDocument, string> = {
  releve_notes: "Relevé de notes",
  certificat_scolarite: "Certificat de scolarité",
  attestation: "Attestation",
  autre: "Autre document",
};

export function formatDateFr(iso: string): string {
  return new Date(iso).toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

export function formatHeure(time: string): string {
  return time.slice(0, 5);
}
