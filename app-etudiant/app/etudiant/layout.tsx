import { requireRole } from "@/lib/auth/dal";
import { LogoutButton } from "@/lib/auth/logout-button";

export default async function EtudiantLayout({
  children,
}: LayoutProps<"/etudiant">) {
  const profile = await requireRole("etudiant");

  return (
    <div className="flex min-h-full flex-col">
      <header className="flex items-center justify-between border-b border-border bg-surface px-6 py-4">
        <span className="font-heading text-lg text-ink">
          Espace étudiant — {profile.prenom} {profile.nom}
        </span>
        <LogoutButton />
      </header>
      <main className="flex flex-1 flex-col px-6 py-8">{children}</main>
    </div>
  );
}
