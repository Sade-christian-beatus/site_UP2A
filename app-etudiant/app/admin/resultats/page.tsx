import Link from "next/link";
import {
  listFormations,
  listAnneesAcademiques,
  listExamens,
  getAnneeCourante,
} from "@/lib/admin/academique";
import { FormationAnneeFilter } from "@/lib/admin/formation-annee-filter";
import { TYPE_EXAMEN_LABELS, formatDateFr } from "@/lib/academique/constants";

export default async function ResultatsPage({
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
        <h1 className="font-heading text-2xl text-ink">Résultats</h1>
        <p className="text-sm text-ink-soft">
          Choisissez un examen pour saisir ou publier ses résultats.
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
          Aucun examen pour cette formation/année — créez-en un dans
          l&apos;onglet Examens.
        </p>
      ) : (
        <ul className="flex flex-col divide-y divide-border rounded-lg border border-border">
          {examens.map((e) => (
            <li key={e.id}>
              <Link
                href={`/admin/resultats/${e.id}`}
                className="flex items-center justify-between px-4 py-3 text-sm hover:bg-surface-alt"
              >
                <span className="text-ink">
                  {e.matiere}{" "}
                  <span className="text-ink-soft">
                    ({TYPE_EXAMEN_LABELS[e.type]})
                  </span>
                </span>
                <span className="text-ink-soft">{formatDateFr(e.date_examen)}</span>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
