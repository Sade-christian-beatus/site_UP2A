import { getMesExamens } from "@/lib/etudiant/data";
import { TYPE_EXAMEN_LABELS, formatDateFr, formatHeure } from "@/lib/academique/constants";

export default async function ExamensPage() {
  const examens = await getMesExamens();

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Examens</h1>
        <p className="text-sm text-ink-soft">Sessions d&apos;examen de votre formation.</p>
      </div>

      {examens.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun examen programmé pour le moment.
        </p>
      ) : (
        <ul className="flex flex-col divide-y divide-border rounded-lg border border-border">
          {examens.map((e) => (
            <li key={e.id} className="flex items-center justify-between px-4 py-3 text-sm">
              <div>
                <span className="font-medium text-ink">{e.matiere}</span>{" "}
                <span className="text-ink-soft">({TYPE_EXAMEN_LABELS[e.type]})</span>
              </div>
              <div className="text-right text-ink-soft">
                <div>{formatDateFr(e.date_examen)}</div>
                <div>
                  {formatHeure(e.heure_debut)} – {formatHeure(e.heure_fin)}
                  {e.salle ? ` · ${e.salle}` : ""}
                </div>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
