import type { Metadata } from "next";
import Link from "next/link";
import {
  getMonEtudiant,
  getMesAnnonces,
  getMesExamens,
  getMesSupports,
  getMesResultats,
} from "@/lib/etudiant/data";
import { getProfile } from "@/lib/auth/dal";
import { formatDateFr } from "@/lib/academique/constants";
import { Avatar } from "@/lib/avatar";
import { Icon, type IconName } from "@/lib/icons";

export const metadata: Metadata = { title: "Tableau de bord — Espace étudiant UP-2A" };

const STATUT_LABELS: Record<string, string> = {
  actif: "Actif",
  suspendu: "Suspendu",
  diplome: "Diplômé",
  abandon: "Abandon",
};

const STATUT_BADGE_CLASSES: Record<string, string> = {
  actif: "bg-success text-white",
  suspendu: "bg-warning text-white",
  diplome: "bg-primary text-white",
  abandon: "bg-error text-white",
};

function StatTile({
  href,
  icon,
  valeur,
  label,
}: {
  href: string;
  icon: IconName;
  valeur: number;
  label: string;
}) {
  return (
    <Link
      href={href}
      className="flex items-center gap-3 rounded-lg border border-border bg-surface p-4 transition-colors hover:border-primary"
    >
      <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-alt text-primary">
        <Icon name={icon} />
      </span>
      <span className="flex flex-col">
        <span className="font-heading text-xl text-ink">{valeur}</span>
        <span className="text-xs text-ink-soft">{label}</span>
      </span>
    </Link>
  );
}

export default async function EtudiantPage() {
  const [etudiant, annonces, examens, supports, resultats, profile] = await Promise.all([
    getMonEtudiant(),
    getMesAnnonces(),
    getMesExamens(),
    getMesSupports(),
    getMesResultats(),
    getProfile(),
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
  const prochainsExamens = examens.filter((e) => e.date_examen >= aujourdhui);
  const dernieresAnnonces = annonces.slice(0, 3);

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-col gap-4 rounded-xl border border-border bg-surface p-6 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-4">
          <Avatar
            prenom={profile?.prenom ?? ""}
            nom={profile?.nom ?? ""}
            taille="lg"
            className="hidden sm:flex"
          />
          <div>
            <p className="text-xs font-medium uppercase tracking-wide text-ink-soft">
              {etudiant.matricule}
            </p>
            <h1 className="font-heading text-2xl text-ink">
              {etudiant.formation?.nom ?? "Formation non renseignée"}
            </h1>
            <p className="text-sm text-ink-soft">{etudiant.annee_academique?.libelle ?? "—"}</p>
          </div>
        </div>
        <span
          className={`w-fit rounded-full px-3 py-1 text-xs font-semibold ${
            STATUT_BADGE_CLASSES[etudiant.statut] ?? "bg-surface-alt text-ink-soft"
          }`}
        >
          {STATUT_LABELS[etudiant.statut] ?? etudiant.statut}
        </span>
      </div>

      <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <StatTile href="/examens" icon="clipboard" valeur={prochainsExamens.length} label="Examens à venir" />
        <StatTile href="/supports" icon="book" valeur={supports.length} label="Supports de cours" />
        <StatTile href="/resultats" icon="chart" valeur={resultats.length} label="Résultats publiés" />
        <StatTile href="/annonces" icon="megaphone" valeur={annonces.length} label="Annonces" />
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section className="rounded-lg border border-border bg-surface p-6">
          <div className="mb-3 flex items-center justify-between">
            <h2 className="flex items-center gap-2 font-heading text-lg text-ink">
              <Icon name="clipboard" className="h-5 w-5 text-primary" />
              Prochains examens
            </h2>
            <Link href="/examens" className="text-sm text-primary hover:underline">
              Tout voir
            </Link>
          </div>
          {prochainsExamens.length === 0 ? (
            <p className="text-sm text-ink-soft">Aucun examen à venir.</p>
          ) : (
            <ul className="flex flex-col gap-2">
              {prochainsExamens.slice(0, 3).map((e) => (
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
            <h2 className="flex items-center gap-2 font-heading text-lg text-ink">
              <Icon name="megaphone" className="h-5 w-5 text-primary" />
              Dernières annonces
            </h2>
            <Link href="/annonces" className="text-sm text-primary hover:underline">
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
