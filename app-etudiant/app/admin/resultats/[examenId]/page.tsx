import Link from "next/link";
import { notFound } from "next/navigation";
import {
  getExamen,
  listEtudiantsFormationAnnee,
  listResultatsExamen,
} from "@/lib/admin/academique";
import { TYPE_EXAMEN_LABELS, formatDateFr } from "@/lib/academique/constants";
import { ResultatsForm } from "./resultats-form";

export default async function ResultatsExamenPage({
  params,
}: {
  params: Promise<{ examenId: string }>;
}) {
  const { examenId } = await params;
  const examen = await getExamen(examenId);
  if (!examen) notFound();

  const [etudiantsBruts, resultats] = await Promise.all([
    listEtudiantsFormationAnnee(examen.formation_id, examen.annee_academique_id),
    listResultatsExamen(examenId),
  ]);

  const resultatsParEtudiant = new Map(resultats.map((r) => [r.etudiant_id, r]));
  const etudiants = etudiantsBruts.map((e) => ({
    id: e.id,
    matricule: e.matricule,
    nom: e.profile?.nom ?? "—",
    prenom: e.profile?.prenom ?? "",
    note: resultatsParEtudiant.get(e.id)?.note ?? null,
    mention: resultatsParEtudiant.get(e.id)?.mention ?? null,
  }));
  const toutesPubliees = resultats.length > 0 && resultats.every((r) => r.publie);

  return (
    <div className="flex flex-col gap-6">
      <div>
        <Link href="/admin/resultats" className="text-sm text-primary hover:underline">
          ← Retour aux examens
        </Link>
        <h1 className="mt-2 font-heading text-2xl text-ink">
          {examen.matiere} — {TYPE_EXAMEN_LABELS[examen.type]}
        </h1>
        <p className="text-sm text-ink-soft">{formatDateFr(examen.date_examen)}</p>
      </div>

      {etudiants.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun étudiant actif dans cette formation/année.
        </p>
      ) : (
        <ResultatsForm
          examenId={examenId}
          etudiants={etudiants}
          toutesPubliees={toutesPubliees}
        />
      )}
    </div>
  );
}
