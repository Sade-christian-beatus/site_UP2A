"use client";

import { useActionState, useRef } from "react";
import { createCreneau, type ActionState } from "@/lib/academique-actions";
import { JOURS_SEMAINE, JOUR_LABELS } from "@/lib/academique/constants";

const inputClass =
  "rounded-md border border-border bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-primary";

export function CreneauForm({
  formationId,
  anneeAcademiqueId,
}: {
  formationId: string;
  anneeAcademiqueId: string;
}) {
  const formRef = useRef<HTMLFormElement>(null);
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    async (_prev, formData) => {
      const result = await createCreneau(formData);
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
      <input type="hidden" name="formation_id" value={formationId} />
      <input type="hidden" name="annee_academique_id" value={anneeAcademiqueId} />

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
        <select name="jour_semaine" required defaultValue="lundi" className={inputClass}>
          {JOURS_SEMAINE.map((j) => (
            <option key={j} value={j}>
              {JOUR_LABELS[j]}
            </option>
          ))}
        </select>
        <input type="time" name="heure_debut" required className={inputClass} />
        <input type="time" name="heure_fin" required className={inputClass} />
        <input
          type="text"
          name="matiere"
          placeholder="Matière"
          required
          className={`${inputClass} sm:col-span-2 md:col-span-1`}
        />
        <input type="text" name="enseignant" placeholder="Enseignant (optionnel)" className={inputClass} />
        <input type="text" name="salle" placeholder="Salle (optionnel)" className={inputClass} />
      </div>

      <div className="flex items-center gap-3">
        <button
          type="submit"
          disabled={pending}
          className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
        >
          {pending ? "Ajout..." : "Ajouter le créneau"}
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
