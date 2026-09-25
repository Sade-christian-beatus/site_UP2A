import Link from "next/link";
import { requireRole } from "@/lib/auth/dal";
import { LogoutButton } from "@/lib/auth/logout-button";
import { NavLinks } from "@/lib/nav-links";

const NAV_LINKS = [
  { href: "/", label: "Candidatures" },
  { href: "/emplois-du-temps", label: "Emplois du temps" },
  { href: "/examens", label: "Examens" },
  { href: "/resultats", label: "Résultats" },
  { href: "/supports-cours", label: "Supports de cours" },
  { href: "/annonces", label: "Annonces" },
];

export default async function AppLayout({
  children,
}: LayoutProps<"/">) {
  const profile = await requireRole("admin");

  return (
    <div className="flex min-h-full flex-col">
      <header className="flex items-center justify-between border-b border-border bg-surface px-6 py-4">
        <Link href="/" className="font-heading text-lg text-ink">
          Back-office UP-2A
        </Link>
        <div className="flex items-center gap-4">
          <span className="text-sm text-ink-soft">
            {profile.prenom} {profile.nom}
          </span>
          <LogoutButton />
        </div>
      </header>
      <nav className="flex flex-wrap gap-1 border-b border-border bg-surface-alt px-6 py-2">
        <NavLinks links={NAV_LINKS} />
      </nav>
      <main className="mx-auto flex w-full max-w-5xl flex-1 flex-col px-6 py-8">
        {children}
      </main>
    </div>
  );
}
