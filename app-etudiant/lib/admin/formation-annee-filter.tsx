"use client";

import { useRouter, usePathname } from "next/navigation";
import type { Formation, AnneeAcademique } from "@/lib/admin/academique";

/**
 * Barre de filtre formation/année, réutilisée par les écrans de saisie
 * académique (emplois du temps, examens, supports de cours) — même
 * comportement partout : changer un select met à jour l'URL (GET), la
 * page se recharge côté serveur avec les nouveaux filtres.
 */
export function FormationAnneeFilter({
  formations,
  annees,
  formationId,
  anneeId,
}: {
  formations: Formation[];
  annees: AnneeAcademique[];
  formationId: string;
  anneeId: string;
}) {
  const router = useRouter();
  const pathname = usePathname();

  function update(next: { formation?: string; annee?: string }) {
    const params = new URLSearchParams({
      formation: next.formation ?? formationId,
      annee: next.annee ?? anneeId,
    });
    router.push(`${pathname}?${params.toString()}`);
  }

  return (
    <div className="flex flex-wrap gap-3">
      <select
        value={formationId}
        onChange={(e) => update({ formation: e.target.value })}
        className="rounded-md border border-border bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-primary"
      >
        {formations.map((f) => (
          <option key={f.id} value={f.id}>
            {f.nom}
          </option>
        ))}
      </select>
      <select
        value={anneeId}
        onChange={(e) => update({ annee: e.target.value })}
        className="rounded-md border border-border bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-primary"
      >
        {annees.map((a) => (
          <option key={a.id} value={a.id}>
            {a.libelle}
          </option>
        ))}
      </select>
    </div>
  );
}
