import type { Metadata } from "next";
import Link from "next/link";
import { getMonEtudiant, getMesAnnonces, getMesExamens } from "@/lib/etudiant/data";
import { formatDateFr } from "@/lib/academique/constants";

export const metadata: Metadata = { title: "Tableau de bord — Espace étudiant UP-2A" };

const STATUT_LABELS: Record<string, string> = {
  actif: "Actif",
  suspendu: "Suspendu",
  diplome: "Diplômé",
  abandon: "Abandon",
};

export default async function EtudiantPage() {
  const [etudiant, annonces, examens] = await Promise.all([
    getMonEtudiant(),
    getMesAnnonces(),
    getMesExamens(),
  ]);

  if (!etudiant) {
    return (
      <p className="text-sm text-ink-soft">
        Aucun dossier étudiant associé à ce compte — contactez
        l&apos;administration.
      </p>
    );
  }

  const aujourdhui = new Date().toISOString().slice(0, 10);
  const prochainsExamens = examens
    .filter((e) => e.date_examen >= aujourdhui)
    .slice(0, 3);
  const dernieresAnnonces = annonces.slice(0, 3);

  return (
    <div className="flex flex-col gap-6">
      <div className="rounded-lg border border-border bg-surface p-6">
        <p className="text-xs font-medium uppercase tracking-wide text-ink-soft">
          {etudiant.matricule}
        </p>
        <h1 className="font-heading text-2xl text-ink">
          {etudiant.formation?.nom ?? "Formation non renseignée"}
        </h1>
        <p className="text-sm text-ink-soft">
          {etudiant.annee_academique?.libelle ?? "—"} · Statut :{" "}
          {STATUT_LABELS[etudiant.statut] ?? etudiant.statut}
        </p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <section className="rounded-lg border border-border bg-surface p-6">
          <div className="mb-3 flex items-center justify-between">
            <h2 className="font-heading text-lg text-ink">Prochains examens</h2>
            <Link href="/etudiant/examens" className="text-sm text-primary hover:underline">
              Tout voir
            </Link>
          </div>
          {prochainsExamens.length === 0 ? (
            <p className="text-sm text-ink-soft">Aucun examen à venir.</p>
          ) : (
            <ul className="flex flex-col gap-2">
              {prochainsExamens.map((e) => (
                <li key={e.id} className="text-sm">
                  <span className="text-ink">{e.matiere}</span>{" "}
                  <span className="text-ink-soft">— {formatDateFr(e.date_examen)}</span>
                </li>
              ))}
            </ul>
          )}
        </section>

        <section className="rounded-lg border border-border bg-surface p-6">
          <div className="mb-3 flex items-center justify-between">
            <h2 className="font-heading text-lg text-ink">Dernières annonces</h2>
            <Link href="/etudiant/annonces" className="text-sm text-primary hover:underline">
              Tout voir
            </Link>
          </div>
          {dernieresAnnonces.length === 0 ? (
            <p className="text-sm text-ink-soft">Aucune annonce pour le moment.</p>
          ) : (
            <ul className="flex flex-col gap-2">
              {dernieresAnnonces.map((a) => (
                <li key={a.id} className="text-sm">
                  <span className="text-ink">{a.titre}</span>{" "}
                  <span className="text-ink-soft">— {formatDateFr(a.publie_le)}</span>
                </li>
              ))}
            </ul>
          )}
        </section>
      </div>
    </div>
  );
}
