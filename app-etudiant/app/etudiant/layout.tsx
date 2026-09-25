import Link from "next/link";
import { requireRole } from "@/lib/auth/dal";
import { LogoutButton } from "@/lib/auth/logout-button";

const NAV_LINKS = [
  { href: "/etudiant", label: "Tableau de bord" },
  { href: "/etudiant/emploi-du-temps", label: "Emploi du temps" },
  { href: "/etudiant/supports", label: "Supports de cours" },
  { href: "/etudiant/examens", label: "Examens" },
  { href: "/etudiant/resultats", label: "Résultats" },
  { href: "/etudiant/documents", label: "Documents" },
  { href: "/etudiant/annonces", label: "Annonces" },
];

export default async function EtudiantLayout({
  children,
}: LayoutProps<"/etudiant">) {
  const profile = await requireRole("etudiant");

  return (
    <div className="flex min-h-full flex-col">
      <header className="flex items-center justify-between border-b border-border bg-surface px-6 py-4">
        <Link href="/etudiant" className="font-heading text-lg text-ink">
          Espace étudiant — {profile.prenom} {profile.nom}
        </Link>
        <LogoutButton />
      </header>
      <nav className="flex flex-wrap gap-1 border-b border-border bg-surface-alt px-6 py-2">
        {NAV_LINKS.map((link) => (
          <Link
            key={link.href}
            href={link.href}
            className="rounded-md px-3 py-1.5 text-sm font-medium text-ink-soft transition-colors hover:bg-border hover:text-ink"
          >
            {link.label}
          </Link>
        ))}
      </nav>
      <main className="mx-auto flex w-full max-w-4xl flex-1 flex-col px-6 py-8">
        {children}
      </main>
    </div>
  );
}
