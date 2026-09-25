"use client";

import { useActionState } from "react";
import {
  updateCandidatureStatut,
  type ActionState,
} from "@/lib/actions";
import {
  STATUTS_CANDIDATURE,
  STATUT_LABELS,
  type StatutCandidature,
} from "@/lib/constants";

export function StatutForm({
  candidatureId,
  statutActuel,
}: {
  candidatureId: string;
  statutActuel: StatutCandidature;
}) {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    async (_prevState, formData) => {
      const statut = formData.get("statut") as StatutCandidature;
      return updateCandidatureStatut(candidatureId, statut);
    },
    undefined,
  );

  return (
    <form action={formAction} className="flex flex-wrap items-center gap-3">
      <label htmlFor="statut" className="text-sm font-medium text-ink">
        Statut
      </label>
      <select
        id="statut"
        name="statut"
        defaultValue={statutActuel}
        className="rounded-md border border-border bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-primary"
      >
        {STATUTS_CANDIDATURE.map((s) => (
          <option key={s} value={s}>
            {STATUT_LABELS[s]}
          </option>
        ))}
      </select>
      <button
        type="submit"
        disabled={pending}
        className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
      >
        {pending ? "Mise à jour..." : "Mettre à jour"}
      </button>
      {state && "error" in state && (
        <p className="w-full text-sm text-error" role="alert">
          {state.error}
        </p>
      )}
      {state && "success" in state && (
        <p className="w-full text-sm text-success">Statut mis à jour.</p>
      )}
    </form>
  );
}
