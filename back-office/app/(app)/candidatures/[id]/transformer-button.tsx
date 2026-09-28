"use client";

import { useActionState, useState } from "react";
import { transformerEnEtudiant, type TransformerActionState } from "@/lib/actions";

export function TransformerButton({
  candidatureId,
}: {
  candidatureId: string;
}) {
  const [copie, setCopie] = useState<"matricule" | "motDePasse" | null>(null);
  const [state, formAction, pending] = useActionState<TransformerActionState, FormData>(
    async () => transformerEnEtudiant(candidatureId),
    undefined,
  );

  async function copier(valeur: string, cible: "matricule" | "motDePasse") {
    try {
      await navigator.clipboard.writeText(valeur);
      setCopie(cible);
      setTimeout(() => setCopie(null), 2000);
    } catch {
      // Presse-papiers indisponible (permissions navigateur) : la valeur
      // reste visible à l'écran, copiable à la main.
    }
  }

  if (state && "success" in state) {
    return (
      <div className="flex flex-col gap-3 rounded-md border border-success bg-surface-alt p-4">
        <p className="text-sm font-medium text-success">Compte étudiant créé.</p>
        <p className="text-xs text-ink-soft">
          L&apos;étudiant se connecte à son espace avec ces deux
          identifiants — <strong>jamais avec son e-mail</strong> (SMTP non
          configuré, décision définitive). Communiquez-les-lui vous-même
          (téléphone, en personne) : aucun e-mail n&apos;est envoyé.
        </p>
        <div>
          <p className="text-xs font-medium uppercase tracking-wide text-ink-soft">
            Matricule (identifiant de connexion)
          </p>
          <div className="mt-1 flex items-center gap-2">
            <code className="rounded-md border border-border bg-surface px-3 py-1.5 font-mono text-sm text-ink">
              {state.matricule}
            </code>
            <button
              type="button"
              onClick={() => copier(state.matricule, "matricule")}
              className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-ink-soft transition-colors hover:border-primary hover:text-primary"
            >
              {copie === "matricule" ? "Copié !" : "Copier"}
            </button>
          </div>
        </div>
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
              onClick={() => copier(state.motDePasse, "motDePasse")}
              className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-ink-soft transition-colors hover:border-primary hover:text-primary"
            >
              {copie === "motDePasse" ? "Copié !" : "Copier"}
            </button>
          </div>
        </div>
        <p className="text-xs text-ink-soft">
          L&apos;étudiant pourra changer ce mot de passe une fois connecté,
          dans son espace étudiant (le matricule, lui, ne change pas).
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
        Crée le compte, un matricule et un mot de passe temporaire à
        communiquer vous-même à l&apos;étudiant (aucun e-mail envoyé — il se
        connecte avec son matricule, pas avec son adresse e-mail).
      </p>
      {state && "error" in state && (
        <p className="text-sm text-error" role="alert">
          {state.error}
        </p>
      )}
    </form>
  );
}
