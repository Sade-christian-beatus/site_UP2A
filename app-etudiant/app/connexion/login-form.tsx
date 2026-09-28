"use client";

import { useActionState } from "react";
import { login } from "@/lib/auth/actions";

export function LoginForm() {
  const [state, action, pending] = useActionState(login, undefined);

  return (
    <form action={action} className="flex w-full max-w-sm flex-col gap-4">
      <div className="flex flex-col gap-1">
        <label htmlFor="matricule" className="text-sm font-medium text-ink">
          Matricule
        </label>
        <input
          id="matricule"
          name="matricule"
          type="text"
          required
          autoComplete="username"
          placeholder="UP2A-2026-0001"
          className="rounded-md border border-border bg-surface px-3 py-2 text-ink outline-none focus:border-primary"
        />
      </div>

      <div className="flex flex-col gap-1">
        <label htmlFor="password" className="text-sm font-medium text-ink">
          Mot de passe
        </label>
        <input
          id="password"
          name="password"
          type="password"
          required
          autoComplete="current-password"
          className="rounded-md border border-border bg-surface px-3 py-2 text-ink outline-none focus:border-primary"
        />
      </div>

      {state?.error && (
        <p className="text-sm text-error" role="alert">
          {state.error}
        </p>
      )}

      <button
        type="submit"
        disabled={pending}
        className="rounded-md bg-primary px-4 py-2 font-medium text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
      >
        {pending ? "Connexion..." : "Se connecter"}
      </button>
    </form>
  );
}
