"use client";

import { useActionState } from "react";
import { transformerEnEtudiant, type ActionState } from "@/lib/admin/actions";

export function TransformerButton({
  candidatureId,
}: {
  candidatureId: string;
}) {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    async () => transformerEnEtudiant(candidatureId),
    undefined,
  );

  return (
    <form
      action={formAction}
      onSubmit={(e) => {
        if (
          !confirm(
            "Créer le compte étudiant et envoyer l'invitation par e-mail ? Cette action est définitive.",
          )
        ) {
          e.preventDefault();
        }
      }}
      className="flex flex-col items-start gap-2"
    >
      <button
        type="submit"
        disabled={pending}
        className="rounded-md bg-accent px-4 py-2 text-sm font-semibold text-ink transition-colors hover:bg-accent-light disabled:opacity-60"
      >
        {pending ? "Création en cours..." : "Transformer en compte étudiant"}
      </button>
      <p className="text-xs text-ink-soft">
        Envoie un e-mail au candidat pour qu&apos;il choisisse son mot de
        passe et crée son dossier étudiant.
      </p>
      {state && "error" in state && (
        <p className="text-sm text-error" role="alert">
          {state.error}
        </p>
      )}
      {state && "success" in state && (
        <p className="text-sm text-success">
          Compte étudiant créé — invitation envoyée par e-mail.
        </p>
      )}
    </form>
  );
}
