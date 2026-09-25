import type { Metadata } from "next";
import { getMesSupports } from "@/lib/etudiant/data";
import { formatDateFr } from "@/lib/academique/constants";

export const metadata: Metadata = { title: "Supports de cours — Espace étudiant UP-2A" };

export default async function SupportsPage() {
  const supports = await getMesSupports();

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Supports de cours</h1>
        <p className="text-sm text-ink-soft">Documents pédagogiques publiés pour votre formation.</p>
      </div>

      {supports.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucun support disponible pour le moment.
        </p>
      ) : (
        <ul className="flex flex-col divide-y divide-border rounded-lg border border-border">
          {supports.map((s) => (
            <li key={s.id} className="px-4 py-3">
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
              {s.description && <p className="text-sm text-ink-soft">{s.description}</p>}
              <p className="text-xs text-ink-soft">
                {s.matiere} — publié le {formatDateFr(s.published_at)}
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
