import type { Metadata } from "next";
import { getMesCreneaux } from "@/lib/etudiant/data";
import { JOURS_SEMAINE, JOUR_LABELS, formatHeure } from "@/lib/academique/constants";

export const metadata: Metadata = { title: "Emploi du temps — Espace étudiant UP-2A" };

export default async function EmploiDuTempsPage() {
  const creneaux = await getMesCreneaux();

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Emploi du temps</h1>
        <p className="text-sm text-ink-soft">Vos créneaux hebdomadaires.</p>
      </div>

      {creneaux.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun créneau publié pour le moment.
        </p>
      ) : (
        <div className="flex flex-col gap-6">
          {JOURS_SEMAINE.filter((jour) => creneaux.some((c) => c.jour_semaine === jour)).map(
            (jour) => (
              <div key={jour}>
                <h2 className="mb-2 font-heading text-lg text-ink">{JOUR_LABELS[jour]}</h2>
                <ul className="flex flex-col divide-y divide-border rounded-lg border border-border">
                  {creneaux
                    .filter((c) => c.jour_semaine === jour)
                    .sort((a, b) => a.heure_debut.localeCompare(b.heure_debut))
                    .map((c) => (
                      <li key={c.id} className="flex items-center justify-between px-4 py-3 text-sm">
                        <div>
                          <span className="font-medium text-ink">{c.matiere}</span>
                          {c.enseignant && (
                            <span className="text-ink-soft"> — {c.enseignant}</span>
                          )}
                        </div>
                        <div className="text-right text-ink-soft">
                          <div>
                            {formatHeure(c.heure_debut)} – {formatHeure(c.heure_fin)}
                          </div>
                          {c.salle && <div>{c.salle}</div>}
                        </div>
                      </li>
                    ))}
                </ul>
              </div>
            ),
          )}
        </div>
      )}
    </div>
  );
}
