import type { Metadata } from "next";
import Link from "next/link";
import { listCandidatures } from "@/lib/admin/candidatures";
import {
  STATUTS_CANDIDATURE,
  STATUT_LABELS,
  type StatutCandidature,
} from "@/lib/admin/constants";

export const metadata: Metadata = { title: "Candidatures — Back-office UP-2A" };

const STATUT_BADGE_CLASSES: Record<StatutCandidature, string> = {
  nouvelle: "bg-accent-light text-ink",
  en_cours: "bg-teal text-white",
  acceptee: "bg-success text-white",
  refusee: "bg-error text-white",
  transformee: "bg-primary text-white",
};

function isStatut(value: string | undefined): value is StatutCandidature {
  return !!value && (STATUTS_CANDIDATURE as string[]).includes(value);
}

export default async function AdminPage({
  searchParams,
}: {
  searchParams: Promise<{ statut?: string }>;
}) {
  const { statut: statutParam } = await searchParams;
  const statut = isStatut(statutParam) ? statutParam : undefined;
  const candidatures = await listCandidatures(statut);

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Candidatures</h1>
        <p className="text-sm text-ink-soft">
          Préinscriptions reçues depuis le site — {candidatures.length}{" "}
          {statut ? STATUT_LABELS[statut].toLowerCase() : "au total"}.
        </p>
      </div>

      <nav className="flex flex-wrap gap-2">
        <Link
          href="/admin"
          className={`rounded-full px-4 py-1.5 text-sm font-medium transition-colors ${
            !statut
              ? "bg-primary text-white"
              : "bg-surface-alt text-ink-soft hover:bg-border"
          }`}
        >
          Toutes
        </Link>
        {STATUTS_CANDIDATURE.map((s) => (
          <Link
            key={s}
            href={`/admin?statut=${s}`}
            className={`rounded-full px-4 py-1.5 text-sm font-medium transition-colors ${
              statut === s
                ? "bg-primary text-white"
                : "bg-surface-alt text-ink-soft hover:bg-border"
            }`}
          >
            {STATUT_LABELS[s]}
          </Link>
        ))}
      </nav>

      {candidatures.length === 0 ? (
        <p className="rounded-lg border border-border bg-surface-alt px-4 py-8 text-center text-sm text-ink-soft">
          Aucune candidature{statut ? ` avec le statut "${STATUT_LABELS[statut]}"` : ""}.
        </p>
      ) : (
        <div className="overflow-x-auto rounded-lg border border-border">
          <table className="w-full min-w-[640px] text-left text-sm">
            <thead className="bg-surface-alt text-ink-soft">
              <tr>
                <th className="px-4 py-3 font-medium">Candidat</th>
                <th className="px-4 py-3 font-medium">Formation</th>
                <th className="px-4 py-3 font-medium">Contact</th>
                <th className="px-4 py-3 font-medium">Reçue le</th>
                <th className="px-4 py-3 font-medium">Statut</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {candidatures.map((c) => (
                <tr key={c.id} className="hover:bg-surface-alt">
                  <td className="px-4 py-3">
                    <Link
                      href={`/admin/candidatures/${c.id}`}
                      className="font-medium text-ink hover:text-primary hover:underline"
                    >
                      {c.prenom} {c.nom}
                    </Link>
                  </td>
                  <td className="px-4 py-3 text-ink-soft">
                    {c.formation?.nom ?? "—"}
                  </td>
                  <td className="px-4 py-3 text-ink-soft">
                    <div>{c.email}</div>
                    <div>{c.telephone}</div>
                  </td>
                  <td className="px-4 py-3 text-ink-soft">
                    {new Date(c.created_at).toLocaleDateString("fr-FR", {
                      day: "2-digit",
                      month: "2-digit",
                      year: "numeric",
                    })}
                  </td>
                  <td className="px-4 py-3">
                    <span
                      className={`inline-block rounded-full px-3 py-1 text-xs font-semibold ${STATUT_BADGE_CLASSES[c.statut]}`}
                    >
                      {STATUT_LABELS[c.statut]}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
