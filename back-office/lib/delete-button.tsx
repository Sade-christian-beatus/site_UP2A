"use client";

import { useActionState } from "react";
import type { ActionState } from "@/lib/academique-actions";

/**
 * Bouton de suppression générique (confirm() + Server Action), réutilisé
 * par tous les écrans de saisie académique — même pattern que
 * TransformerButton (app/admin/candidatures/[id]/transformer-button.tsx).
 */
export function DeleteButton({
  action,
  confirmMessage,
  label = "Supprimer",
}: {
  action: () => Promise<ActionState>;
  confirmMessage: string;
  label?: string;
}) {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    async () => action(),
    undefined,
  );

  return (
    <form
      action={formAction}
      onSubmit={(e) => {
        if (!confirm(confirmMessage)) e.preventDefault();
      }}
    >
      <button
        type="submit"
        disabled={pending}
        className="text-xs font-medium text-error hover:underline disabled:opacity-60"
      >
        {pending ? "..." : label}
      </button>
      {state && "error" in state && (
        <p className="text-xs text-error">{state.error}</p>
      )}
    </form>
  );
}
