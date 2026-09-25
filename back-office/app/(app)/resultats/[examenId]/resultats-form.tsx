"use client";

import { useActionState } from "react";
import {
  upsertResultats,
  setResultatsPublication,
  type ActionState,
} from "@/lib/academique-actions";

type Etudiant = {
  id: string;
  matricule: string;
  nom: string;
  prenom: string;
  note: number | null;
  mention: string | null;
};

const inputClass =
  "w-24 rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-ink outline-none focus:border-primary";

export function ResultatsForm({
  examenId,
  etudiants,
  toutesPubliees,
}: {
  examenId: string;
  etudiants: Etudiant[];
  toutesPubliees: boolean;
}) {
  const etudiantIds = etudiants.map((e) => e.id);

  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    async (_prev, formData) => upsertResultats(examenId, etudiantIds, formData),
    undefined,
  );

  const [pubState, pubAction, pubPending] = useActionState<ActionState, FormData>(
    async () => setResultatsPublication(examenId, !toutesPubliees),
    undefined,
  );

  return (
    <div className="flex flex-col gap-4">
      <form action={pubAction} className="flex items-center gap-3">
        <button
          type="submit"
          disabled={pubPending || etudiants.length === 0}
          className={`rounded-md px-4 py-2 text-sm font-medium text-white transition-colors disabled:opacity-60 ${
            toutesPubliees ? "bg-ink-soft hover:bg-ink" : "bg-success hover:opacity-90"
          }`}
        >
          {pubPending
            ? "..."
            : toutesPubliees
              ? "Dépublier les résultats"
              : "Publier les résultats"}
        </button>
        <span className="text-xs text-ink-soft">
          {toutesPubliees
            ? "Visibles par les étudiants concernés."
            : "Non visibles par les étudiants tant que non publiés."}
        </span>
        {pubState && "error" in pubState && (
          <p className="text-sm text-error">{pubState.error}</p>
        )}
      </form>

      <form action={formAction} className="flex flex-col gap-4">
        <div className="overflow-x-auto rounded-lg border border-border">
          <table className="w-full min-w-[560px] text-left text-sm">
            <thead className="bg-surface-alt text-ink-soft">
              <tr>
                <th className="px-4 py-3 font-medium">Matricule</th>
                <th className="px-4 py-3 font-medium">Étudiant</th>
                <th className="px-4 py-3 font-medium">Note / 20</th>
                <th className="px-4 py-3 font-medium">Mention</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {etudiants.map((e) => (
                <tr key={e.id}>
                  <td className="px-4 py-3 text-ink-soft">{e.matricule}</td>
                  <td className="px-4 py-3 text-ink">
                    {e.prenom} {e.nom}
                  </td>
                  <td className="px-4 py-3">
                    <input
                      type="number"
                      name={`note_${e.id}`}
                      min={0}
                      max={20}
                      step={0.25}
                      defaultValue={e.note ?? ""}
                      className={inputClass}
                    />
                  </td>
                  <td className="px-4 py-3">
                    <input
                      type="text"
                      name={`mention_${e.id}`}
                      defaultValue={e.mention ?? ""}
                      placeholder="optionnel"
                      className={inputClass}
                    />
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="flex items-center gap-3">
          <button
            type="submit"
            disabled={pending || etudiants.length === 0}
            className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
          >
            {pending ? "Enregistrement..." : "Enregistrer les résultats"}
          </button>
          <p className="text-xs text-ink-soft">
            Les cases laissées vides ne créent ni ne modifient de résultat.
          </p>
        </div>
        {state && "error" in state && (
          <p className="text-sm text-error" role="alert">
            {state.error}
          </p>
        )}
        {state && "success" in state && (
          <p className="text-sm text-success">Résultats enregistrés.</p>
        )}
      </form>
    </div>
  );
}
