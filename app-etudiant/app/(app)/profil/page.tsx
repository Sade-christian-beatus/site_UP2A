import type { Metadata } from "next";
import { getProfile } from "@/lib/auth/dal";
import { PasswordForm } from "./password-form";

export const metadata: Metadata = { title: "Mon compte — Espace étudiant UP-2A" };

export default async function ProfilPage() {
  const profile = await getProfile();

  return (
    <div className="flex flex-col gap-6">
      <div>
        <h1 className="font-heading text-2xl text-ink">Mon compte</h1>
        <p className="text-sm text-ink-soft">
          Connecté en tant que {profile?.prenom} {profile?.nom}.
        </p>
      </div>

      <section className="rounded-lg border border-border bg-surface p-6">
        <h2 className="mb-1 font-heading text-lg text-ink">Changer mon mot de passe</h2>
        <p className="mb-4 text-sm text-ink-soft">
          Utile en particulier pour remplacer le mot de passe temporaire
          communiqué par l&apos;administration lors de la création de votre
          compte.
        </p>
        <PasswordForm />
      </section>
    </div>
  );
}
