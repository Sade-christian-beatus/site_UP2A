"use client";

import { useActionState } from "react";
import { changerMotDePasse, type ChangerMotDePasseState } from "@/lib/auth/actions";

const inputClass =
  "rounded-md border border-border bg-surface px-3 py-2 text-sm text-ink outline-none focus:border-primary";

export function PasswordForm() {
  const [state, formAction, pending] = useActionState<ChangerMotDePasseState, FormData>(
    changerMotDePasse,
    undefined,
  );

  return (
    <form action={formAction} className="flex max-w-sm flex-col gap-3">
      <div className="flex flex-col gap-1">
        <label htmlFor="mot_de_passe" className="text-sm font-medium text-ink">
          Nouveau mot de passe
        </label>
        <input
          id="mot_de_passe"
          name="mot_de_passe"
          type="password"
          required
          minLength={8}
          autoComplete="new-password"
          className={inputClass}
        />
      </div>
      <div className="flex flex-col gap-1">
        <label htmlFor="confirmation" className="text-sm font-medium text-ink">
          Confirmer le mot de passe
        </label>
        <input
          id="confirmation"
          name="confirmation"
          type="password"
          required
          minLength={8}
          autoComplete="new-password"
          className={inputClass}
        />
      </div>
      <button
        type="submit"
        disabled={pending}
        className="w-fit rounded-md bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
      >
        {pending ? "Mise à jour..." : "Mettre à jour le mot de passe"}
      </button>
      {state && "error" in state && (
        <p className="text-sm text-error" role="alert">
          {state.error}
        </p>
      )}
      {state && "success" in state && (
        <p className="text-sm text-success">Mot de passe mis à jour.</p>
      )}
    </form>
  );
}
