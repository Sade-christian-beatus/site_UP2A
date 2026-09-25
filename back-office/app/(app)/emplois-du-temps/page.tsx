import type { Metadata } from "next";
import {
  listFormations,
  listAnneesAcademiques,
  listEmploisDuTemps,
  getAnneeCourante,
} from "@/lib/academique";
import { deleteCreneau } from "@/lib/academique-actions";
import { FormationAnneeFilter } from "@/lib/formation-annee-filter";
import { DeleteButton } from "@/lib/delete-button";
import { CreneauForm } from "./creneau-form";

export const metadata: Metadata = { title: "Emplois du temps — Back-office UP-2A" };
import { JOURS_SEMAINE, JOUR_LABELS, formatHeure } from "@/lib/academique/constants";

export default async function EmploisDuTempsPage({
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

  const creneaux = await listEmploisDuTemps(formationId, anneeId);

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Emploi du temps</h1>
        <p className="text-sm text-ink-soft">
          Créneaux hebdomadaires par formation et année académique.
        </p>
      </div>

      <FormationAnneeFilter
        formations={formations}
        annees={annees}
        formationId={formationId}
        anneeId={anneeId}
      />

      {creneaux.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun créneau pour cette formation/année.
        </p>
      ) : (
        <div className="overflow-x-auto rounded-lg border border-border">
          <table className="w-full min-w-[640px] text-left text-sm">
            <thead className="bg-surface-alt text-ink-soft">
              <tr>
                <th className="px-4 py-3 font-medium">Jour</th>
                <th className="px-4 py-3 font-medium">Horaire</th>
                <th className="px-4 py-3 font-medium">Matière</th>
                <th className="px-4 py-3 font-medium">Enseignant</th>
                <th className="px-4 py-3 font-medium">Salle</th>
                <th className="px-4 py-3 font-medium" />
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {[...creneaux]
                .sort(
                  (a, b) =>
                    JOURS_SEMAINE.indexOf(a.jour_semaine) -
                      JOURS_SEMAINE.indexOf(b.jour_semaine) ||
                    a.heure_debut.localeCompare(b.heure_debut),
                )
                .map((c) => (
                  <tr key={c.id} className="hover:bg-surface-alt">
                    <td className="px-4 py-3 font-medium text-ink">
                      {JOUR_LABELS[c.jour_semaine]}
                    </td>
                    <td className="px-4 py-3 text-ink-soft">
                      {formatHeure(c.heure_debut)} – {formatHeure(c.heure_fin)}
                    </td>
                    <td className="px-4 py-3 text-ink">{c.matiere}</td>
                    <td className="px-4 py-3 text-ink-soft">{c.enseignant ?? "—"}</td>
                    <td className="px-4 py-3 text-ink-soft">{c.salle ?? "—"}</td>
                    <td className="px-4 py-3 text-right">
                      <DeleteButton
                        action={() => deleteCreneau(c.id)}
                        confirmMessage="Supprimer ce créneau ?"
                      />
                    </td>
                  </tr>
                ))}
            </tbody>
          </table>
        </div>
      )}

      <CreneauForm formationId={formationId} anneeAcademiqueId={anneeId} />
    </div>
  );
}
