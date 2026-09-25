import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import {
  getCandidature,
  signPiecesJointes,
  type PieceJointe,
} from "@/lib/admin/candidatures";
import { StatutForm } from "./statut-form";
import { TransformerButton } from "./transformer-button";

export const metadata: Metadata = { title: "Détail candidature — Back-office UP-2A" };

function InfoRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex flex-col gap-0.5">
      <dt className="text-xs font-medium uppercase tracking-wide text-ink-soft">
        {label}
      </dt>
      <dd className="text-sm text-ink">{value}</dd>
    </div>
  );
}

export default async function CandidatureDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const candidature = await getCandidature(id);

  if (!candidature) {
    notFound();
  }

  const pieces = await signPiecesJointes(
    (candidature.pieces_jointes as PieceJointe[]) ?? [],
  );

  return (
    <div className="flex max-w-3xl flex-col gap-8">
      <div>
        <Link href="/admin" className="text-sm text-primary hover:underline">
          ← Retour aux candidatures
        </Link>
        <h1 className="mt-2 font-heading text-2xl text-ink">
          {candidature.prenom} {candidature.nom}
        </h1>
        <p className="text-sm text-ink-soft">
          Reçue le{" "}
          {new Date(candidature.created_at).toLocaleDateString("fr-FR", {
            day: "2-digit",
            month: "long",
            year: "numeric",
          })}
        </p>
      </div>

      <section className="rounded-lg border border-border bg-surface p-6">
        <h2 className="mb-4 font-heading text-lg text-ink">Statut</h2>
        <StatutForm candidatureId={candidature.id} statutActuel={candidature.statut} />
      </section>

      <section className="grid grid-cols-1 gap-6 rounded-lg border border-border bg-surface p-6 sm:grid-cols-2">
        <InfoRow label="E-mail" value={candidature.email} />
        <InfoRow label="Téléphone" value={candidature.telephone} />
        <InfoRow
          label="Date de naissance"
          value={new Date(candidature.date_naissance).toLocaleDateString("fr-FR")}
        />
        <InfoRow label="Sexe" value={candidature.sexe ?? "—"} />
        <InfoRow label="Adresse" value={candidature.adresse ?? "—"} />
        <InfoRow
          label="Formation"
          value={candidature.formation?.nom ?? "—"}
        />
        <InfoRow
          label="Année académique"
          value={candidature.annee_academique?.libelle ?? "—"}
        />
        <InfoRow
          label="Diplôme obtenu"
          value={candidature.diplome_obtenu ?? "—"}
        />
        <InfoRow
          label="Établissement d'origine"
          value={candidature.etablissement_origine ?? "—"}
        />
      </section>

      <section className="rounded-lg border border-border bg-surface p-6">
        <h2 className="mb-4 font-heading text-lg text-ink">
          Pièces jointes
        </h2>
        {pieces.length === 0 ? (
          <p className="text-sm text-ink-soft">Aucune pièce jointe.</p>
        ) : (
          <ul className="flex flex-col gap-2">
            {pieces.map((piece, i) => (
              <li key={i}>
                {piece.url ? (
                  <a
                    href={piece.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-sm text-primary hover:underline"
                  >
                    {piece.label ?? piece.type ?? "Document"}
                  </a>
                ) : (
                  <span className="text-sm text-ink-soft">
                    {piece.label ?? piece.type ?? "Document"} (lien indisponible)
                  </span>
                )}
              </li>
            ))}
          </ul>
        )}
      </section>

      <section className="rounded-lg border border-border bg-surface p-6">
        <h2 className="mb-4 font-heading text-lg text-ink">
          Transformer en compte étudiant
        </h2>
        {candidature.statut === "transformee" ? (
          <p className="text-sm text-ink-soft">
            Cette candidature a déjà été transformée en compte étudiant.
          </p>
        ) : candidature.statut === "acceptee" ? (
          <TransformerButton candidatureId={candidature.id} />
        ) : (
          <p className="text-sm text-ink-soft">
            Passez d&apos;abord le statut à &quot;Acceptée&quot; ci-dessus
            pour pouvoir transformer cette candidature en compte étudiant.
          </p>
        )}
      </section>
    </div>
  );
}
