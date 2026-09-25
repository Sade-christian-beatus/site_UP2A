import type { Metadata } from "next";
import { getMesResultats } from "@/lib/etudiant/data";
import { TYPE_EXAMEN_LABELS, formatDateFr } from "@/lib/academique/constants";

export const metadata: Metadata = { title: "Résultats — Espace étudiant UP-2A" };

export default async function ResultatsPage() {
  const resultats = await getMesResultats();

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Résultats</h1>
        <p className="text-sm text-ink-soft">Vos résultats publiés.</p>
      </div>

      {resultats.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun résultat publié pour le moment.
        </p>
      ) : (
        <div className="overflow-x-auto rounded-lg border border-border">
          <table className="w-full min-w-[480px] text-left text-sm">
            <thead className="bg-surface-alt text-ink-soft">
              <tr>
                <th className="px-4 py-3 font-medium">Matière</th>
                <th className="px-4 py-3 font-medium">Type</th>
                <th className="px-4 py-3 font-medium">Date</th>
                <th className="px-4 py-3 font-medium">Note / 20</th>
                <th className="px-4 py-3 font-medium">Mention</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {resultats.map((r) => (
                <tr key={r.id}>
                  <td className="px-4 py-3 text-ink">{r.examen?.matiere ?? "—"}</td>
                  <td className="px-4 py-3 text-ink-soft">
                    {r.examen ? TYPE_EXAMEN_LABELS[r.examen.type] : "—"}
                  </td>
                  <td className="px-4 py-3 text-ink-soft">
                    {r.examen ? formatDateFr(r.examen.date_examen) : "—"}
                  </td>
                  <td className="px-4 py-3 font-medium text-ink">{r.note}</td>
                  <td className="px-4 py-3 text-ink-soft">{r.mention ?? "—"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
