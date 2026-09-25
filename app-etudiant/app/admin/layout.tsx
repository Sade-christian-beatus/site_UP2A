import Link from "next/link";
import { requireRole } from "@/lib/auth/dal";
import { LogoutButton } from "@/lib/auth/logout-button";

const NAV_LINKS = [
  { href: "/admin", label: "Candidatures" },
  { href: "/admin/emplois-du-temps", label: "Emplois du temps" },
  { href: "/admin/examens", label: "Examens" },
  { href: "/admin/resultats", label: "Résultats" },
  { href: "/admin/supports-cours", label: "Supports de cours" },
  { href: "/admin/annonces", label: "Annonces" },
];

export default async function AdminLayout({
  children,
}: LayoutProps<"/admin">) {
  const profile = await requireRole("admin");

  return (
    <div className="flex min-h-full flex-col">
      <header className="flex items-center justify-between border-b border-border bg-surface px-6 py-4">
        <Link href="/admin" className="font-heading text-lg text-ink">
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
      <main className="mx-auto flex w-full max-w-5xl flex-1 flex-col px-6 py-8">
        {children}
      </main>
    </div>
  );
}
