import Link from "next/link";
import { requireRole } from "@/lib/auth/dal";
import { LogoutButton } from "@/lib/auth/logout-button";
import { Avatar } from "@/lib/avatar";
import { Icon } from "@/lib/icons";
import { SidebarNav, MobileNav, type NavItem } from "./sidebar-nav";

const NAV_ITEMS: NavItem[] = [
  { href: "/", label: "Tableau de bord", icon: "dashboard" },
  { href: "/emploi-du-temps", label: "Emploi du temps", icon: "calendar" },
  { href: "/supports", label: "Supports de cours", icon: "book" },
  { href: "/examens", label: "Examens", icon: "clipboard" },
  { href: "/resultats", label: "Résultats", icon: "chart" },
  { href: "/documents", label: "Documents", icon: "file" },
  { href: "/annonces", label: "Annonces", icon: "megaphone" },
];

export default async function EtudiantLayout({
  children,
}: LayoutProps<"/">) {
  const profile = await requireRole("etudiant");

  return (
    <div className="flex min-h-full">
      {/* Sidebar — desktop uniquement */}
      <aside className="hidden w-64 shrink-0 flex-col border-r border-border bg-surface md:flex">
        <Link href="/" className="flex items-center gap-2 px-6 py-6">
          <span className="font-heading text-lg text-ink">UP-2A</span>
          <span className="text-xs font-medium text-ink-soft">Espace étudiant</span>
        </Link>

        <SidebarNav items={NAV_ITEMS} />

        <div className="mt-auto flex flex-col gap-3 border-t border-border p-4">
          <div className="flex items-center gap-3">
            <Avatar prenom={profile.prenom} nom={profile.nom} />
            <div className="flex min-w-0 flex-col">
              <span className="truncate text-sm font-medium text-ink">
                {profile.prenom} {profile.nom}
              </span>
              <span className="text-xs text-ink-soft">Étudiant·e</span>
            </div>
          </div>
          <LogoutButton />
        </div>
      </aside>

      <div className="flex min-w-0 flex-1 flex-col">
        {/* Barre du haut — mobile uniquement */}
        <header className="flex items-center justify-between border-b border-border bg-surface px-4 py-3 md:hidden">
          <Link href="/" className="flex items-center gap-2">
            <span className="font-heading text-base text-ink">UP-2A</span>
            <span className="text-xs text-ink-soft">Espace étudiant</span>
          </Link>
          <Avatar prenom={profile.prenom} nom={profile.nom} taille="sm" />
        </header>
        <MobileNav items={NAV_ITEMS} />

        <main className="mx-auto flex w-full max-w-4xl flex-1 flex-col px-4 py-6 md:px-8 md:py-10">
          {children}
        </main>

        <div className="border-t border-border px-4 py-3 md:hidden">
          <div className="mx-auto flex max-w-4xl items-center justify-between">
            <span className="flex items-center gap-1.5 text-xs text-ink-soft">
              <Icon name="dashboard" className="h-3.5 w-3.5" />
              Connecté·e en tant que {profile.prenom}
            </span>
            <LogoutButton />
          </div>
        </div>
      </div>
    </div>
  );
}
