import type { Metadata } from "next";
import { LoginForm } from "./login-form";

export const metadata: Metadata = { title: "Connexion — Back-office UP-2A" };

export default function ConnexionPage() {
  return (
    <main className="flex flex-1 flex-col items-center justify-center gap-8 px-6 py-16">
      <div className="flex flex-col items-center gap-2 text-center">
        <h1 className="font-heading text-2xl font-semibold text-ink">
          Back-office UP-2A
        </h1>
        <p className="text-ink-soft">Connectez-vous avec votre compte administrateur.</p>
      </div>
      <LoginForm />
    </main>
  );
}
