import type { Metadata } from "next";
import { listAnnonces, listFormations, listAnneesAcademiques } from "@/lib/academique";
import { deleteAnnonce } from "@/lib/academique-actions";
import { DeleteButton } from "@/lib/delete-button";
import { AnnonceForm } from "./annonce-form";
import { formatDateFr } from "@/lib/academique/constants";

export const metadata: Metadata = { title: "Annonces — Back-office UP-2A" };

export default async function AnnoncesPage() {
  const [annonces, formations, annees] = await Promise.all([
    listAnnonces(),
    listFormations(),
    listAnneesAcademiques(),
  ]);

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Annonces</h1>
        <p className="text-sm text-ink-soft">
          Visibles par les étudiants dès leur publication, selon leur ciblage.
        </p>
      </div>

      {annonces.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucune annonce publiée.
        </p>
      ) : (
        <ul className="flex flex-col gap-3">
          {annonces.map((a) => (
            <li key={a.id} className="rounded-lg border border-border bg-surface p-4">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <h2 className="font-heading text-base text-ink">{a.titre}</h2>
                  <p className="mt-1 whitespace-pre-wrap text-sm text-ink-soft">{a.contenu}</p>
                  <p className="mt-2 text-xs text-ink-soft">
                    {a.formation?.nom ?? "Toutes les formations"} ·{" "}
                    {a.annee_academique?.libelle ?? "Toutes les années"} · publiée le{" "}
                    {formatDateFr(a.publie_le)}
                  </p>
                </div>
                <DeleteButton
                  action={() => deleteAnnonce(a.id)}
                  confirmMessage="Supprimer cette annonce ?"
                />
              </div>
            </li>
          ))}
        </ul>
      )}

      <AnnonceForm formations={formations} annees={annees} />
    </div>
  );
}
