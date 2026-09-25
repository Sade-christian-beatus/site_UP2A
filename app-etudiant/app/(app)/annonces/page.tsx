import type { Metadata } from "next";
import { getMesAnnonces } from "@/lib/etudiant/data";
import { formatDateFr } from "@/lib/academique/constants";

export const metadata: Metadata = { title: "Annonces — Espace étudiant UP-2A" };

export default async function AnnoncesPage() {
  const annonces = await getMesAnnonces();

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Annonces</h1>
        <p className="text-sm text-ink-soft">Actualités de votre formation et de l&apos;université.</p>
      </div>

      {annonces.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucune annonce pour le moment.
        </p>
      ) : (
        <ul className="flex flex-col gap-3">
          {annonces.map((a) => (
            <li key={a.id} className="rounded-lg border border-border bg-surface p-4">
              <h2 className="font-heading text-base text-ink">{a.titre}</h2>
              <p className="mt-1 whitespace-pre-wrap text-sm text-ink-soft">{a.contenu}</p>
              <p className="mt-2 text-xs text-ink-soft">{formatDateFr(a.publie_le)}</p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
