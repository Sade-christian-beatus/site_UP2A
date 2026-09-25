import {
  listFormations,
  listAnneesAcademiques,
  listSupports,
  getAnneeCourante,
  signStorageUrl,
} from "@/lib/admin/academique";
import { deleteSupport } from "@/lib/admin/academique-actions";
import { FormationAnneeFilter } from "@/lib/admin/formation-annee-filter";
import { DeleteButton } from "@/lib/admin/delete-button";
import { SupportForm } from "./support-form";
import { formatDateFr } from "@/lib/academique/constants";

export default async function SupportsCoursPage({
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

  const supports = await listSupports(formationId, anneeId);
  const supportsAvecUrl = await Promise.all(
    supports.map(async (s) => ({
      ...s,
      url: await signStorageUrl("supports-cours", s.fichier_url),
    })),
  );

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Supports de cours</h1>
        <p className="text-sm text-ink-soft">
          Documents pédagogiques par formation et année académique.
        </p>
      </div>

      <FormationAnneeFilter
        formations={formations}
        annees={annees}
        formationId={formationId}
        anneeId={anneeId}
      />

      {supportsAvecUrl.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun support pour cette formation/année.
        </p>
      ) : (
        <ul className="flex flex-col divide-y divide-border rounded-lg border border-border">
          {supportsAvecUrl.map((s) => (
            <li key={s.id} className="flex items-center justify-between gap-4 px-4 py-3">
              <div>
                {s.url ? (
                  <a
                    href={s.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-sm font-medium text-primary hover:underline"
                  >
                    {s.titre}
                  </a>
                ) : (
                  <span className="text-sm font-medium text-ink">{s.titre}</span>
                )}
                <p className="text-xs text-ink-soft">
                  {s.matiere} — publié le {formatDateFr(s.published_at)}
                </p>
              </div>
              <DeleteButton
                action={() => deleteSupport(s.id)}
                confirmMessage="Supprimer ce support de cours ?"
              />
            </li>
          ))}
        </ul>
      )}

      <SupportForm formationId={formationId} anneeAcademiqueId={anneeId} />
    </div>
  );
}
