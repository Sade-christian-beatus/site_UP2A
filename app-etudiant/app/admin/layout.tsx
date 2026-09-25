import Link from "next/link";
import { requireRole } from "@/lib/auth/dal";
import { LogoutButton } from "@/lib/auth/logout-button";

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
      <main className="mx-auto flex w-full max-w-5xl flex-1 flex-col px-6 py-8">
        {children}
      </main>
    </div>
  );
}
