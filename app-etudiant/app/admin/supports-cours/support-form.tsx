"use client";

import { useActionState, useRef } from "react";
import { createSupport, type ActionState } from "@/lib/admin/academique-actions";

const inputClass =
  "rounded-md border border-border bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-primary";

export function SupportForm({
  formationId,
  anneeAcademiqueId,
}: {
  formationId: string;
  anneeAcademiqueId: string;
}) {
  const formRef = useRef<HTMLFormElement>(null);
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    async (_prev, formData) => {
      const result = await createSupport(formData);
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

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <input type="text" name="matiere" placeholder="Matière" required className={inputClass} />
        <input type="text" name="titre" placeholder="Titre du document" required className={inputClass} />
        <input
          type="text"
          name="description"
          placeholder="Description (optionnel)"
          className={`${inputClass} sm:col-span-2`}
        />
        <input
          type="file"
          name="fichier"
          required
          accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
          className={`${inputClass} sm:col-span-2`}
        />
      </div>

      <div className="flex items-center gap-3">
        <button
          type="submit"
          disabled={pending}
          className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
        >
          {pending ? "Envoi..." : "Publier le support"}
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
