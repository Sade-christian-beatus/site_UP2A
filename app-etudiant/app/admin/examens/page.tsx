import type { Metadata } from "next";
import Link from "next/link";
import {
  listFormations,
  listAnneesAcademiques,
  listExamens,
  getAnneeCourante,
} from "@/lib/admin/academique";
import { deleteExamen } from "@/lib/admin/academique-actions";
import { FormationAnneeFilter } from "@/lib/admin/formation-annee-filter";
import { DeleteButton } from "@/lib/admin/delete-button";

export const metadata: Metadata = { title: "Examens — Back-office UP-2A" };
import { ExamenForm } from "./examen-form";
import { TYPE_EXAMEN_LABELS, formatDateFr, formatHeure } from "@/lib/academique/constants";

export default async function ExamensPage({
  searchParams,
}: {
  searchParams: Promise<{ formation?: string; annee?: string }>;
}) {
  const { formation, annee } = await searchParams;
  const [formations, annees, anneeCourante] = await Promise.all([
    listFormations(),
    listAnneesAcademiques(),
    getAnneeCourante(),
  ]);

  const formationId = formation ?? formations[0]?.id;
  const anneeId = annee ?? anneeCourante?.id ?? annees[0]?.id;

  if (!formationId || !anneeId) {
    return (
      <p className="text-sm text-ink-soft">
        Aucune formation ou année académique en base — créez-les d&apos;abord
        dans Supabase.
      </p>
    );
  }

  const examens = await listExamens(formationId, anneeId);

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Examens</h1>
        <p className="text-sm text-ink-soft">
          Sessions d&apos;examen par formation et année académique.
        </p>
      </div>

      <FormationAnneeFilter
        formations={formations}
        annees={annees}
        formationId={formationId}
        anneeId={anneeId}
      />

      {examens.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun examen pour cette formation/année.
        </p>
      ) : (
        <div className="overflow-x-auto rounded-lg border border-border">
          <table className="w-full min-w-[640px] text-left text-sm">
            <thead className="bg-surface-alt text-ink-soft">
              <tr>
                <th className="px-4 py-3 font-medium">Date</th>
                <th className="px-4 py-3 font-medium">Matière</th>
                <th className="px-4 py-3 font-medium">Type</th>
                <th className="px-4 py-3 font-medium">Horaire</th>
                <th className="px-4 py-3 font-medium">Salle</th>
                <th className="px-4 py-3 font-medium" />
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {examens.map((e) => (
                <tr key={e.id} className="hover:bg-surface-alt">
                  <td className="px-4 py-3 text-ink">{formatDateFr(e.date_examen)}</td>
                  <td className="px-4 py-3 text-ink">{e.matiere}</td>
                  <td className="px-4 py-3 text-ink-soft">{TYPE_EXAMEN_LABELS[e.type]}</td>
                  <td className="px-4 py-3 text-ink-soft">
                    {formatHeure(e.heure_debut)} – {formatHeure(e.heure_fin)}
                  </td>
                  <td className="px-4 py-3 text-ink-soft">{e.salle ?? "—"}</td>
                  <td className="px-4 py-3">
                    <div className="flex items-center justify-end gap-4">
                      <Link
                        href={`/admin/resultats/${e.id}`}
                        className="text-xs font-medium text-primary hover:underline"
                      >
                        Résultats
                      </Link>
                      <DeleteButton
                        action={() => deleteExamen(e.id)}
                        confirmMessage="Supprimer cet examen ? Les résultats associés seront aussi supprimés."
                      />
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <ExamenForm formationId={formationId} anneeAcademiqueId={anneeId} />
    </div>
  );
}
