"use client";

import { useActionState, useRef } from "react";
import { createAnnonce, type ActionState } from "@/lib/academique-actions";
import type { Formation, AnneeAcademique } from "@/lib/academique";

const inputClass =
  "rounded-md border border-border bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-primary";

export function AnnonceForm({
  formations,
  annees,
}: {
  formations: Formation[];
  annees: AnneeAcademique[];
}) {
  const formRef = useRef<HTMLFormElement>(null);
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    async (_prev, formData) => {
      const result = await createAnnonce(formData);
      if (result && "success" in result) formRef.current?.reset();
      return result;
    },
    undefined,
  );

  return (
    <form
      ref={formRef}
      action={formAction}
      className="flex flex-col gap-3 rounded-lg border border-border bg-surface p-6"
    >
      <input type="text" name="titre" placeholder="Titre" required className={inputClass} />
      <textarea
        name="contenu"
        placeholder="Contenu de l'annonce"
        required
        rows={4}
        className={inputClass}
      />

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <select name="formation_id" defaultValue="" className={inputClass}>
          <option value="">Toutes les formations</option>
          {formations.map((f) => (
            <option key={f.id} value={f.id}>
              {f.nom}
            </option>
          ))}
        </select>
        <select name="annee_academique_id" defaultValue="" className={inputClass}>
          <option value="">Toutes les années</option>
          {annees.map((a) => (
            <option key={a.id} value={a.id}>
              {a.libelle}
            </option>
          ))}
        </select>
      </div>

      <div className="flex items-center gap-3">
        <button
          type="submit"
          disabled={pending}
          className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
        >
          {pending ? "Publication..." : "Publier l'annonce"}
        </button>
        {state && "error" in state && (
          <p className="text-sm text-error" role="alert">
            {state.error}
          </p>
        )}
      </div>
    </form>
  );
}
