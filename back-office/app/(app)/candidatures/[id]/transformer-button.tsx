"use client";

import { useActionState, useState } from "react";
import { transformerEnEtudiant, type TransformerActionState } from "@/lib/actions";

export function TransformerButton({
  candidatureId,
}: {
  candidatureId: string;
}) {
  const [copie, setCopie] = useState(false);
  const [state, formAction, pending] = useActionState<TransformerActionState, FormData>(
    async () => transformerEnEtudiant(candidatureId),
    undefined,
  );

  async function copierMotDePasse(motDePasse: string) {
    try {
      await navigator.clipboard.writeText(motDePasse);
      setCopie(true);
      setTimeout(() => setCopie(false), 2000);
    } catch {
      // Presse-papiers indisponible (permissions navigateur) : le mot de
      // passe reste visible à l'écran, copiable à la main.
    }
  }

  if (state && "success" in state) {
    return (
      <div className="flex flex-col gap-3 rounded-md border border-success bg-surface-alt p-4">
        <p className="text-sm font-medium text-success">
          Compte étudiant créé — matricule {state.matricule}.
        </p>
        <div>
          <p className="text-xs font-medium uppercase tracking-wide text-ink-soft">
            Mot de passe temporaire (affiché une seule fois)
          </p>
          <div className="mt-1 flex items-center gap-2">
            <code className="rounded-md border border-border bg-surface px-3 py-1.5 font-mono text-sm text-ink">
              {state.motDePasse}
            </code>
            <button
              type="button"
              onClick={() => copierMotDePasse(state.motDePasse)}
              className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-ink-soft transition-colors hover:border-primary hover:text-primary"
            >
              {copie ? "Copié !" : "Copier"}
            </button>
          </div>
        </div>
        <p className="text-xs text-ink-soft">
          Communiquez ce mot de passe à l&apos;étudiant par téléphone ou en
          personne (pas d&apos;e-mail envoyé). Il pourra le changer une fois
          connecté, dans son espace étudiant.
        </p>
      </div>
    );
  }

  return (
    <form
      action={formAction}
      onSubmit={(e) => {
        if (
          !confirm(
            "Créer le compte étudiant avec un mot de passe temporaire ? Cette action est définitive.",
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
        Crée le compte et un mot de passe temporaire à communiquer
        vous-même à l&apos;étudiant (aucun e-mail envoyé).
      </p>
      {state && "error" in state && (
        <p className="text-sm text-error" role="alert">
          {state.error}
        </p>
      )}
    </form>
  );
}
